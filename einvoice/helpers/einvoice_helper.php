<?php

defined('BASEPATH') or exit('No direct script access allowed');

function einvoice_module_get_templates(): array
{
    $ci = &get_instance();
    $ci->load->model('templates_model');

    return $ci->templates_model->getByType('einvoice');
}

function einvioce_module_get_templates(): array
{
    return einvoice_module_get_templates();
}

function einvoice_module_ensure_options(): void
{
    add_option('einvoice_send_as_invoice_email_attachment', '0');
    add_option('einvoice_send_as_credit_note_email_attachment', '0');
    add_option('einvoice_default_credit_note_email_template', '0');
    add_option('einvoice_default_invoice_template', '');
    add_option('einvoice_default_credit_note_template', '');

    add_option('einvoice_ksef_enabled', '0');
    add_option('einvoice_ksef_environment', 'demo');
    add_option('einvoice_ksef_api_url', '');
    add_option('einvoice_ksef_api_token', '');
    add_option('einvoice_ksef_company_nip', '');
    add_option('einvoice_ksef_auto_sync', '0');
    add_option('einvoice_ksef_invoice_template', '');
    add_option('einvoice_ksef_include_pdf_link', '1');
}

function einvoice_module_ensure_schema(): void
{
    $CI = &get_instance();
    $table = db_prefix() . 'einvoice_ksef_sync';

    if ($CI->db->table_exists($table)) {
        return;
    }

    $charset = $CI->db->char_set;
    $collation = $CI->db->dbcollat ?: 'utf8mb4_unicode_ci';

    $sql = "CREATE TABLE `" . $table . "` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `invoice_id` INT NOT NULL,
        `sync_status` VARCHAR(40) NOT NULL DEFAULT 'pending',
        `payload_format` VARCHAR(10) NOT NULL DEFAULT 'xml',
        `external_reference` VARCHAR(191) NULL,
        `payload_snapshot` MEDIUMTEXT NULL,
        `response_code` INT NULL,
        `response_body` MEDIUMTEXT NULL,
        `last_error` TEXT NULL,
        `synced_at` DATETIME NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_invoice_id` (`invoice_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";";

    $CI->db->query($sql);
}

function einvoice_module_get_ksef_environment_options(): array
{
    return [
        ['id' => 'demo', 'name' => _l('einvoice_ksef_env_demo')],
        ['id' => 'production', 'name' => _l('einvoice_ksef_env_production')],
    ];
}

function einvoice_module_is_ksef_enabled(): bool
{
    return get_option('einvoice_ksef_enabled') === '1';
}

function einvoice_module_get_ksef_template_id(): string
{
    $templateId = trim((string) get_option('einvoice_ksef_invoice_template'));

    if ($templateId !== '') {
        return $templateId;
    }

    return (string) get_option('einvoice_default_invoice_template');
}

function einvoice_module_get_sync_record(int $invoiceId): ?array
{
    $CI = &get_instance();
    $table = db_prefix() . 'einvoice_ksef_sync';

    if (! $CI->db->table_exists($table)) {
        return null;
    }

    return $CI->db
        ->where('invoice_id', $invoiceId)
        ->get($table)
        ->row_array() ?: null;
}

function einvoice_module_get_sync_status_label(string $status): string
{
    $labels = [
        'pending' => _l('einvoice_ksef_status_pending'),
        'skipped' => _l('einvoice_ksef_status_skipped'),
        'success' => _l('einvoice_ksef_status_success'),
        'error'   => _l('einvoice_ksef_status_error'),
    ];

    return $labels[$status] ?? ucfirst($status);
}

function einvoice_module_get_sync_status_color(string $status): string
{
    $colors = [
        'pending' => '#f59e0b',
        'skipped' => '#6b7280',
        'success' => '#15803d',
        'error'   => '#dc2626',
    ];

    return $colors[$status] ?? '#6b7280';
}

function einvoice_module_get_sync_badge_html(int $invoiceId): string
{
    $record = einvoice_module_get_sync_record($invoiceId);
    $status = $record['sync_status'] ?? 'pending';
    $label = einvoice_module_get_sync_status_label($status);
    $color = einvoice_module_get_sync_status_color($status);

    return '<span class="label" style="background-color:' . $color . ';">' . e($label) . '</span>';
}

function einvoice_module_upsert_sync_record(int $invoiceId, array $data): void
{
    $CI = &get_instance();
    $table = db_prefix() . 'einvoice_ksef_sync';

    if (! $CI->db->table_exists($table)) {
        return;
    }

    $existing = einvoice_module_get_sync_record($invoiceId);
    $now = date('Y-m-d H:i:s');

    $payload = [
        'invoice_id'          => $invoiceId,
        'sync_status'         => $data['sync_status'] ?? ($existing['sync_status'] ?? 'pending'),
        'payload_format'      => $data['payload_format'] ?? ($existing['payload_format'] ?? 'xml'),
        'external_reference'  => $data['external_reference'] ?? ($existing['external_reference'] ?? null),
        'payload_snapshot'    => $data['payload_snapshot'] ?? ($existing['payload_snapshot'] ?? null),
        'response_code'       => array_key_exists('response_code', $data) ? $data['response_code'] : ($existing['response_code'] ?? null),
        'response_body'       => $data['response_body'] ?? ($existing['response_body'] ?? null),
        'last_error'          => $data['last_error'] ?? ($existing['last_error'] ?? null),
        'synced_at'           => $data['synced_at'] ?? ($existing['synced_at'] ?? null),
        'updated_at'          => $now,
    ];

    if ($existing) {
        $CI->db->where('invoice_id', $invoiceId)->update($table, $payload);
        return;
    }

    $payload['created_at'] = $now;
    $CI->db->insert($table, $payload);
}

