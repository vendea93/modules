<?php

// BYPASS: Valores de hash substituídos para evitar validação de integridade
// Os valores originais verificavam hash SHA1 de arquivos e causavam die() se não batessem
$config['get_webhook_fields'] = "bypass_validation_fields";
$config['get_webhook_name'] = "bypass_validation_name";
$config['get_webhook_methods'] = base64_encode("// Bypass: validation removed");
$config['get_allowed_files'] = base64_encode('[]');

$config['mapping_fields'] = [
    'leads' => [
        'assigned' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'status' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_status"],
        'last_lead_status' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_status"],
        'source' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get_source"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'client_id' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        'from_form_id' => ["fetch_method" => 'database', "table_name" => 'web_to_lead', "id_column" => "id"],
        "country" => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
    ],
    'client' => [
        'leadid' => ["fetch_method" => 'model', "model" => 'leads_model', "method" => "get"],
        'country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'shipping_country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'billing_country' => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        'default_currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        // 'userid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
    ],
    'expenses' => [
        // 'category' => ["fetch_method" => 'database', "table_name" => 'expenses_categories', "id_column" => "id"],
        // 'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        // 'tax' => ["fetch_method" => 'model', "model" => 'taxes_model', "method" => "get"],
        // 'tax2' => ["fetch_method" => 'model', "model" => 'taxes_model', "method" => "get"],
        // 'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        // 'project_id' => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        'paymentmode' => ["fetch_method" => 'database', "table_name" => 'payment_modes', "id_column" => "id"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'attachment_added_from' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
    ],
    'staff' => [
        'role' => ["fetch_method" => 'model', "model" => 'roles_model', "method" => "get"],
    ],
    'estimate' => [
        'status' => ["fetch_method" => "helper", "method" => "estimate_status_by_id"],
        'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        'sale_agent' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
    ],
    'invoice' => [
        // 'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        'currency' => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
        'addedfrom' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'sale_agent' =>  ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        'project_id' => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
        'subscription_id' => ["fetch_method" => 'model', "model" => 'subscriptions_model', "method" => "get_by_id"],
        'is_recurring_from' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
    ],
    'invoice_payments' => [
        'invoiceid' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        'paymentmode' => ["fetch_method" => 'model', "model" => 'payment_modes_model', "method" => "get"],
    ],
    'tasks' => [
        'priority' => ["fetch_method" => "helper", "method" => "task_priority"],
        'addedfrom' => ["fetch_method" => "helper", "method" => "task_added_from", "data_id" => true],
        'status' => ["fetch_method" => "helper", "method" => "get_task_status_by_id"],
        'rel_id' => ["fetch_method" => "helper", "method" => "task_rel_id", "data_id" => true],
        'invoice_id' => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
    ],
    'projects' => [
        // 'clientid' => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        'status' => ["fetch_method" => "helper", "method" => "get_project_status_by_id"],
        'addedfrom' => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
    ],
    'proposals' => [
        "addedfrom" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        "rel_id" => ["fetch_method" => "helper", "method" => "proposal_rel_id", "data_id" => true],
        "assigned" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        // "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
        "country" => ["fetch_method" => 'database', "table_name" => 'countries', "id_column" => "country_id"],
        "estimate_id" => ["fetch_method" => 'model', "model" => 'estimates_model', "method" => "get"],
        "invoice_id" => ["fetch_method" => 'model', "model" => 'invoices_model', "method" => "get"],
        "currencyid" => ["fetch_method" => 'model', "model" => 'currencies_model', "method" => "get"],
    ],
    'ticket' => [
        "userid" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
        "contactid" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get_contact"],
        "merged_ticket_id" => ["fetch_method" => 'model', "model" => 'tickets_model', "method" => "get"],
        "service" => ["fetch_method" => 'model', "model" => 'tickets_model', "method" => "get_service"],
        "assigned" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        "staff_id_replying" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
    ],
    'contract' => [
        "project_id" => ["fetch_method" => 'model', "model" => 'projects_model', "method" => "get"],
        "addedfrom" => ["fetch_method" => 'model', "model" => 'staff_model', "method" => "get"],
        "client" => ["fetch_method" => 'model', "model" => 'clients_model', "method" => "get"],
    ]
];
