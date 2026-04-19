<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_event_notes_model extends App_Model {
	private $table = 'catering_event_notes';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get all notes for an event
	 * @param int $event_id
	 * @return array
	 */
	public function get_event_notes($event_id)
	{
		$this->db->select(
			db_prefix().$this->table.'.*,
			CONCAT('.db_prefix().'staff.firstname, " ", '.db_prefix().'staff.lastname) as staff_name,
			'.db_prefix().'staff.profile_image'
		);
		$this->db->from(db_prefix().$this->table);
		$this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().$this->table.'.created_by', 'left');
		$this->db->where(db_prefix().$this->table.'.event_id', $event_id);
		$this->db->order_by(db_prefix().$this->table.'.created_at', 'DESC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get single note by ID
	 * @param int $note_id
	 * @return object|null
	 */
	public function get($note_id)
	{
		$this->db->select(
			db_prefix().$this->table.'.*,
			CONCAT('.db_prefix().'staff.firstname, " ", '.db_prefix().'staff.lastname) as staff_name'
		);
		$this->db->from(db_prefix().$this->table);
		$this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().$this->table.'.created_by', 'left');
		$this->db->where(db_prefix().$this->table.'.id', $note_id);

		return $this->db->get()->row();
	}

	/**
	 * Add new note
	 * @param array $data
	 * @return int|bool Note ID or false
	 */
	public function add($data)
	{
		$data['created_by'] = get_staff_user_id();
		$data['created_at'] = date('Y-m-d H:i:s');

		$this->db->insert(db_prefix().$this->table, $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			// Log activity
			$event = $this->get_event_info($data['event_id']);
			log_activity('New Note Added to Event [Event: '.$event->event_name.', Note ID: '.$insert_id.']');

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Update note
	 * @param int $note_id
	 * @param array $data
	 * @return bool
	 */
	public function update($note_id, $data)
	{
		$note = $this->get($note_id);

		if ( ! $note)
		{
			return FALSE;
		}

		// Only allow creator or admin to edit
		if ($note->created_by != get_staff_user_id() && ! is_admin())
		{
			return FALSE;
		}

		$data['updated_at'] = date('Y-m-d H:i:s');

		$this->db->where('id', $note_id);
		$this->db->update(db_prefix().$this->table, $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Note Updated [Note ID: '.$note_id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete note
	 * @param int $note_id
	 * @return bool
	 */
	public function delete($note_id)
	{
		$note = $this->get($note_id);

		if ( ! $note)
		{
			return FALSE;
		}

		// Only allow creator or admin to delete
		if ($note->created_by != get_staff_user_id() && ! is_admin())
		{
			return FALSE;
		}

		$this->db->where('id', $note_id);
		$this->db->delete(db_prefix().$this->table);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Note Deleted [Note ID: '.$note_id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get notes count for event
	 * @param int $event_id
	 * @param bool $client_visible_only
	 * @return int
	 */
	public function get_notes_count($event_id, $client_visible_only = FALSE)
	{
		$this->db->where('event_id', $event_id);

		if ($client_visible_only)
		{
			$this->db->where('visible_to_client', 1);
		}

		return $this->db->count_all_results(db_prefix().$this->table);
	}

	/**
	 * Get notes statistics for event
	 * @param int $event_id
	 * @return array
	 */
	public function get_notes_stats($event_id)
	{
		$total = $this->get_notes_count($event_id);
		$client_visible = $this->get_notes_count($event_id, TRUE);
		$internal = $total - $client_visible;

		return [
			'total' => $total,
			'client_visible' => $client_visible,
			'internal' => $internal,
		];
	}

	/**
	 * Get event info helper
	 * @param int $event_id
	 * @return object|null
	 */
	private function get_event_info($event_id)
	{
		$this->db->select('event_name');
		$this->db->from(db_prefix().'catering_events');
		$this->db->where('eventid', $event_id);

		return $this->db->get()->row();
	}

	/**
	 * Get recent notes across all events (for dashboard/reports)
	 * @param int $limit
	 * @return array
	 */
	public function get_recent_notes($limit = 10)
	{
		$this->db->select(
			db_prefix().$this->table.'.*,
			CONCAT('.db_prefix().'staff.firstname, " ", '.db_prefix().'staff.lastname) as staff_name,
			'.db_prefix().'catering_events.event_name,
			'.db_prefix().'catering_events.eventid'
		);
		$this->db->from(db_prefix().$this->table);
		$this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().$this->table.'.created_by', 'left');
		$this->db->join(db_prefix().'catering_events', db_prefix().'catering_events.eventid = '.db_prefix().$this->table.'.event_id', 'left');
		$this->db->order_by(db_prefix().$this->table.'.created_at', 'DESC');
		$this->db->limit($limit);

		return $this->db->get()->result_array();
	}

	/**
	 * Search notes
	 * @param string $search_term
	 * @param int|null $event_id
	 * @return array
	 */
	public function search($search_term, $event_id = NULL)
	{
		$this->db->select(
			db_prefix().$this->table.'.*,
			CONCAT('.db_prefix().'staff.firstname, " ", '.db_prefix().'staff.lastname) as staff_name,
			'.db_prefix().'catering_events.event_name'
		);
		$this->db->from(db_prefix().$this->table);
		$this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().$this->table.'.created_by', 'left');
		$this->db->join(db_prefix().'catering_events', db_prefix().'catering_events.eventid = '.db_prefix().$this->table.'.event_id', 'left');

		if ($event_id)
		{
			$this->db->where(db_prefix().$this->table.'.event_id', $event_id);
		}

		$this->db->like(db_prefix().$this->table.'.description', $search_term);
		$this->db->order_by(db_prefix().$this->table.'.created_at', 'DESC');

		return $this->db->get()->result_array();
	}
}