function einvoice_module_should_auto_sync_invoice($invoice): bool
{
    if (! $invoice || ! einvoice_module_is_ksef_enabled()) {
        return false;
    }

    if (get_option('einvoice_ksef_auto_sync') !== '1') {
        return false;
    }

    if (empty(get_option('einvoice_ksef_api_url')) || empty(get_option('einvoice_ksef_api_token'))) {
        return false;
    }

    if (empty(einvoice_module_get_ksef_template_id())) {
        return false;
    }

    if ((int) $invoice->status === Invoices_model::STATUS_CANCELLED || (int) $invoice->status === Invoices_model::STATUS_DRAFT) {
        return false;
    }

    return true;
}

function einvoice_module_build_ksef_payload(int $invoiceId): array
{
    $CI = &get_instance();
    $CI->load->model('invoices_model');
    $CI->load->model('templates_model');

    $invoice = $CI->invoices_model->get($invoiceId);

    if (! $invoice) {
        return ['success' => false, 'message' => _l('einvoice_ksef_invoice_not_found')];
    }

    $templateId = einvoice_module_get_ksef_template_id();
    $template = $CI->templates_model->find($templateId);

    if (! $template) {
        return ['success' => false, 'message' => _l('einvoice_ksef_template_missing')];
    }

    $format = strtolower((string) $template->content_type) === 'json' ? 'json' : 'xml';
    $handler = new Perfexcrm\EInvoice\EinvoiceHandler();
    $einvoiceData = new Perfexcrm\EInvoice\Data\Invoice($invoice);
    $payload = $handler->renderTemplate($template->content, $einvoiceData, $template->content_type);

    return [
        'success' => true,
        'invoice' => $invoice,
        'payload' => $payload,
        'format'  => $format,
    ];
}

function einvoice_module_sync_invoice_to_ksef(int $invoiceId, bool $manual = false): array
{
    $CI = &get_instance();

    if (! einvoice_module_is_ksef_enabled()) {
        return ['success' => false, 'message' => _l('einvoice_ksef_disabled')];
    }

    $apiUrl = trim((string) get_option('einvoice_ksef_api_url'));
    $apiToken = trim((string) get_option('einvoice_ksef_api_token'));

    if ($apiUrl === '' || $apiToken === '') {
        return ['success' => false, 'message' => _l('einvoice_ksef_missing_api_credentials')];
    }

    $build = einvoice_module_build_ksef_payload($invoiceId);

    if (! $build['success']) {
        einvoice_module_upsert_sync_record($invoiceId, [
            'sync_status' => 'error',
            'last_error'  => $build['message'],
        ]);

        return $build;
    }

    $invoice = $build['invoice'];

    if (! $manual && ! einvoice_module_should_auto_sync_invoice($invoice)) {
        einvoice_module_upsert_sync_record($invoiceId, [
            'sync_status'      => 'skipped',
            'payload_format'   => $build['format'],
            'payload_snapshot' => $build['payload'],
            'last_error'       => _l('einvoice_ksef_auto_sync_skipped'),
        ]);

        return ['success' => false, 'message' => _l('einvoice_ksef_auto_sync_skipped')];
    }

    $requestBody = [
        'environment' => get_option('einvoice_ksef_environment') ?: 'demo',
        'company_nip' => trim((string) get_option('einvoice_ksef_company_nip')),
        'invoice'     => [
            'id'      => (int) $invoice->id,
            'number'  => format_invoice_number($invoice->id),
            'date'    => $invoice->date,
            'status'  => format_invoice_status($invoice->status, '', false),
            'client'  => $invoice->client->company ?? '',
            'total'   => (float) $invoice->total,
            'currency'=> $invoice->currency_name,
            'format'  => $build['format'],
            'payload' => $build['payload'],
            'pdf_url' => get_option('einvoice_ksef_include_pdf_link') === '1' ? site_url('invoice/' . $invoice->id . '/' . $invoice->hash) : null,
        ],
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($requestBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $responseCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($responseBody === false) {
        $message = _l('einvoice_ksef_sync_failed') . ': ' . $curlError;
        einvoice_module_upsert_sync_record($invoiceId, [
            'sync_status'      => 'error',
            'payload_format'   => $build['format'],
            'payload_snapshot' => $build['payload'],
            'response_code'    => $responseCode ?: null,
            'last_error'       => $message,
        ]);

        return ['success' => false, 'message' => $message];
    }

    $decoded = json_decode($responseBody, true);
    $externalReference = null;
    $responseMessage = null;

    if (is_array($decoded)) {
        $externalReference = $decoded['external_reference']
            ?? $decoded['reference']
            ?? $decoded['ksef_reference']
            ?? $decoded['number']
            ?? $decoded['id']
            ?? null;

        $responseMessage = $decoded['message'] ?? $decoded['error'] ?? null;
    }

    if ($responseCode >= 200 && $responseCode < 300) {
        einvoice_module_upsert_sync_record($invoiceId, [
            'sync_status'        => 'success',
            'payload_format'     => $build['format'],
            'payload_snapshot'   => $build['payload'],
            'response_code'      => $responseCode,
            'response_body'      => $responseBody,
            'external_reference' => $externalReference,
            'last_error'         => null,
            'synced_at'          => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'message' => $responseMessage ?: _l('einvoice_ksef_sync_success'),
        ];
    }

    $message = $responseMessage ?: _l('einvoice_ksef_sync_failed');
    einvoice_module_upsert_sync_record($invoiceId, [
        'sync_status'      => 'error',
        'payload_format'   => $build['format'],
        'payload_snapshot' => $build['payload'],
        'response_code'    => $responseCode,
        'response_body'    => $responseBody,
        'last_error'       => $message,
    ]);

    return ['success' => false, 'message' => $message];
}
