<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_event_staff_model extends App_Model
{
    private $table = 'catering_event_staff';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get event staff assignments
     * @param int $event_id
     * @return array
     */
    public function get_event_staff($event_id)
    {
        $this->db->select(
            '
            ces.*,
            CONCAT(s.firstname, " ", s.lastname) as staff_name,
            s.email as staff_email,
            s.profile_image,
            csr.role_name,
            csr.description as role_description,
            csr.default_hourly_rate,
            csr.color as role_color
        '
        );
        $this->db->from(db_prefix() . $this->table . ' ces');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ces.staff_id');
        $this->db->join(db_prefix() . 'catering_staff_roles csr', 'csr.role_name = ces.role', 'left');
        $this->db->where('ces.event_id', $event_id);
        $this->db->order_by('ces.shift_start', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get staff assignment by ID
     * @param int $id
     * @return object|null
     */
    public function get_staff_assignment($id)
    {
        $this->db->select(
            '
            ces.*,
            CONCAT(s.firstname, " ", s.lastname) as staff_name,
            s.email as staff_email,
            s.profile_image,
            csr.role_name,
            csr.description as role_description,
            csr.default_hourly_rate,
            csr.color as role_color
        '
        );
        $this->db->from(db_prefix() . $this->table . ' ces');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ces.staff_id');
        $this->db->join(db_prefix() . 'catering_staff_roles csr', 'csr.role_name = ces.role', 'left');
        $this->db->where('ces.id', $id);

        return $this->db->get()->row();
    }

    /**
     * Add staff assignment to event
     * @param array $data
     * @return int|bool
     */
    public function add_staff_assignment($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');

        // Calculate hours
        $start = strtotime($data['shift_start']);
        $end = strtotime($data['shift_end']);
        $data['hours'] = ($end - $start) / 3600;

        $this->db->insert(db_prefix() . $this->table, $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id)
        {
            log_activity('Staff Assignment Added [ID: ' . $insert_id . ', Event: ' . $data['event_id'] . ']');
            return $insert_id;
        }

        return FALSE;
    }

    /**
     * Update staff assignment
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_staff_assignment($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Recalculate hours if times changed
        if (isset($data['shift_start']) || isset($data['shift_end']))
        {
            $assignment = $this->get_staff_assignment($id);
            $start = strtotime($data['shift_start'] ?? $assignment->shift_start);
            $end = strtotime($data['shift_end'] ?? $assignment->shift_end);
            $data['hours'] = ($end - $start) / 3600;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->table, $data);

        if ($this->db->affected_rows() > 0)
        {
            log_activity('Staff Assignment Updated [ID: ' . $id . ']');
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Remove staff assignment
     * @param int $id
     * @return bool
     */
    public function remove_staff_assignment($id)
    {
        $assignment = $this->get_staff_assignment($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . $this->table);

        if ($this->db->affected_rows() > 0)
        {
            log_activity('Staff Assignment Removed [ID: ' . $id . ', Event: ' . $assignment->event_id . ']');
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Get staff summary for event
     * @param int $event_id
     * @return array
     */
    public function get_staff_summary($event_id)
    {
        $this->db->select(
            '
            COUNT(*) as total_staff,
            SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed_count,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = "declined" THEN 1 ELSE 0 END) as declined_count,
            SUM(hours) as total_hours,
            AVG(hourly_rate) as avg_hourly_rate,
            SUM(hours * hourly_rate) as total_cost
        '
        );
        $this->db->where('event_id', $event_id);
        $result = $this->db->get(db_prefix() . $this->table)->row();

        return [
            'total_staff' => $result->total_staff ?? 0,
            'confirmed' => $result->confirmed_count ?? 0,
            'pending' => $result->pending_count ?? 0,
            'declined' => $result->declined_count ?? 0,
            'total_hours' => $result->total_hours ?? 0,
            'avg_hourly_rate' => $result->avg_hourly_rate ?? 0,
            'total_cost' => $result->total_cost ?? 0,
        ];
    }

    /**
     * Get available staff for event
     * @param int $event_id
     * @param string $shift_start
     * @param string $shift_end
     * @return array
     */
    public function get_available_staff($event_id, $shift_start, $shift_end)
    {
        // Get staff who are not already assigned during this time
        $this->db->select('s.staffid, CONCAT(s.firstname, " ", s.lastname) as staff_name, s.email');
        $this->db->from(db_prefix() . 'staff s');
        $this->db->where('s.active', 1);
        $this->db->where('s.staffid NOT IN (
            SELECT staff_id FROM ' . db_prefix() . 'catering_event_staff 
            WHERE event_id = ' . $event_id . ' 
            AND status IN ("pending", "confirmed")
            AND (
                (shift_start <= "' . $shift_start . '" AND shift_end > "' . $shift_start . '")
                OR (shift_start < "' . $shift_end . '" AND shift_end >= "' . $shift_end . '")
                OR (shift_start >= "' . $shift_start . '" AND shift_end <= "' . $shift_end . '")
            )
        )');

        return $this->db->get()->result_array();
    }

    /**
     * Get all staff roles
     * @return array
     */
    public function get_staff_roles()
    {
        $this->db->where('active', 1);
        $this->db->order_by('display_order', 'ASC');
        $this->db->order_by('role_name', 'ASC');

        return $this->db->get(db_prefix() . 'catering_staff_roles')->result_array();
    }

    /**
     * Check for scheduling conflicts
     * @param int $staff_id
     * @param string $shift_start
     * @param string $shift_end
     * @param int $exclude_id (optional, for updates)
     * @return array
     */
    public function check_scheduling_conflicts($staff_id, $shift_start, $shift_end, $exclude_id = NULL)
    {
        $this->db->select('ces.*, ce.event_name');
        $this->db->from(db_prefix() . $this->table . ' ces');
        $this->db->join(db_prefix() . 'catering_events ce', 'ce.eventid = ces.event_id');
        $this->db->where('ces.staff_id', $staff_id);
        $this->db->where('ces.status IN ("pending", "confirmed")');
        $this->db->where('(
            (ces.shift_start <= "' . $shift_start . '" AND ces.shift_end > "' . $shift_start . '")
            OR (ces.shift_start < "' . $shift_end . '" AND ces.shift_end >= "' . $shift_end . '")
            OR (ces.shift_start >= "' . $shift_start . '" AND ces.shift_end <= "' . $shift_end . '")
        )');

        if ($exclude_id)
        {
            $this->db->where('ces.id !=', $exclude_id);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Update staff assignment status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function update_assignment_status($id, $status)
    {
        $valid_statuses = ['pending', 'confirmed', 'declined', 'completed'];
        
        if (!in_array($status, $valid_statuses))
        {
            return FALSE;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->table, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get staff assignments by date range
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_assignments_by_date_range($start_date, $end_date)
    {
        $this->db->select(
            '
            ces.*,
            CONCAT(s.firstname, " ", s.lastname) as staff_name,
            s.email as staff_email,
            ce.event_name,
            csr.color as role_color
        '
        );
        $this->db->from(db_prefix() . $this->table . ' ces');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = ces.staff_id');
        $this->db->join(db_prefix() . 'catering_events ce', 'ce.eventid = ces.event_id');
        $this->db->join(db_prefix() . 'catering_staff_roles csr', 'csr.role_name = ces.role', 'left');
        $this->db->where('ces.shift_start >=', $start_date);
        $this->db->where('ces.shift_start <=', $end_date);
        $this->db->order_by('ces.shift_start', 'ASC');

        return $this->db->get()->result_array();
    }
}
