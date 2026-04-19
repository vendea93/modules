<?php

use app\services\AbstractKanban;

class Events_kanban extends AbstractKanban {
	protected function table(): string
	{
		return 'catering_events';
	}

	public function defaultSortDirection(): string
	{
		return 'ASC';
	}

	public function defaultSortColumn(): string
	{
		return 'kanban_order';
	}

	public function limit()
	{
		return 10;
	}

	protected function applySearchQuery($q): self
	{
		$q = $this->ci->db->escape_like_str($q);
		$this->ci->db->where('('.db_prefix().'catering_events.event_name LIKE "%'.$q.'%" ESCAPE \'!\'  OR '.db_prefix().'catering_events.venue_name LIKE "%'.$q.'%" ESCAPE \'!\')');

		return $this;
	}

	protected function initiateQuery(): self
	{
		$where = '';

//		if (staff_cant('view', 'tasks'))
//		{
//			$where = get_tasks_where_string(FALSE);
//		}

		$this->ci->db->select(
			db_prefix().'catering_events.*, 
            '.db_prefix().'clients.company as client_company,'.
			db_prefix().'leads.name as lead_name,'.
			db_prefix().'catering_event_types.name as event_type_name,
            CONCAT('.db_prefix().'staff.firstname, " ", '.db_prefix().'staff.lastname) as created_by_name'
		);
		$this->ci->db->from(db_prefix().'catering_events');
		$this->ci->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'catering_events.client_id', 'left');
		$this->ci->db->join(db_prefix().'leads', db_prefix().'leads.id = '.db_prefix().'catering_events.lead_id', 'left');
		$this->ci->db->join(db_prefix().'catering_event_types', db_prefix().'catering_event_types.etid = '.db_prefix().'catering_events.event_type_id', 'left');
		$this->ci->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().'catering_events.created_by', 'left');
		$this->ci->db->where(db_prefix().'catering_events.status', $this->status);

		if ( ! empty($where))
		{
			$this->ci->db->where($where);
		}

		return $this;
	}


	public static function updateOrder($data, $column, $table, $status, $statusColumnName = 'status', $primaryKey = 'id')
	{
		$ci = &get_instance();

		$batch = [];
		$allOrder = [];
		$allIds = [];

		foreach ($data as $order)
		{
			$allIds[] = $order[0];
			$allOrder[] = $order[1];
			$batch[] = [
				$primaryKey => $order[0],
				$column => $order[1],
			];
		}

		$max = max($allOrder);

		$query = 'UPDATE '.db_prefix().$table.' SET '.$column.'='.$max.'+'.$column.' WHERE '.$primaryKey.' NOT IN ('.implode(',', $allIds).') AND '.$statusColumnName.'='."'".$status."'";
		$ci->db->query($query);

		$ci->db->update_batch($table, $batch, $primaryKey);
	}

}
