<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Domain_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();

    }
    /**
     * Retrieve hm_domains data.
     *
     * @param int|string $id Optional ID of the hm_domains.
     * @return array|object Returns all records if no ID is provided, otherwise returns a single record.
     */
    // public function get($id = ''){
    //     if($id == ''){
    //         return  $this->db->get(db_prefix().'hm_domains')->result_array();
    //     }else{
    //         $this->db->where('id',$id);
    //         return $this->db->get(db_prefix().'hm_domains')->row();
    //     }
    // }

    public function get($id = ''){
        // If no $id is provided, fetch all data
        if ($id == '') {
            // Join the hm_domains table with the client table on client_id
            $this->db->select(db_prefix().'hm_domains.*');
            $this->db->from(db_prefix().'hm_domains');
            return $this->db->get()->result_array(); // Fetch all the results as an array
        } else {
            // If an $id is provided, fetch only the matching row
            $this->db->select(db_prefix().'hm_domains.* ');
            $this->db->from(db_prefix().'hm_domains');
            $this->db->where(db_prefix().'hm_domains.id', $id);
            return $this->db->get()->row(); // Fetch a single result as an object
        }
    }
    


  

     /**
     * Add a new hm_domains record.
     *
     * @param array $data Array of hm_domains data.
     * @return int The ID of the newly inserted hm_domains.
     */
    public function add($data){
        $this->db->insert(db_prefix() . 'hm_domains', $data);
        return $this->db->insert_id();
    }
    /**
     * Update an existing hm_domains record.
     *
     * @param array $data Array of hm_domains data, including ID for the record to be updated.
     * @return bool|int Returns the number of affected rows or false if no ID is provided.
     */
   
    public function update($id,$data)
    {
        if ($id) {
            unset($data['id']);
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'hm_domains', $data);

            return $this->db->affected_rows();
        }

        return false;
    }
     /**
     * Retrieve all hm_domainss created by the current staff member.
     *
     * @return array Returns an array of hm_domainss created by the current staff member.
     */
    public function all(){
        $CI = &get_instance();
        $CI->db->from(db_prefix() . 'hm_domains');
        $CI->db->where('created_by', get_staff_user_id());
        $query = $CI->db->get();
        return $query->result_array();

    }
    /**
     * Delete a hm_domains record by ID.
     *
     * @param int $id ID of the hm_domains to be deleted.
     * @return bool Returns true if the record was deleted successfully, otherwise false.
     */
    public function delete($id)
    {
        if (isset($id) && is_numeric($id)) {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'hm_domains');
            return ($this->db->affected_rows() > 0);
        }
        return false;
    }

     /**
     * Get Projects
     * @param  mixed project (Optional)
     * @return mixed     object or array
     */
    public function get_projects()
    {
        return $this->db->get(db_prefix() . 'projects')->result_array();
    }

     /**
     * Get Projects
     * @param  mixed project (Optional)
     * @return mixed     object or array
     */
    public function get_clients()
    {
        return $this->db->get(db_prefix() . 'clients')->result_array();
    }
}