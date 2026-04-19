<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_orders_model extends App_Model
{
    protected $table = 'flexacademy_orders';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all orders
     * @param array $conditions
     * @return array
     */
    public function all($conditions = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $this->db->order_by('order_date', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get order by conditions
     * @param array $conditions
     * @return array|null
     */
    public function get($conditions)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Create new order
     * @param array $data
     * @return int|bool
     */
    public function add($data)
    {
        // Generate unique order number if not provided
        if (!isset($data['order_number'])) {
            $data['order_number'] = $this->generate_order_number();
        }

        $data['order_date'] = date('Y-m-d H:i:s');
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . $this->table, $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('FlexAcademy Order Created [Order #' . $data['order_number'] . ']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update order
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update($data, $id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->table, $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('FlexAcademy Order Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete order
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . $this->table);

        if ($this->db->affected_rows() > 0) {
            log_activity('FlexAcademy Order Deleted [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Generate unique order number
     * @return string
     */
    private function generate_order_number()
    {
        $prefix = 'FA';
        $date = date('Ymd');
        
        // Get last order number for today
        $this->db->select('order_number');
        $this->db->from(db_prefix() . $this->table);
        $this->db->like('order_number', $prefix . $date, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $last_order = $query->row_array();
            $last_number = (int)substr($last_order['order_number'], -4);
            $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $new_number = '0001';
        }
        
        return $prefix . $date . $new_number;
    }

    /**
     * Get orders by client
     * @param int $client_id
     * @param string $status
     * @return array
     */
    public function get_client_orders($client_id, $status = null)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('client_id', $client_id);
        
        if ($status) {
            $this->db->where('status', $status);
        }
        
        $this->db->order_by('order_date', 'DESC');
        $query = $this->db->get();
        
        return $query->result_array();
    }

    /**
     * Get orders by invoice
     * @param int $invoice_id
     * @return array|null
     */
    public function get_by_invoice($invoice_id)
    {
        return $this->get(['invoice_id' => $invoice_id]);
    }

    /**
     * Update order status
     * @param int $order_id
     * @param string $status
     * @return bool
     */
    public function update_status($order_id, $status)
    {
        return $this->update(['status' => $status], $order_id);
    }

    /**
     * Link order to invoice
     * @param int $order_id
     * @param int $invoice_id
     * @return bool
     */
    public function link_invoice($order_id, $invoice_id)
    {
        return $this->update(['invoice_id' => $invoice_id], $order_id);
    }
}

