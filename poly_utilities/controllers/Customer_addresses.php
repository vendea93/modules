<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Customer Addresses Controller (Admin Side)
 * Provides AJAX endpoints for managing customer branch/location data.
 */
class Customer_addresses extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!staff_can('view', 'customers') && !staff_can('view_own', 'customers')) {
            access_denied('Customers');
        }

        $this->load->model('poly_utilities/customer_addresses_model');
    }

    /**
     * Datatable source for customer addresses.
     *
     * @param int $customerId
     * @return void
     */
    public function table($customerId)
    {
        $this->validate_ajax_request();

        $addresses = $this->customer_addresses_model->get_by_customer($customerId);
        $rows = [];

        foreach ($addresses as $address) {
            $rows[] = [
                $address['id'],
                '<strong>' . html_escape($address['title']) . '</strong>' .
                    $this->format_badges($address),
                nl2br(html_escape($this->customer_addresses_model->format_inline_address($address))),
                html_escape($address['contact_person'] ?? ''),
                html_escape($address['phone'] ?? ''),
                html_escape($address['email'] ?? ''),
                $this->build_actions($customerId, $address['id']),
            ];
        }

        $total = count($rows);
        $draw = (int) $this->input->post('draw');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'data' => $rows,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'draw' => $draw,
            ]));
    }

    /**
     * Return single address payload.
     *
     * @param int $customerId
     * @param int $id
     * @return void
     */
    public function show($customerId, $id)
    {
        $this->validate_ajax_request();

        $address = $this->customer_addresses_model->find($id, $customerId);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => (bool) $address,
                'data'    => $address,
            ]));
    }

    /**
     * Create or update address.
     *
     * @param int $customerId
     * @return void
     */
    public function save($customerId)
    {
        $this->validate_ajax_request();

        $payload = $this->input->post(null, false);
        $addressId = !empty($payload['id']) ? (int) $payload['id'] : 0;

        $payload = $this->prepare_payload($payload, $customerId);

        if ($addressId) {
            $success = $this->customer_addresses_model->update($addressId, $payload);
            $message = $success ? _l('poly_utilities_address_updated_successfully') : _l('poly_utilities_address_update_failed');
        } else {
            $insertId = $this->customer_addresses_model->create($payload);
            $success  = (bool) $insertId;
            $message  = $success ? _l('poly_utilities_address_created_successfully') : _l('poly_utilities_address_create_failed');
            $addressId = $insertId ?: 0;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $success,
                'message' => $message,
                'id'      => $addressId,
            ]));
    }

    /**
     * Delete address.
     *
     * @param int $customerId
     * @param int $id
     * @return void
     */
    public function delete($customerId, $id)
    {
        $this->validate_ajax_request();

        $success = $this->customer_addresses_model->delete($id, $customerId);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $success,
                'message' => $success ? _l('poly_utilities_address_deleted_successfully') : _l('poly_utilities_address_delete_failed'),
            ]));
    }

    /**
     * Dropdown options for address selection.
     *
     * @param int $customerId
     * @return void
     */
    public function dropdown($customerId)
    {
        $this->validate_ajax_request();

        $options = $this->customer_addresses_model->get_dropdown_options($customerId);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data'    => $options,
            ]));
    }

    /**
     * Build action buttons markup.
     *
     * @param int $customerId
     * @param int $addressId
     * @return string
     */
    private function build_actions($customerId, $addressId)
    {
        $preview = '<button class="btn btn-sm btn-info tw-inline-flex tw-items-center poly-address-preview-btn" data-id="' . $addressId . '" title="' . _l('poly_utilities_address_preview') . '"><i class="fa-solid fa-map-location-dot"></i></button>';
        $edit = '<button class="btn btn-sm btn-primary tw-inline-flex tw-items-center tw-ml-1 poly-address-edit" data-id="' . $addressId . '" title="' . _l('edit') . '"><i class="fa fa-pencil"></i></button>';
        $delete = '<button class="btn btn-sm btn-danger tw-inline-flex tw-items-center tw-ml-1 poly-address-delete" data-id="' . $addressId . '"><i class="fa fa-trash"></i></button>';

        return '<div class="tw-flex tw-items-center tw-gap-1">'.$preview . $edit . $delete.'</div>';
    }

    /**
     * Format badge labels.
     *
     * @param array $address
     * @return string
     */
    private function format_badges(array $address)
    {
        $badges = [];

        if (!empty($address['is_default_billing'])) {
            $badges[] = '<span class="badge bg-info tw-ml-1">' . _l('poly_utilities_address_default_billing') . '</span>';
        }

        if (!empty($address['is_default_shipping'])) {
            $badges[] = '<span class="badge bg-success tw-ml-1">' . _l('poly_utilities_address_default_shipping') . '</span>';
        }

        return implode(' ', $badges);
    }

    /**
     * Ensure request is AJAX.
     *
     * @return void
     */
    private function validate_ajax_request()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
    }

    /**
     * Prepare and sanitize payload before persisting.
     *
     * @param array $payload
     * @param int $customerId
     * @return array
     */
    private function prepare_payload(array $payload, $customerId)
    {
        $payload['clientid'] = $customerId;

        if (isset($payload['social_links'])) {
            $payload['social_links'] = $this->sanitize_social_links($payload['social_links']);
        }

        $payload['map_url'] = $this->sanitize_map_url($payload['map_url'] ?? '');

        $embedSourceUrl = null;
        $payload['map_embed'] = $this->sanitize_map_embed($payload['map_embed'] ?? '', $embedSourceUrl);

        if (empty($payload['map_url']) && !empty($embedSourceUrl)) {
            $payload['map_url'] = $embedSourceUrl;
        }

        $payload['latitude'] = $this->sanitize_coordinate($payload['latitude'] ?? '');
        $payload['longitude'] = $this->sanitize_coordinate($payload['longitude'] ?? '');

        if (empty($payload['latitude']) || empty($payload['longitude'])) {
            $coords = $this->extract_coordinates($payload['map_url'] ?? $embedSourceUrl ?? '');
            if (empty($payload['latitude']) && isset($coords['lat'])) {
                $payload['latitude'] = $coords['lat'];
            }
            if (empty($payload['longitude']) && isset($coords['lng'])) {
                $payload['longitude'] = $coords['lng'];
            }
        }

        $payload['title'] = trim($payload['title'] ?? '');
        $payload['address_line1'] = $this->nullable_trim($payload['address_line1'] ?? null);
        $payload['address_line2'] = $this->nullable_trim($payload['address_line2'] ?? null);
        $payload['city'] = $this->nullable_trim($payload['city'] ?? null);
        $payload['state'] = $this->nullable_trim($payload['state'] ?? null);
        $payload['zip'] = $this->nullable_trim($payload['zip'] ?? null);
        $payload['contact_person'] = $this->nullable_trim($payload['contact_person'] ?? null);
        $payload['phone'] = $this->nullable_trim($payload['phone'] ?? null);
        $payload['email'] = $this->sanitize_email($payload['email'] ?? null);
        $payload['additional_info'] = $this->nullable_trim($payload['additional_info'] ?? null);

        $payload['is_default_billing'] = !empty($payload['is_default_billing']) ? 1 : 0;
        $payload['is_default_shipping'] = !empty($payload['is_default_shipping']) ? 1 : 0;

        return $payload;
    }

    private function sanitize_social_links($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return [];
            }
            $value = $decoded;
        }

        $normalized = [];
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }
            $normalized[] = [
                'id'    => $item['id'] ?? uniqid('social_', true),
                'icon'  => $this->nullable_trim($item['icon'] ?? ''),
                'title' => $this->nullable_trim($item['title'] ?? ''),
                'value' => $this->nullable_trim($item['value'] ?? ''),
            ];
        }

        return $normalized;
    }

    private function sanitize_map_url($value)
    {
        $value = $this->nullable_trim($value);

        if (empty($value)) {
            return null;
        }

        if (stripos($value, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/', $value, $matches)) {
            $value = $matches[1];
        }

        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $value;
    }

    private function sanitize_map_embed($value, &$sourceUrl = null)
    {
        $value = trim((string) $value);

        if ($value === '') {
            $sourceUrl = null;
            return null;
        }

        // Extract iframe src if provided
        if (stripos($value, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/', $value, $matches)) {
            $sourceUrl = $matches[1];
        } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
            $sourceUrl = $value;
        }

        if ($sourceUrl) {
            $sourceUrl = html_entity_decode($sourceUrl, ENT_QUOTES, 'UTF-8');
            $safeSrc = htmlspecialchars($sourceUrl, ENT_QUOTES, 'UTF-8');

            return '<iframe src="' . $safeSrc . '" width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        }

        $clean = strip_tags($value, '<iframe>');

        return $clean ?: null;
    }

    private function sanitize_coordinate($value)
    {
        $value = $this->nullable_trim($value);
        if ($value === null) {
            return null;
        }

        if (!preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            return null;
        }

        return $value;
    }

    private function extract_coordinates($value)
    {
        $coords = ['lat' => null, 'lng' => null];
        if (empty($value) || !is_string($value)) {
            return $coords;
        }

        if (preg_match('/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', $value, $match)) {
            $coords['lat'] = $match[1];
            $coords['lng'] = $match[2];
        }

        if (preg_match('/!3d(-?\d+(?:\.\d+)?)/', $value, $matchLat)) {
            $coords['lat'] = $matchLat[1];
        }

        if (preg_match('/!(?:4d|2d)(-?\d+(?:\.\d+)?)/', $value, $matchLng)) {
            $coords['lng'] = $matchLng[1];
        }

        return $coords;
    }

    private function sanitize_email($value)
    {
        $value = $this->nullable_trim($value);
        if (!$value) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    private function nullable_trim($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}

