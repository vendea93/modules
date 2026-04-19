<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Customer Addresses Model
 * Handles CRUD operations for additional customer locations/branches.
 */
class Customer_addresses_model extends App_Model
{
    /**
     * @var string
     */
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'poly_utilities_customer_addresses';
        $this->ensure_table_exists();
    }

    /**
     * Get all addresses for a customer.
     *
     * @param int $customerId
     * @return array
     */
    public function get_by_customer($customerId)
    {
        if (empty($customerId)) {
            return [];
        }

        $rows = $this->db->where('clientid', (int) $customerId)
            ->order_by('is_default_billing', 'DESC')
            ->order_by('is_default_shipping', 'DESC')
            ->order_by('title', 'ASC')
            ->get($this->table)
            ->result_array();

        return array_map([$this, 'cast_row'], $rows);
    }

    /**
     * Find address by ID.
     *
     * @param int $id
     * @param int|null $customerId
     * @return array|null
     */
    public function find($id, $customerId = null)
    {
        if (empty($id)) {
            return null;
        }

        $this->db->where('id', (int) $id);

        if (!empty($customerId)) {
            $this->db->where('clientid', (int) $customerId);
        }

        $address = $this->db->get($this->table)->row_array();

        return $address ? $this->cast_row($address) : null;
    }

    /**
     * Create new address for customer.
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data)
    {
        $address = $this->prepare_payload($data, true);

        if (!$address) {
            return false;
        }

        $this->db->insert($this->table, $address);
        $insertId = $this->db->insert_id();

        if ($insertId) {
            $this->sync_default_flags($address['clientid'], $insertId, $address);
        }

        return $insertId;
    }

    /**
     * Update address.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        $current = $this->find($id);

        if (!$current) {
            return false;
        }

        $address = $this->prepare_payload($data + ['clientid' => $current['clientid']], false);

        if (!$address) {
            return false;
        }

        $this->db->where('id', (int) $id)
            ->update($this->table, $address);

        if ($this->db->affected_rows() >= 0) {
            $this->sync_default_flags($current['clientid'], $id, $address);
            return true;
        }

        return false;
    }

    /**
     * Delete address.
     *
     * @param int $id
     * @param int|null $customerId
     * @return bool
     */
    public function delete($id, $customerId = null)
    {
        if (empty($id)) {
            return false;
        }

        $this->db->where('id', (int) $id);

        if (!empty($customerId)) {
            $this->db->where('clientid', (int) $customerId);
        }

        return $this->db->delete($this->table);
    }

    /**
     * Fetch address options formatted for dropdowns.
     *
     * @param int $customerId
     * @return array
     */
    public function get_dropdown_options($customerId)
    {
        $addresses = $this->get_by_customer($customerId);
        $options = [];

        foreach ($addresses as $address) {
            $labelParts = array_filter([
                $address['title'],
                $this->format_inline_address($address),
            ]);

            $options[] = [
                'id' => $address['id'],
                'label' => implode(' — ', $labelParts),
                'data' => $this->cast_row($address),
            ];
        }

        return $options;
    }

    /**
     * Normalize payload.
     *
     * @param array $data
     * @param bool $isInsert
     * @return array|null
     */
    protected function prepare_payload(array $data, $isInsert = true)
    {
        $fields = [
            'clientid'            => 'int',
            'title'               => 'string',
            'address_line1'       => 'string',
            'address_line2'       => 'string',
            'city'                => 'string',
            'state'               => 'string',
            'zip'                 => 'string',
            'country_id'          => 'int',
            'contact_person'      => 'string',
            'phone'               => 'string',
            'email'               => 'string',
            'map_url'             => 'string',
            'map_embed'           => 'raw',
            'latitude'            => 'string',
            'longitude'           => 'string',
            'additional_info'     => 'string',
            'social_links'        => 'json',
            'is_default_billing'  => 'bool',
            'is_default_shipping' => 'bool',
        ];

        $payload = [];

        foreach ($fields as $field => $type) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            switch ($type) {
                case 'int':
                    $payload[$field] = !empty($value) ? (int) $value : null;
                    break;
                case 'bool':
                    $payload[$field] = !empty($value) ? 1 : 0;
                    break;
                case 'json':
                    if (is_array($value)) {
                        $payload[$field] = json_encode($value);
                    } elseif (is_string($value) && $value !== '') {
                        $payload[$field] = $value;
                    } else {
                        $payload[$field] = null;
                    }
                    break;
                case 'raw':
                    $payload[$field] = !empty($value) ? $value : null;
                    break;
                default:
                    $payload[$field] = !empty($value) ? trim($value) : null;
                    break;
            }
        }

        if ($isInsert) {
            if (empty($payload['clientid']) || empty($payload['title'])) {
                return null;
            }
        } else {
            unset($payload['clientid']); // prevent changing ownership
        }

        return $payload;
    }

    /**
     * Keep default flags unique per customer.
     *
     * @param int $customerId
     * @param int $addressId
     * @param array $payload
     * @return void
     */
    protected function sync_default_flags($customerId, $addressId, array $payload)
    {
        if (empty($customerId) || empty($addressId)) {
            return;
        }

        foreach (['is_default_billing', 'is_default_shipping'] as $flag) {
            if (array_key_exists($flag, $payload) && (int) $payload[$flag] === 1) {
                $this->db->where('clientid', (int) $customerId)
                    ->where('id !=', (int) $addressId)
                    ->set($flag, 0)
                    ->update($this->table);
            }
        }
    }

    /**
     * Format address inline text.
     *
     * @param array $address
     * @return string
     */
    public function format_inline_address(array $address)
    {
        $segments = array_filter([
            $address['address_line1'] ?? '',
            $address['address_line2'] ?? '',
            $address['city'] ?? '',
            $address['state'] ?? '',
            $address['zip'] ?? '',
        ]);

        return trim(implode(', ', $segments));
    }

    protected function cast_row(array $address)
    {
        $address['social_links'] = $this->decode_social_links($address['social_links'] ?? null);
        return $address;
    }

    protected function decode_social_links($value)
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
        foreach ($value as $link) {
            if (!is_array($link)) {
                continue;
            }
            $normalized[] = [
                'id'    => $link['id'] ?? uniqid('social_', true),
                'icon'  => $link['icon'] ?? '',
                'title' => $link['title'] ?? '',
                'value' => $link['value'] ?? '',
            ];
        }

        return $normalized;
    }

    /**
     * Lazily create table when missing (e.g. tenant databases seeded from older dump).
     *
     * @return void
     */
    private function ensure_table_exists()
    {
        $charset = $this->db->char_set;
        $collate = $this->db->dbcollat;

        if (!$this->db->table_exists($this->table)) {
            $this->db->query("
                CREATE TABLE `{$this->table}` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `clientid` INT(11) UNSIGNED NOT NULL,
                    `title` VARCHAR(191) NOT NULL,
                    `address_line1` TEXT DEFAULT NULL,
                    `address_line2` TEXT DEFAULT NULL,
                    `city` VARCHAR(120) DEFAULT NULL,
                    `state` VARCHAR(120) DEFAULT NULL,
                    `zip` VARCHAR(40) DEFAULT NULL,
                    `country_id` INT(11) DEFAULT NULL,
                    `contact_person` VARCHAR(191) DEFAULT NULL,
                    `phone` VARCHAR(60) DEFAULT NULL,
                    `email` VARCHAR(191) DEFAULT NULL,
                    `map_url` TEXT DEFAULT NULL,
                    `map_embed` TEXT DEFAULT NULL,
                    `latitude` VARCHAR(50) DEFAULT NULL,
                    `longitude` VARCHAR(50) DEFAULT NULL,
                    `additional_info` TEXT DEFAULT NULL,
                    `social_links` LONGTEXT DEFAULT NULL,
                    `is_default_billing` TINYINT(1) NOT NULL DEFAULT 0,
                    `is_default_shipping` TINYINT(1) NOT NULL DEFAULT 0,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_customer` (`clientid`),
                    KEY `idx_country` (`country_id`),
                    KEY `idx_default_billing` (`clientid`, `is_default_billing`),
                    KEY `idx_default_shipping` (`clientid`, `is_default_shipping`)
                ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};
            ");
        } else {
            $columns = [
                'map_url'      => "ALTER TABLE `{$this->table}` ADD COLUMN `map_url` TEXT DEFAULT NULL AFTER `email`",
                'map_embed'    => "ALTER TABLE `{$this->table}` ADD COLUMN `map_embed` TEXT DEFAULT NULL AFTER `map_url`",
                'latitude'     => "ALTER TABLE `{$this->table}` ADD COLUMN `latitude` VARCHAR(50) DEFAULT NULL AFTER `map_embed`",
                'longitude'    => "ALTER TABLE `{$this->table}` ADD COLUMN `longitude` VARCHAR(50) DEFAULT NULL AFTER `latitude`",
                'social_links' => "ALTER TABLE `{$this->table}` ADD COLUMN `social_links` LONGTEXT DEFAULT NULL AFTER `additional_info`",
            ];

            foreach ($columns as $column => $statement) {
                if (!$this->db->field_exists($column, $this->table)) {
                    try {
                        $this->db->query($statement);
                    } catch (\Throwable $th) {
                        log_message('error', 'poly_utilities ensure_table_exists error: ' . $th->getMessage());
                    }
                }
            }
        }
    }
}

