<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * This class describes a Reputation model.
 */
class Reputation_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets the vendor.
     *
     * @param      string        $id     The identifier
     * @param      array|string  $where  The where
     *
     * @return     <type>        The vendor or list vendors.
     */
    public function get_vendor($id = '', $where = [])
    {
        $this->db->select(implode(',', prefixed_table_fields_array(db_prefix() . 'pur_vendor')) );

        $this->db->join(db_prefix() . 'countries', '' . db_prefix() . 'countries.country_id = ' . db_prefix() . 'pur_vendor.country', 'left');


        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $this->db->where($where);
        }

        if (is_numeric($id)) {

            $this->db->where(db_prefix().'pur_vendor.userid', $id);
            $vendor = $this->db->get(db_prefix() . 'pur_vendor')->row();

            if ($vendor && get_option('company_requires_vat_number_field') == 0) {
                $vendor->vat = null;
            }


            return $vendor;

        }

        $this->db->order_by('company', 'asc');

        return $this->db->get(db_prefix() . 'pur_vendor')->result_array();
    }

    /**
     * Adds a vendor.
     */
    public function add_vendor($data){

        $data = $this->check_zero_columns($data);
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();

        $this->db->insert(db_prefix() . 'pur_vendor', $data);
        $userid = $this->db->insert_id();    
        
        if ($userid) {
            return $userid;
        }
        return false;
    }

    /**
     * { update vendor }
     *
     * @param      <type>   $data            The data
     * @param      <type>   $id              The identifier
     * @param      boolean  $client_request  The client request
     *
     * @return     boolean 
     */
    public function update_vendor($data, $id)
    {

        $affectedRows = 0;

        $data = $this->check_zero_columns($data);

        $this->db->where('userid', $id);
        $this->db->update(db_prefix() . 'pur_vendor', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }


        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * { delete vendor }
     *
     * @param      <type>   $id     The identifier
     *
     * @return     boolean  
     */
    public function delete_vendor($id)
    {
        $affectedRows = 0;

        $this->db->where('userid', $id);
        $this->db->delete(db_prefix() . 'pur_vendor');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;

            
        }
        if ($affectedRows > 0) {

            return true;
        }

        return false;
    }

    /**
     * { check zero columns }
     *
     * @param      <type>  $data   The data
     *
     * @return     array  
     */
    private function check_zero_columns($data)
    {
        if (isset($data['default_currency']) && $data['default_currency'] == '' || !isset($data['default_currency'])) {
            $data['default_currency'] = 0;
        }

        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }

        if (isset($data['billing_country']) && $data['billing_country'] == '' || !isset($data['billing_country'])) {
            $data['billing_country'] = 0;
        }

        if (isset($data['shipping_country']) && $data['shipping_country'] == '' || !isset($data['shipping_country'])) {
            $data['shipping_country'] = 0;
        }

        return $data;
    }

    /**
     * add new topic
     * @param array $data
     * @return integer
     */
    public function add_topic($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'rep_topics', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    /**
     * update topic
     * @param array $data
     * @param integer $id
     * @return integer
     */
    public function update_topic($data, $id)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_topics', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * get topic
     * @param  integer $id    member type id
     * @param  array  $where
     * @return object
     */
    public function get_topic($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'rep_topics')->row();
        }

        $this->db->where($where);
        $this->db->order_by('id', 'desc');
        $topics = $this->db->get(db_prefix() . 'rep_topics')->result_array();

        return $topics;
    }
    /**
     * delete topic
     * @param integer $id
     * @return boolean
     */

    public function delete_topic($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'rep_topics');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change topic active
     * @param  [integer] $id     
     * @param  [string] $status 
     */
    public function change_topic_active($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_topics', [
            'active' => $status,
        ]);
    }

    /**
     * add new project
     * @param array $data
     * @return integer
     */
    public function add_project($data)
    {
        if(isset($data['keywords'])){
            $data['keywords'] = json_encode($data['keywords']);
        }

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'rep_projects', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    /**
     * update project
     * @param array $data
     * @param integer $id
     * @return integer
     */
    public function update_project($data, $id)
    {
        if (isset($data['DataTables_Table_0_length'])) {
            unset($data['DataTables_Table_0_length']);
        }
        
        if (isset($data['id'])) {
            unset($data['id']);
        }

        if(isset($data['keywords'])){
            $data['keywords'] = json_encode($data['keywords']);
        }

        if(isset($data['tripadvisor'])){
            $data['tripadvisor'] = json_encode($data['tripadvisor']);
        }

        if(isset($data['booking'])){
            $data['booking'] = json_encode($data['booking']);
        }

        if(isset($data['app_store'])){
            $data['app_store'] = json_encode($data['app_store']);
        }

        if(isset($data['google_play'])){
            $data['google_play'] = json_encode($data['google_play']);
        }

        if(isset($data['trustpilot'])){
            $data['trustpilot'] = json_encode($data['trustpilot']);
        }

        if(isset($data['spotify'])){
            $data['spotify'] = json_encode($data['spotify']);
        }

        if(isset($data['apple_itunes'])){
            $data['apple_itunes'] = json_encode($data['apple_itunes']);
        }

        if(isset($data['youtube'])){
            $data['youtube'] = json_encode($data['youtube']);
        }

        if(isset($data['vimeo'])){
            $data['vimeo'] = json_encode($data['vimeo']);
        }

        if(isset($data['tiktok'])){
            $data['tiktok'] = json_encode($data['tiktok']);
        }

        if(isset($data['news_source'])){
            $data['news_source'] = json_encode($data['news_source']);
        }

        if(isset($data['blog_source'])){
            $data['blog_source'] = json_encode($data['blog_source']);
        }

        if(isset($data['web_source'])){
            $data['web_source'] = json_encode($data['web_source']);
        }

        if(isset($data['telegram'])){
            $data['telegram'] = json_encode($data['telegram']);
        }

        if(isset($data['x_twitter'])){
            $data['x_twitter'] = json_encode($data['x_twitter']);
        }

        if(isset($data['excluded_sites'])){
            $data['excluded_sites'] = json_encode($data['excluded_sites']);
        }

        if(isset($data['excluded_social_media_authors'])){
            $data['excluded_social_media_authors'] = json_encode($data['excluded_social_media_authors']);
        }


        if(isset($data['tab']) && $data['tab'] == 'sources'){
            if(isset($data['active_sources_x_twitter'])){
                $data['active_sources_x_twitter'] = 1;
            }else{
                $data['active_sources_x_twitter'] = 0;
            }

            if(isset($data['active_sources_news'])){
                $data['active_sources_news'] = 1;
            }else{
                $data['active_sources_news'] = 0;
            }

            if(isset($data['active_sources_web'])){
                $data['active_sources_web'] = 1;
            }else{
                $data['active_sources_web'] = 0;
            }

            if(isset($data['active_sources_blogs'])){
                $data['active_sources_blogs'] = 1;
            }else{
                $data['active_sources_blogs'] = 0;
            }
            
            if(isset($data['active_sources_videos'])){
                $data['active_sources_videos'] = 1;
            }else{
                $data['active_sources_videos'] = 0;
            }

            if(isset($data['active_sources_podcast'])){
                $data['active_sources_podcast'] = 1;
            }else{
                $data['active_sources_podcast'] = 0;
            }

            if(isset($data['active_sources_forums'])){
                $data['active_sources_forums'] = 1;
            }else{
                $data['active_sources_forums'] = 0;
            }

            if(isset($data['active_sources_instagram'])){
                $data['active_sources_instagram'] = 1;
            }else{
                $data['active_sources_instagram'] = 0;
            }

            if(isset($data['active_sources_facebook'])){
                $data['active_sources_facebook'] = 1;
            }else{
                $data['active_sources_facebook'] = 0;
            }

            if(isset($data['active_sources_youtube'])){
                $data['active_sources_youtube'] = 1;
            }else{
                $data['active_sources_youtube'] = 0;
            }

            unset($data['tab']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_projects', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * get project
     * @param  integer $id    member type id
     * @param  array  $where
     * @return object
     */
    public function get_project($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'rep_projects')->row();
        }

        $this->db->where($where);
        $this->db->order_by('id', 'desc');
        $projects = $this->db->get(db_prefix() . 'rep_projects')->result_array();

        return $projects;
    }
    /**
     * delete project
     * @param integer $id
     * @return boolean
     */

    public function delete_project($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'rep_projects');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change project active
     * @param  [integer] $id     
     * @param  [string] $status 
     */
    public function change_project_active($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_projects', [
            'active' => $status,
        ]);
    }

    /**
     * add new notification
     * @param array $data
     * @return integer
     */
    public function add_notification($data)
    {
        if(isset($data['sources'])){
            $data['sources'] = implode(',', $data['sources']);
        }

        if(isset($data['sentiment'])){
            $data['sentiment'] = implode(',', $data['sentiment']);
        }

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'rep_notifications', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    /**
     * update notification
     * @param array $data
     * @param integer $id
     * @return integer
     */
    public function update_notification($data, $id)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }

        if(isset($data['sources'])){
            $data['sources'] = implode(',', $data['sources']);
        }

        if(isset($data['sentiment'])){
            $data['sentiment'] = implode(',', $data['sentiment']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_notifications', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * get notification
     * @param  integer $id    member type id
     * @param  array  $where
     * @return object
     */
    public function get_notification($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'rep_notifications')->row();
        }

        $this->db->where($where);
        $this->db->order_by('id', 'desc');
        $notifications = $this->db->get(db_prefix() . 'rep_notifications')->result_array();

        return $notifications;
    }
    /**
     * delete notification
     * @param integer $id
     * @return boolean
     */

    public function delete_notification($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'rep_notifications');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * [update_setting]
     * @param  [array] $data
     * @return [boolean]      
     */
    public function update_setting($data){
        $affectedRows = 0;

        foreach ($data as $key => $value) {
            $this->db->where('name', $key);
            $this->db->update(db_prefix() . 'options', [
                    'value' => $value,
                ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    /**
     * get account
     * @param  integer $id    member type id
     * @param  array  $where
     * @return object
     */
    public function get_account($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'rep_accounts')->row();
        }

        $this->db->where($where);
        $this->db->order_by('id', 'desc');
        $accounts = $this->db->get(db_prefix() . 'rep_accounts')->result_array();

        return $accounts;
    }

    /**
     * delete account
     * @param integer $id
     * @return boolean
     */
    public function delete_account($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'rep_accounts');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * add new mention
     * @param array $data
     * @return integer
     */
    public function add_mention($data)
    {
        $data['time'] = to_sql_date($data['time'], true);
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $data['add_manually'] = 1;
        $data['status'] = 'new';
        $this->db->insert(db_prefix() . 'rep_mentions', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    /**
     * update mention
     * @param array $data
     * @param integer $id
     * @return integer
     */
    public function update_mention($data, $id)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $data['time'] = to_sql_date($data['time'], true);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_mentions', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * get mention
     * @param  integer $id    member type id
     * @param  array  $where
     * @return object
     */
    public function get_mention($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'rep_mentions')->row();
        }

        $this->db->where($where);
        $this->db->order_by('time', 'desc');
        $mentions = $this->db->get(db_prefix() . 'rep_mentions')->result_array();

        return $mentions;
    }
    /**
     * delete mention
     * @param integer $id
     * @return boolean
     */

    public function delete_mention($id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_mentions', ['status' => 'deleted']);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * set_default_project
     * @param integer $id 
     * @return boolean
     */
    public function set_default_project($id){
        $staffid = get_staff_user_id();
        $this->db->where('staffid', $staffid);
        $this->db->update(db_prefix().'staff', ['rep_base_project_id' => $id]);
        
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * add social_accounts
     * @param array $data
     * @param  integer $id 
     * @return boolean
     */
    public function add_social_account($data){

        $data['project_id'] = rep_get_base_workspace_id();
        $data['active'] = 1;
        $data['addedfrom'] = get_staff_user_id();
        $data['dateadded'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix().'rep_accounts', $data);
        
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return true;
        }

        return false;
    }

    /**
     * update social_accounts
     * @param array $data
     * @param  integer $id 
     * @return boolean
     */
    public function update_social_account($data, $id){
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'rep_accounts', $data);
       
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * delete social_accounts
     * @param integer $id
     * @return boolean
     */
    public function delete_social_account($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'rep_accounts');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get social_accounts
     * @param  mixed $id social_accounts id (Optional)
     * @param  string $type
     * @return mixed     object or array
     */
    public function get_social_accounts($id = '', $type = '')
    {
        $workspace_id = rep_get_base_workspace_id();
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'rep_accounts')->row();
        }

        $this->db->where('project_id', $workspace_id);
        if ($type != '') {
            $this->db->where('type', $type);
        }

            $this->db->where('active', 1);
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'rep_accounts')->result_array();
    }

    /**
     * change account active
     * @param  [integer] $id     
     * @param  [string] $status 
     */
    public function change_account_active($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_accounts', [
            'active' => $status,
        ]);
    }

    /**
     * [youtube_connect_save]
     * @param  [array] $data 
     * @return [boolean]  
     */
    public function youtube_connect_save($data){
        $page_id = $data['page_id'];
        $this->db->where('id', $data['account_id']);
        $this->db->update(db_prefix().'rep_accounts', 
            [
                'user_id' => $page_id,
                'page_id' => $page_id,
                'access_token' => $data['access_token'],
                'expires_in' => $data['expires_in'],
                'refresh_token' => $data['refresh_token'],
                'status' => 1,
            ]
        );
        
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * [facebook_connect_save]
     * @param  [string] $data
     * @return [boolean] 
     */
    public function facebook_connect_save($data){
        $pages = json_decode($data['pages'], true);
        $account = json_decode($data['account'], true);
        $page_arr = [];
        foreach ($pages as $key => $value) {
            $page_arr[$value['id']] = $value;
        }
        $page_id = $data['page_id'];
        $this->db->where('id', $data['account_id']);
        $this->db->update(db_prefix().'rep_accounts', 
            [
                'user_id' => $account['id'],
                'page_id' => $page_id,
                'access_token' => $page_arr[$page_id]['access_token'],
                'expires_in' => $data['expires_in'],
                'category' => $page_arr[$page_id]['category'],
                'status' => 1,
            ]
        );
        
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * [instagram_connect_save]
     * @param  [Array] $data
     * @return [Boolean]
     */
    public function instagram_connect_save($data){
        $pages = json_decode($data['pages'], true);
        $page_arr = [];
        foreach ($pages as $key => $value) {
            $page_arr[$value['id']] = $value;
        }
        $page_id = $data['page_id'];
        $this->db->where('id', $data['account_id']);
        $this->db->update(db_prefix().'rep_accounts', 
            [
                'user_id' => $page_id,
                'page_id' => $page_id,
                'access_token' => $data['access_token'],
                'expires_in' => $data['expires_in'],
                'status' => 1,
            ]
        );
        
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * [executeRequest]
     * @param  [string] $url        
     * @param  array  $parameters 
     * @param  string $http_header
     * @param  string $http_method
     * @return [array]             
     */
    public function executeRequest($url, $parameters = array(), $http_header = '', $http_method = '')
    {

      $curl_options = array();

      switch($http_method){
            case 'GET':
              $curl_options[CURLOPT_HTTPGET] = 'true';
              if (is_array($parameters) && count($parameters) > 0) {
                $url .= '?' . http_build_query($parameters);
              } elseif ($parameters) {
                $url .= '?' . $parameters;
              }
              break;
            case 'POST':
              $curl_options[CURLOPT_POST] = '1';
              if(is_array($parameters) && count($parameters) > 0){
                $body = http_build_query($parameters);
                $curl_options[CURLOPT_POSTFIELDS] = $body;
              }
              break;
            default:
              break;
      }
      /**
      * An array of HTTP header fields to set, in the format array('Content-type: text/plain', 'Content-length: 100')
      */
      if(is_array($http_header)){
            $header = array();
            foreach($http_header as $key => $value) {
                $header[] = "$key: $value";
            }
            $curl_options[CURLOPT_HTTPHEADER] = $header;
      }

      $curl_options[CURLOPT_URL] = $url;
      $ch = curl_init();


      curl_setopt_array($ch, $curl_options);

      //Don't display, save it on result
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      //Execute the Curl Request
      $result = curl_exec($ch);

      $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );

      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


      $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
       if ($curl_error = curl_error($ch)) {
           throw new Exception($curl_error);
       } else {
           $json_decode = json_decode($result, true);
       }
       curl_close($ch);

       return $json_decode;
    }

    /**
     * [callAPI]
     * @param  [string] $url    
     * @param  [array] $params 
     * @param  [array] $header 
     * @param  string $method 
     * @return [array]         
     */
    public function callAPI($url, $params, $header, $method = 'POST'){
            $data_string = json_encode($params);

            $curl = curl_init($url);
            
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

            if($method == 'POST' || $method == 'PUT'){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            }

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 120);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            
            $result = curl_exec($curl);
            $result = json_decode($result, true);

            return $result;
    }

    /**
     * [account_connect_save]
     * @param  [array] $data      
     * @param  [string] $account_id 
     * @return [Boolean]      
     */
    public function account_connect_save($data, $account_id)
    {
        $this->db->where('id', $account_id);
        $this->db->update(db_prefix().'rep_accounts', 
            $data
        );

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * [get_facebook_data]
     * @param  [integer] $id facebook account id
     * @return [boolean]    
     */
    public function get_facebook_data($project_id, $id){
        $project = $this->get_project($project_id);
        $cases = $this->get_case('', ['active' => 1]);
        $keywords = json_decode($project->keywords, true);
        $keywords = array_map('mb_strtoupper', $keywords);

        $excluded_keywords = explode(',', $project->excluded_keywords);

        $this->db->where('platform', 'facebook');
        $mentions = $this->db->get(db_prefix() . 'rep_mentions')->result_array();
        $mention_arr = array_column($mentions, 'comment_id');



        $topics = $this->get_topic('', ['active' => 1]);
        $positive_topics = [];
        $negative_topics = [];
        $scales_topics = [];
        foreach ($topics as $key => $value) {
            $scales_topics[$value['id']] = $value['scales'];
            if($value['type'] == 'negative'){
                $negative_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }else{
                $positive_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }
        }
        
        $config = rep_get_facebook_config();
        $fb = new \Facebook\Facebook($config);
        $account = $this->get_social_accounts($id);

        try {
            $node_default = [
                'datecreated' => date('Y-m-d H:i:s'),
                'account_id' => $account->id,
            ];

            $data_insert = [];
            $response = $fb->get('/'.$account->page_id.'/feed', $account->access_token);
            $posts = $response->getDecodedBody();

            if(isset($posts['data'])){
                foreach ($posts['data'] as $post) {
                    $response = $fb->get('/'.$post['id'].'/comments', $account->access_token);
                    $comments = $response->getDecodedBody();

                    if(isset($comments['data'])){
                        foreach ($comments['data'] as $comment) {
                            $check_excluded = false;

                            if(in_array($comment['id'], $mention_arr)){
                                continue;
                            }

                            $message = mb_strtoupper(trim($comment['message']));
                            $sentiment = 'Neutral';
                            $scales = 0;
                            $topic = 0;

                            foreach ($excluded_keywords as $key => $excluded) {

                                if (stripos($message, mb_strtoupper(trim($excluded))) !== false) {
                                    $check_excluded = true;
                                }
                            }

                            if (!$check_excluded) {
                                foreach ($positive_topics as $key => $positive) {
                                    if (stripos($message, $positive) !== false) {
                                        $sentiment = 'Positive';
                                        $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                                        $topic = $key;
                                    }
                                }

                                foreach ($negative_topics as $key => $negative) {
                                    if (stripos($message, $negative) !== false) {
                                        $sentiment = 'Negative';
                                        $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                                        $topic = $key;
                                    }
                                }

                                foreach ($keywords as $keyword) {
                                    if (stripos($message, $keyword) !== false) { 
                                        $timestamp = strtotime($comment['created_time']);
                                        $node = $node_default;
                                        $node['project_id'] = $project_id;
                                        $node['platform'] = 'facebook';
                                        $node['content'] = $comment['message'];
                                        $node['time'] = date('Y-m-d H:i:s', $timestamp);
                                        $node['author_name'] = $comment['from']['name'];
                                        $node['author_id'] = $comment['from']['id'];
                                        $node['post_id'] = $post['id'];
                                        $node['sentiment'] = $sentiment;
                                        $node['comment_id'] = $comment['id'];
                                        $node['link'] = 'https://www.facebook.com/'.$comment['id'];
                                        $node['title'] = '';
                                        $node['status'] = 'new';
                                        $node['keyword'] = $keyword;
                                        $node['scales'] = $scales;
                                        $node['topic'] = $topic;
                                        $data_insert[] = $node;

                                        break;
                                    }
                                }
                            }


                            $response = $fb->get('/'.$comment['id'].'/comments', $account->access_token);
                            $_comments = $response->getDecodedBody();

                            if(isset($_comments['data'])){
                                foreach ($_comments['data'] as $_comment) {
                                    $check_excluded = false;

                                    $message = mb_strtoupper(trim($_comment['message']));
                                    $sentiment = 'Neutral';
                                    $scales = 0;
                                    $topic = 0;
                                    foreach ($excluded_keywords as $key => $excluded) {
                                        if (stripos($message, mb_strtoupper(trim($excluded))) !== false) {
                                            $check_excluded = true;
                                        }
                                    }

                                    if (!$check_excluded) {
                                        foreach ($positive_topics as $key => $positive) {
                                            if (stripos($message, $positive) !== false) {
                                                $sentiment = 'Positive';
                                                $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                                                $topic = $key;
                                            }
                                        }

                                        foreach ($negative_topics as $key => $negative) {
                                            if (stripos($message, $negative) !== false) {
                                                $sentiment = 'Negative';
                                                $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                                                $topic = $key;
                                            }
                                        }

                                        foreach ($keywords as $keyword) {
                                            if (stripos($message, $keyword) !== false) { 

                                                $timestamp = strtotime($_comment['created_time']);
                                                $node = $node_default;
                                                $node['project_id'] = $project_id;
                                                $node['platform'] = 'facebook';
                                                $node['content'] = $_comment['message'];
                                                $node['time'] = date('Y-m-d H:i:s', $timestamp);
                                                $node['author_name'] = $_comment['from']['name'];
                                                $node['author_id'] = $_comment['from']['id'];
                                                $node['post_id'] = $post['id'];
                                                $node['sentiment'] = $sentiment;
                                                $node['comment_id'] = $_comment['id'];
                                                $node['link'] = 'https://www.facebook.com/'.$comment['id'];
                                                $node['status'] = 'new';
                                                $node['title'] = '';
                                                $node['keyword'] = $keyword;
                                                $node['scales'] = $scales;
                                                $node['topic'] = $topic;
                                                $data_insert[] = $node;

                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

           if (count($data_insert) > 0) {

                foreach ($data_insert as $key => $mention) {
                    $this->db->insert(db_prefix() . 'rep_mentions', $mention);
                    $insert_id = $this->db->insert_id();    
                    
                    if ($insert_id) {
                        $mention['id'] = $insert_id;
                        foreach ($cases as $key => $case) {
                            $this->check_case($mention, $case);
                        }
                    }
                }
                return true;
            }
 
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        return true;
    }

    /**
     * [get_tiktok_data]
     * @param  [integer] $id tiktok account id
     * @return [boolean]   
     */
    public function get_tiktok_data($id){
        $config = rep_get_tiktok_config();
        $account = $this->get_social_accounts($id);

        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $this->tiktok_refresh_token($id);
            $account = $this->get_social_accounts($id);
        }

        $node_default = [
            'addedfrom' => get_staff_user_id(),
            'dateadded' => date('Y-m-d H:i:s'),
            'account_id' => $account->id,
        ];

        $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer '. $account->access_token);

        $user_url = $config['api_domain'].'/user/info/?fields=open_id,union_id,avatar_url_100,display_name,follower_count,following_count,likes_count,video_count';

        $user_result = $this->callAPI($user_url,  [], $header, 'GET');
        $data_insert = [];
        if(isset($user_result['data']['user'])){
            $node = $node_default;
            $node['type'] = 'follower';
            $node['time'] = date('Y-m-d H:i:s');
            $node['value'] = $user_result['data']['user']['follower_count'];
            $data_insert[] = $node;

            $node = $node_default;
            $node['type'] = 'following';
            $node['time'] = date('Y-m-d H:i:s');
            $node['value'] = $user_result['data']['user']['following_count'];
            $data_insert[] = $node;

            $node = $node_default;
            $node['type'] = 'like';
            $node['time'] = date('Y-m-d H:i:s');
            $node['value'] = $user_result['data']['user']['likes_count'];
            $data_insert[] = $node;

            $node = $node_default;
            $node['type'] = 'video';
            $node['time'] = date('Y-m-d H:i:s');
            $node['value'] = $user_result['data']['user']['video_count'];
            $data_insert[] = $node;
        }

         if (count($data_insert) > 0) {
            $this->db->where('id', $account->id);
            $this->db->update(db_prefix() . 'rep_accounts', ['last_sync_time' => date('Y-m-d H:i:s')]);
            
            $this->db->where('date_format(time, \'%Y-%m-%d\') = "'.date('Y-m-d').'"');
            $this->db->where('account_id', $account->id);
            $this->db->delete(db_prefix() . 'rep_analytics');


            $affectedRows = $this->db->insert_batch(db_prefix() . 'rep_analytics', $data_insert);

            if ($affectedRows > 0) {
                return true;
            }
        }

        return true;
    }

    /**
     * [get_twitter_data]
     * @param  [type] $id twitter account id
     * @return [boolean]  
     */
    public function get_twitter_data($project_id, $id){
        $cases = $this->get_case('', ['active' => 1]);
        $project = $this->get_project($project_id);
        $keywords = json_decode($project->keywords);
        $excluded_keywords = explode(',', $project->excluded_keywords);

        $mentions = $this->get_mention('', ['platform' => 'youtube']);
        $mention_arr = [];
        foreach ($mentions as $key => $value) {
            $mention_arr[$value['post_id']] = 1;
        }

        $topics = $this->get_topic('', ['active' => 1]);
        $positive_topics = [];
        $negative_topics = [];
        $scales_topics = [];
        foreach ($topics as $key => $value) {
                        $scales_topics[$value['id']] = $value['scales'];

            if($value['type'] == 'negative'){
                $negative_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }else{
                $positive_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }
        }

        $config = rep_get_twitter_config();
        $account = $this->get_social_accounts($id);

        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $this->twitter_refresh_token($id);
            $account = $this->get_social_accounts($id);
        }

        $data_insert = [];

        $node_default = [
            'datecreated' => date('Y-m-d H:i:s'),
            'account_id' => $account->id,
        ];

        $header = array(
                'Authorization: Bearer '. $account->access_token);

        foreach ($keywords as $keyword) {
            $queryParams = [
                'query' => urlencode($keyword),
                'max_results' => 10,
                'tweet.fields' => 'id,text,author_id,created_at,public_metrics',
                'expansions' => 'author_id',
                'user.fields' => 'username',
            ];

            $nextToken = null;
            do {
                if ($nextToken) {
                    $queryParams['next_token'] = $nextToken;
                }

                $user_url = $config['api_domain'].'/2/tweets/search/recent?'. http_build_query($queryParams);
                $user_result = $this->callAPI($user_url,  [], $header, 'GET');

                if(isset($user_result['data'])){
                    $users = $user_result['includes']['users'] ?? [];

                    $author_name = [];
                    foreach ($users as $user) {
                        $author_name[$user['id']] = ['name' => $user['name'], 'username' => $user['username']];
                    }

                    foreach ($user_result['data'] as $item) {
                        $check_excluded = false;
                        if (isset($mention_arr[$item['id']])) continue;

                        $title = '';
                        $content = mb_strtoupper($item['text']);
                        $sentiment = 'Neutral';
                        $scales = 0;
                        $topic = 0;

                        foreach ($excluded_keywords as $key => $excluded) {
                            if (stripos($content, mb_strtoupper(trim($excluded))) !== false) {
                                $check_excluded = true;
                            }
                        }

                        if (!$check_excluded) {
                            foreach ($positive_topics as $key => $positive) {
                                if (stripos($content, $positive) !== false) {
                                    $sentiment = 'Positive';
                                    $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                                    $topic = $key;
                                }
                            }

                            foreach ($negative_topics as $key => $negative) {
                                if (stripos($content, $negative) !== false) {
                                    $sentiment = 'Negative';
                                    $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                                    $topic = $key;
                                }
                            }

                            $url = 'https://x.com/'.$author_name[$item['author_id']]['username'].'/status/' . $item['id'];
                            $time = date('Y-m-d H:i:s', strtotime($item['created_at']));

                            $node = $node_default;
                            $node['project_id'] = $project_id;
                            $node['platform'] = 'x_twitter';
                            $node['title'] = $title;
                            $node['content'] = $item['text'];
                            $node['time'] = $time;
                            $node['author_name'] = $author_name[$item['author_id']]['name'];
                            $node['author_id'] = $item['author_id'];
                            $node['post_id'] = $item['id'];
                            $node['sentiment'] = $sentiment;
                            $node['link'] = $url;
                            $node['status'] = 'new';
                            $node['shares'] = $item['public_metrics']['retweet_count'];
                            $node['comments'] = $item['public_metrics']['reply_count'];
                            $node['likes'] = $item['public_metrics']['like_count'];
                            $node['pageviews'] = $item['public_metrics']['impression_count'];
                            $node['keyword'] = $keyword;
                            $node['topic'] = $topic;

                            $data_insert[] = $node;
                        }
                    }
                }

                $nextToken = $user_result['meta']['next_token'] ?? null;
            } while ($nextToken);
        }

        if (count($data_insert) > 0) {

            foreach ($data_insert as $key => $mention) {
                $this->db->insert(db_prefix() . 'rep_mentions', $mention);
                $insert_id = $this->db->insert_id();    
                
                if ($insert_id) {
                    $mention['id'] = $insert_id;
                    foreach ($cases as $key => $case) {
                        $this->check_case($mention, $case);
                    }
                }
            }
        }

        return false;
    }

    /**
     * [get_youtube_data]
     * @param  [string] $id youtube account id
     * @return [boolean] 
     */
    public function get_youtube_data($project_id, $id){
        $cases = $this->get_case('', ['active' => 1]);
        $project = $this->get_project($project_id);
        $keywords = json_decode($project->keywords);
        $excluded_keywords = explode(',', $project->excluded_keywords);

        $mentions = $this->get_mention('', ['platform' => 'youtube']);
        $mention_arr = [];
        foreach ($mentions as $key => $value) {
            $mention_arr[$value['post_id']] = 1;
        }

        $topics = $this->get_topic('', ['active' => 1]);
        $positive_topics = [];
        $negative_topics = [];
        $scales_topics = [];
        foreach ($topics as $key => $value) {
            $scales_topics[$value['id']] = $value['scales'];

            if($value['type'] == 'negative'){
                $negative_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }else{
                $positive_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }
        }

        $account = $this->get_social_accounts($id);

        if($account->access_token == ''){
            return false;
        }
        $config = rep_get_youtube_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setRedirectUri(admin_url('social_analytic/youtube_callback'));
        $client->setAccessToken($account->access_token);

        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in && $account->refresh_token != ''){
            $token_result = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            if(isset($token_result['access_token'])){
                $data_update = [
                    'access_token' => $token_result['access_token'],
                    'expires_in' => time() + $token_result['expires_in'],
                ];

                $this->account_connect_save($data_update, $account->id);
            }
        }
        
        $data_insert = [];
        $node_default = [
                'datecreated' => date('Y-m-d H:i:s'),
                'account_id' => $account->id,
            ];
        foreach ($keywords as $keyword) {

        try{
            $youtube = new Google_Service_YouTube($client);
            $searchResponse = $youtube->search->listSearch('snippet', [
                'q' => urlencode($keyword),
                'type' => 'video',
                'order' => 'date',
            ]);

            foreach ($searchResponse['items'] as $item) {
                $check_excluded = false;

                if (isset($mention_arr[$item['id']['videoId']])) continue;

                $title = mb_strtoupper($item['snippet']['title']);
                $content = mb_strtoupper($item['snippet']['description']);

                foreach ($excluded_keywords as $key => $excluded) {
                    if ((stripos($title, mb_strtoupper(trim($excluded))) !== false) || (stripos($content, mb_strtoupper(trim($excluded))) !== false)) {
                        $check_excluded = true;
                    }
                }

                if (!$check_excluded) {

                    $response = $youtube->videos->listVideos('snippet,statistics', ['id' => $item['id']['videoId']]);
                    $video_statistics = $response->getItems();


                    $sentiment = 'Neutral';
                    $scales = 0;
                    $topic = 0;

                    foreach ($positive_topics as $key => $positive) {
                        if (stripos($title, $positive) !== false || stripos($content, $positive) !== false) {
                            $sentiment = 'Positive';
                            $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                            $topic = $key;
                        }
                    }

                    foreach ($negative_topics as $key => $negative) {
                        if (stripos($title, $negative) !== false || stripos($content, $negative) !== false) {
                            $sentiment = 'Negative';
                            $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                            $topic = $key;
                        }
                    }

                    $url = 'https://www.youtube.com/watch?v=' . $item['id']['videoId'];
                    $time = date('Y-m-d H:i:s', strtotime($item['snippet']['publishedAt']));

                    $node = $node_default;
                    $node['project_id'] = $project_id;
                    $node['platform'] = 'youtube';
                    $node['title'] = $item['snippet']['title'];
                    $node['content'] = $item['snippet']['description'];
                    $node['time'] = $time;
                    $node['author_name'] = $item['snippet']['channelTitle'];
                    $node['author_id'] = $item['snippet']['channelId'];
                    $node['post_id'] = $item['id']['videoId'];
                    $node['sentiment'] = $sentiment;
                    $node['link'] = $url;
                    $node['status'] = 'new';
                    $node['likes'] = $video_statistics[0]['statistics']['likeCount'];
                    $node['comments'] = $video_statistics[0]['statistics']['commentCount'];
                    $node['pageviews'] = $video_statistics[0]['statistics']['viewCount'];
                    $node['keyword'] = $keyword;
                    $node['scales'] = $scales;
                    $node['topic'] = $topic;

                    $data_insert[] = $node;

                }
            }

        } catch(Exception $e) {
          
        } 
        } 

        if (count($data_insert) > 0) {
            foreach ($data_insert as $key => $mention) {
                $this->db->insert(db_prefix() . 'rep_mentions', $mention);
                $insert_id = $this->db->insert_id();    
                
                if ($insert_id) {
                    $mention['id'] = $insert_id;
                    foreach ($cases as $key => $case) {
                        $this->check_case($mention, $case);
                    }
                }
            }
        }

        return false;
    }

    /**
     * Gets the where report period.
     *
     * @param      string  $field  The field
     *
     * @return     string  The where report period.
     */
    public function get_where_report_period($field = 'date_format(time, \'%Y-%m-%d\')')
    {
        $months_report      = $this->input->post('date_filter');
        
        $custom_date_select = '';
        if ($months_report != '') {
            if (is_numeric($months_report)) {
                // Last month
                if ($months_report == '1') {
                    $beginMonth = date('Y-m-01', strtotime('first day of last month'));
                    $endMonth   = date('Y-m-t', strtotime('last day of last month'));
                } else {
                    $months_report = (int) $months_report;
                    $months_report--;
                    $beginMonth = date('Y-m-01', strtotime("-$months_report MONTH"));
                    $endMonth   = date('Y-m-t');
                }

                $custom_date_select = '(' . $field . ' BETWEEN "' . $beginMonth . '" AND "' . $endMonth . '")';
            } elseif ($months_report == 'last_30_days') {
                $custom_date_select = '(' . $field . ' BETWEEN "' . date('Y-m-d', strtotime('today - 30 days')) . '" AND "' . date('Y-m-d') . '")';
            } elseif ($months_report == 'this_month') {
                $custom_date_select = '(' . $field . ' BETWEEN "' . date('Y-m-01') . '" AND "' . date('Y-m-t') . '")';
            } elseif ($months_report == 'last_month') {
                $this_month = date('m') - 1;
                $custom_date_select = '(' . $field . ' BETWEEN "' . date("Y-m-d", strtotime("first day of previous month")) . '" AND "' . date("Y-m-d", strtotime("last day of previous month")) . '")';
            }elseif ($months_report == 'this_quarter') {
                $current_month = date('m');
                  $current_year = date('Y');
                  if($current_month>=1 && $current_month<=3)
                  {
                    $start_date = date('Y-m-d', strtotime('1-January-'.$current_year));  // timestamp or 1-Januray 12:00:00 AM
                    $end_date = date('Y-m-d', strtotime('1-April-'.$current_year));  // timestamp or 1-April 12:00:00 AM means end of 31 March
                  }
                  else  if($current_month>=4 && $current_month<=6)
                  {
                    $start_date = date('Y-m-d', strtotime('1-April-'.$current_year));  // timestamp or 1-April 12:00:00 AM
                    $end_date = date('Y-m-d', strtotime('1-July-'.$current_year));  // timestamp or 1-July 12:00:00 AM means end of 30 June
                  }
                  else  if($current_month>=7 && $current_month<=9)
                  {
                    $start_date = date('Y-m-d', strtotime('1-July-'.$current_year));  // timestamp or 1-July 12:00:00 AM
                    $end_date = date('Y-m-d', strtotime('1-October-'.$current_year));  // timestamp or 1-October 12:00:00 AM means end of 30 September
                  }
                  else  if($current_month>=10 && $current_month<=12)
                  {
                    $start_date = date('Y-m-d', strtotime('1-October-'.$current_year));  // timestamp or 1-October 12:00:00 AM
                    $end_date = date('Y-m-d', strtotime('1-January-'.($current_year+1)));  // timestamp or 1-January Next year 12:00:00 AM means end of 31 December this year
                  }
                $custom_date_select = '(' . $field . ' BETWEEN "' .
                $start_date .
                '" AND "' .
                $end_date . '")';

            }elseif ($months_report == 'last_quarter') {
                $current_month = date('m');
                    $current_year = date('Y');

                  if($current_month>=1 && $current_month<=3)
                  {
                    $start_date = date('Y-m-d', strtotime('1-October-'.($current_year-1)));  // timestamp or 1-October Last Year 12:00:00 AM
                    $end_date = date('Y-m-d', strtotime('1-January-'.$current_year));  // // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
                  } 
                  else if($current_month>=4 && $current_month<=6)
                  {
                    $start_date = date('Y-m-d', strtotime('1-January-'.$current_year));  // timestamp or 1-Januray 12:00:00 AM
                    $end_date = date('Y-m-d', strtotime('1-April-'.$current_year));  // timestamp or 1-April 12:00:00 AM means end of 31 March
                  }
                  else  if($current_month>=7 && $current_month<=9)
                  {
                    $start_date = date('Y-m-d', strtotime('1-April-'.$current_year));  // timestamp or 1-April 12:00:00 AM
                    $end_date = date('Y-m-d', strtotime('1-July-'.$current_year));  // timestamp or 1-July 12:00:00 AM means end of 30 June
                  }
                  else  if($current_month>=10 && $current_month<=12)
                  {
                    $start_date = date('Y-m-d', strtotime('1-July-'.$current_year));  // timestamp or 1-July 12:00:00 AM
                    $end_date = date('Y-m-d', strtotime('1-October-'.$current_year));  // timestamp or 1-October 12:00:00 AM means end of 30 September
                  }
                $custom_date_select = '(' . $field . ' BETWEEN "' .
                $start_date .
                '" AND "' .
                $end_date . '")';

            }elseif ($months_report == 'this_year') {
                $custom_date_select = '(' . $field . ' BETWEEN "' .
                date('Y-m-d', strtotime(date('Y-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date('Y-12-31'))) . '")';
            } elseif ($months_report == 'last_year') {
                $custom_date_select = '(' . $field . ' BETWEEN "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-01-01'))) .
                '" AND "' .
                date('Y-m-d', strtotime(date(date('Y', strtotime('last year')) . '-12-31'))) . '")';
            } elseif ($months_report == 'custom') {
                $from_date = to_sql_date($this->input->post('report_from'));
                $to_date   = to_sql_date($this->input->post('report_to'));
                if ($from_date == $to_date) {
                    $custom_date_select = '' . $field . ' = "' . $from_date . '"';
                } else {
                    $custom_date_select = '(' . $field . ' BETWEEN "' . $from_date . '" AND "' . $to_date . '")';
                }
            } elseif(!(strpos($months_report, 'year') === false)){
                $year = explode('year_', $months_report);

                $custom_date_select = '(' . $field . ' BETWEEN "' . date($year[1].'-01-01') . '" AND "' . date(($year[1]+1).'-01-01') . '")';
            }else if($months_report == 'all_time'){
                $custom_date_select = '';
            }
        }

        return $custom_date_select;
    }

    /**
     * get_google_news
     * @param  [integer] $project_id
     * @return [boolean]            
     */
    public function get_google_news($project_id)
    {
        $cases = $this->get_case('', ['active' => 1]);
        $project = $this->get_project($project_id);
        $keywords = json_decode($project->keywords);
        $mentions = $this->get_mention();
        $mention_arr = [];
        foreach ($mentions as $key => $value) {
            $mention_arr[$value['source_id']] = 1;
        }

        $excluded_keywords = explode(',', $project->excluded_keywords);

        $topics = $this->get_topic('', ['active' => 1]);
        $positive_topics = [];
        $negative_topics = [];
        $scales_topics = [];
        foreach ($topics as $key => $value) {
                        $scales_topics[$value['id']] = $value['scales'];

            if($value['type'] == 'negative'){
                $negative_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }else{
                $positive_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }
        }

        $country = get_google_new_country('VN:vi');

        $api_param = '&hl='.$country['hl'].'&gl='.$country['gl'].'&ceid='.$country['ceid'];

        $data_insert = [];
        foreach ($keywords as $keyword) {
            $rss_url = 'https://news.google.com/rss/search?q=' . urlencode($keyword) . $api_param;
            $rss = simplexml_load_file($rss_url);

            foreach ($rss->channel->item as $item) {
                $check_excluded = false;

                $source_id = (string) $item->guid;

                if (isset($mention_arr[$source_id])) continue;

                $link = (string) $item->link;

                if ($this->is_blocked_url($link)) continue;



                $desc = mb_strtoupper(strip_tags((string) $item->description));
                foreach ($excluded_keywords as $key => $excluded) {
                    if (stripos($desc, mb_strtoupper(trim($excluded))) !== false) {
                        $check_excluded = true;
                    }
                }
                if (!$check_excluded) {
                    $site = $this->extract_source_from_description((string) $item->description);
                    $time = date('Y-m-d H:i:s', strtotime((string) $item->pubDate));

                    $sentiment = 'Neutral';
                    $scales = 0;
                    $topic = 0;
                    foreach ($positive_topics as $key => $positive) {
                        if (stripos($desc, $positive) !== false) {
                            $sentiment = 'Positive';
                            $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                            $topic = $key;
                        }
                    }

                    foreach ($negative_topics as $key => $negative) {
                        if (stripos($desc, $negative) !== false) {
                            $sentiment = 'Negative';
                            $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                            $topic = $key;
                        }
                    }

                    $data_insert[] = [
                        'project_id'     => $project_id,
                        'title'     => (string) $item->title,
                        'link'       => $link,
                        'content'   => strip_tags((string) $item->description),
                        'platform'   => 'google_news',
                        'source_id'     => $source_id,
                        'status'   => 'new',
                        'time'      => $time,
                        'sentiment' => $sentiment,
                        'site'      => $site,
                        'keyword'      => $keyword,
                        'scales'      => $scales,
                        'topic'      => $topic,
                    ];
                }

            }
        }

        if (count($data_insert) > 0) {
            foreach ($data_insert as $key => $mention) {
                $this->db->insert(db_prefix() . 'rep_mentions', $mention);
                $insert_id = $this->db->insert_id();    
                
                if ($insert_id) {
                    $mention['id'] = $insert_id;
                    foreach ($cases as $key => $case) {
                        $this->check_case($mention, $case);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * [get_filtered_posts]
     * @return [array] 
     */
    public function get_filtered_posts()
    {
        $all_posts = [];

        foreach ($this->keywords as $keyword) {
            $posts = $this->get_google_news($keyword);
            $all_posts = array_merge($all_posts, $posts);
        }

        return $all_posts;
    }

    private $blocked_domains = [
    ];

    /**
     * check is blocked url
     * @param  string  $url
     * @return boolean     
     */
    private function is_blocked_url($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        foreach ($this->blocked_domains as $blocked) {
            if (strpos($host, $blocked) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * extract_source_from_description
     * @param  string $desc 
     * @return string       
     */
    private function extract_source_from_description($desc)
    {
        preg_match('/<b>(.*?)<\/b>/', $desc, $matches);
        return isset($matches[1]) ? $matches[1] : 'Google News';
    }

    /**
     * add_to_pdf_report
     * @param integer $mention_id 
     * @return boolean or integer     
     */
    public function add_to_pdf_report($mention_id)
    {
        $data = [];
        $data['project_id'] = rep_get_base_workspace_id();
        $data['mention_id'] = $mention_id;
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();

        $this->db->insert(db_prefix() . 'rep_pdf_reports', $data);
        $insert_id = $this->db->insert_id();    
        
        if ($insert_id) {
            $this->db->where('id', $mention_id);
            $this->db->update(db_prefix() . 'rep_mentions', ['add_to_pdf' => 1]);

            return $insert_id;
        }

        return false;
    }

    /**
     * remove_from_pdf_report
     * @param  integer $mention_id 
     * @return boolean             
     */
    public function remove_from_pdf_report($mention_id)
    {
        $this->db->where('mention_id', $mention_id);
        $this->db->delete(db_prefix() . 'rep_pdf_reports');
        
        $this->db->where('id', $mention_id);
        $this->db->update(db_prefix() . 'rep_mentions', ['add_to_pdf' => 0]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * mention sentiment mark as
     * @param  string $sentiment 
     * @param  integer $id     
     * @return boolean        
     */
    public function mention_sentiment_mark_as($sentiment, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_mentions', ['sentiment' => $sentiment]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * visit_mention
     * @param  integer $id 
     * @return boolean     
     */
    public function visit_mention($id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_mentions', ['visit' => 1]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * get mention
     * @param  integer $id    member type id
     * @param  array  $where
     * @return object
     */
    public function get_mention_list($data_filter = [])
    {
        $project_id = rep_get_base_workspace_id();
        $where = $this->get_where_report($data_filter);
        if($where != ''){
            $this->db->where($where);
        }
        $this->db->order_by('time', 'desc');

        if(isset($data_filter['page']) && $data_filter['page'] != ''){
            $start = ($data_filter['page'] - 1) * 10;
            $this->db->limit(10, $start);
        }else{
            $this->db->limit(10, 0);
        }

        $mentions = $this->db->get(db_prefix() . 'rep_mentions')->result_array();

        if($where != ''){
            $this->db->where($where);
        }
        $total = $this->db->count_all_results(db_prefix() . 'rep_mentions');

        return ['total' => $total, 'mentions' => $mentions];
    }

    /**
     * get_data_mentions_reach_chart
     * @param  array $data_filter
     * @return array
     */
    public function get_data_mentions_reach_chart($data_filter){
        $project_id = rep_get_base_workspace_id();
        $where = $this->get_where_report($data_filter);
        if($where != ''){
            $this->db->where($where);
        }

        $this->db->order_by('time', 'asc');
        $analytics = $this->db->get(db_prefix().'rep_mentions')->result_array();


        $data_return = [];
        $categories = [];
        $data_date = [];
        $list_invoice = '0';
        foreach ($analytics as $key => $value) {
            $date = date('Y-m-d', strtotime($value['time']));

            if(!isset($data_date[$date])){
                $categories[] = date('d', strtotime($value['time'])).'.'. _l(date('M', strtotime($value['time'])));
                $data_date[$date] = [];
                $data_date[$date]['mentions'] = 0;
                $data_date[$date]['reach'] = 0;
            }

            $data_date[$date]['mentions'] ++;
            if($value['pageviews'] > 0){
                $data_date[$date]['reach'] += $value['pageviews'];
            }
        }

        $mentions = [];
        $reach = [];

        foreach($data_date as $key => $value) {
            $mentions[] = $value['mentions'];
            $reach[] = $value['reach'];
        }

        $data_return = [
            'data' => [
                ['name' => _l('mentions'), 'data' => $mentions],
                ['name' => _l('reach'), 'data' => $reach],
            ],
            'categories' => $categories
        ];
        return $data_return;
    }

    /**
     * get_data_sentiment_chart
     * @param  array $data_filter
     * @return array
     */
    public function get_data_sentiment_chart($data_filter){
        $project_id = rep_get_base_workspace_id();

        $where = $this->get_where_report($data_filter);
        if($where != ''){
            $this->db->where($where);
        }

        $this->db->order_by('time', 'asc');
        $analytics = $this->db->get(db_prefix().'rep_mentions')->result_array();


        $data_return = [];
        $categories = [];
        $data_date = [];
        $list_invoice = '0';
        foreach ($analytics as $key => $value) {
            $date = date('Y-m-d', strtotime($value['time']));

            if(!isset($data_date[$date])){
                $categories[] = date('d', strtotime($value['time'])).'.'. _l(date('M', strtotime($value['time'])));
                $data_date[$date] = [];
                $data_date[$date]['positive'] = 0;
                $data_date[$date]['negative'] = 0;
            }

            if($value['sentiment'] == 'Positive'){
                $data_date[$date]['positive'] ++;
            }

            if($value['sentiment'] == 'Negative'){
                $data_date[$date]['negative'] ++;
            }
        }

        $positive = [];
        $negative = [];

        foreach($data_date as $key => $value) {
            $positive[] = $value['positive'];
            $negative[] = $value['negative'];
        }

        $data_return = [
            'data' => [
                ['name' => _l('positive'), 'data' => $positive],
                ['name' => _l('negative'), 'data' => $negative],
            ],
            'categories' => $categories
        ];
        return $data_return;
    }

    /**
     * twitter refresh token
     * @param  {String} $account_id twitter account id
     * @return {Boolean}           
     */
    public function twitter_refresh_token($account_id){
        $account = $this->get_social_accounts($account_id);
        $config = rep_get_twitter_config();
       
        $parameters = array(
              'grant_type' => 'refresh_token',
              'client_id' => $config['client_id'],
              'refresh_token' => $account->refresh_token,
            );

        $http_header = array(
             'Accept' => 'application/json',
             'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $url = $config['api_domain'].'/2/oauth2/token';

        if(isset($token_result['access_token'])){
            $data_update = [
                'access_token' => $token_result['access_token'],
                'expires_in' => time() + $token_result['expires_in'],
                'refresh_token' => $token_result['refresh_token'],
            ];

            $this->account_connect_save($data_update, $account->id);

            return true;
        }
        
        return false;
    }

    /**
     * add new case
     * @param array $data
     * @return integer
     */
    public function add_case($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'rep_cases', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    /**
     * update case
     * @param array $data
     * @param integer $id
     * @return integer
     */
    public function update_case($data, $id)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }

        if (isset($data['trigger'])) {
            $workflow = [];
            $workflow['trigger'] = $data['trigger'];
            $workflow['topic'] = $data['topic'];
            $workflow['sentiment'] = $data['sentiment'];
            $workflow['sources'] = $data['sources'];
            $workflow['word'] = $data['word'];
            $workflow['action'] = $data['action'];
            $workflow['staff'] = $data['staff'];
            $workflow['tag'] = $data['tag'];

            $data['workflow'] = json_encode($workflow);

            unset($data['trigger']);
            unset($data['topic']);
            unset($data['sentiment']);
            unset($data['sources']);
            unset($data['word']);
            unset($data['action']);
            unset($data['staff']);
            unset($data['tag']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_cases', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * get case
     * @param  integer $id    member type id
     * @param  array  $where
     * @return object
     */
    public function get_case($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'rep_cases')->row();
        }

        $this->db->where($where);
        $this->db->order_by('id', 'desc');
        $cases = $this->db->get(db_prefix() . 'rep_cases')->result_array();

        return $cases;
    }
    /**
     * delete case
     * @param integer $id
     * @return boolean
     */

    public function delete_case($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'rep_cases');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change case active
     * @param  [integer] $id     
     * @param  [string] $status 
     */
    public function change_case_active($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'rep_cases', [
            'active' => $status,
        ]);
    }

    /**
     * [hide_facebook_comment]
     * @param  [integer] $id facebook account id
     * @return [boolean]    
     */
    public function hide_facebook_comment($id, $mention_id){
        $mention = $this->reputation_model->get_mention($mention_id);

        $config = rep_get_facebook_config();
        $fb = new \Facebook\Facebook($config);
        $account = $this->get_social_accounts($id);

        try {

            $data_insert = [];
            $response = $fb->post(
                "/$mention->comment_id",
                ['is_hidden' => true],
                $account->access_token
              );
              $graphNode = $response->getGraphNode();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        return true;
    }

    /**
     * get_data_mentions_chart
     * @param  array $data_filter
     * @return array
     */
    public function get_data_mentions_chart($data_filter){
        $where = $this->get_where_report($data_filter);
        if($where != ''){
            $this->db->where($where);
        }

        $this->db->order_by('time', 'asc');
        $analytics = $this->db->get(db_prefix().'rep_mentions')->result_array();


        $data_return = [];
        $categories = [];
        $data_date = [];
        $list_invoice = '0';
        foreach ($analytics as $key => $value) {
            $date = date('Y-m-d', strtotime($value['time']));

            if(!isset($data_date[$date])){
                $categories[] = date('d', strtotime($value['time'])).'.'. _l(date('M', strtotime($value['time'])));
                $data_date[$date] = [];
                $data_date[$date]['mentions'] = 0;
            }

            $data_date[$date]['mentions'] ++;
        }

        $mentions = [];
        $reach = [];

        foreach($data_date as $key => $value) {
            $mentions[] = $value['mentions'];
        }

        $data_return = [
            'data' => [
                ['name' => _l('mentions'), 'data' => $mentions],
            ],
            'categories' => $categories
        ];
        return $data_return;
    }

    /**
     * get_data_social_media_reach_chart
     * @param  array $data_filter
     * @return array
     */
    public function get_data_social_media_reach_chart($data_filter){
        $where = $this->get_where_report($data_filter);
        if($where != ''){
            $this->db->where($where);
        }

        $this->db->where('platform != "google_news"');
        $this->db->order_by('time', 'asc');
        $analytics = $this->db->get(db_prefix().'rep_mentions')->result_array();


        $data_return = [];
        $categories = [];
        $data_date = [];
        $list_invoice = '0';
        foreach ($analytics as $key => $value) {
            $date = date('Y-m-d', strtotime($value['time']));

            if(!isset($data_date[$date])){
                $categories[] = date('d', strtotime($value['time'])).'.'. _l(date('M', strtotime($value['time'])));
                $data_date[$date] = [];
                $data_date[$date]['reach'] = 0;
            }

            if($value['pageviews'] > 0){
                $data_date[$date]['reach'] += $value['pageviews'];
            }
        }

        $mentions = [];
        $reach = [];

        foreach($data_date as $key => $value) {
            $reach[] = $value['reach'];
        }

        $data_return = [
            'data' => [
                ['name' => _l('reach'), 'data' => $reach],
            ],
            'categories' => $categories
        ];
        return $data_return;
    }

    /**
     * Gets the pur order search.
     *
     * @param        $q      The quarter
     */
    public function get_vendor_search($q){
        $this->db->where('1=1 AND (company LIKE "%'.$this->db->escape_like_str($q).'%")');
        return $this->db->get(db_prefix().'pur_vendor')->result_array();
    }


    /**
     * get_the_most_popular_mentions
     * @param  array $data_filter
     * @return array
     */
    public function get_the_most_popular_mentions($data_filter){
        $project_id = rep_get_base_workspace_id();
        $where = $this->get_where_report($data_filter);
        if($where != ''){
            $this->db->where($where);
        }

        $this->db->order_by('pageviews', 'desc');
        $this->db->limit(5, 0);
        $mentions = $this->db->get(db_prefix().'rep_mentions')->result_array();

        return $mentions;
    }
    
    /**
     * [get_where_report]
     * @param  [array] $data_filter
     * @return [string]              
     */
    public function get_where_report($data_filter){
        $project_id = rep_get_base_workspace_id();
        $where = 'status != "deleted" AND project_id = "'.$project_id.'"';
        if(isset($data_filter['search']) && $data_filter['search'] != ''){
            if(preg_match('/^#\d+$/', $data_filter['search'])){
                $number = substr($data_filter['search'], 1);
                $where .= ' AND (id = '.$number.')';
            }else{
                $where .= ' AND (';
                $where .= 'title LIKE "%'.$data_filter['search'].'%"';
                $where .= ' OR content LIKE "%'.$data_filter['search'].'%"';
                $where .= ' OR author_name LIKE "%'.$data_filter['search'].'%"';
                $where .= ' OR site LIKE"%'.$data_filter['search'].'%"';
                $where .= ')';
            }
        }

        if(isset($data_filter['tags']) && $data_filter['tags'] != ''){
            $where .= ' AND (';
            $where .= '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'rep_mentions.id and rel_type="rep_mention" ORDER by tag_order ASC) LIKE "%'.$data_filter['tags'].'%"';
            $where .= ')';
        }

        if(isset($data_filter['sources']) && $data_filter['sources'] != ''){
            $sources_where = '';
            foreach ($data_filter['sources'] as $key => $value) {
                if($sources_where == ''){
                    $sources_where .= 'platform = "'.$value.'"';
                }else{
                    $sources_where .= ' OR platform = "'.$value.'"';
                }
            }

            $where .= ' AND ('.$sources_where.')';
        }

        if(isset($data_filter['sentiment']) && $data_filter['sentiment'] != ''){
            $sentiment_where = '';
            foreach ($data_filter['sentiment'] as $key => $value) {
                if($sentiment_where == ''){
                    $sentiment_where .= 'sentiment = "'.$value.'"';
                }else{
                    $sentiment_where .= ' OR sentiment = "'.$value.'"';
                }
            }

            $where .= ' AND ('.$sentiment_where.')';
        }
        
        if(isset($data_filter['visited']) && $data_filter['visited'] != 'all'){
            $where .= ' AND (visit = "'.$data_filter['visited'].'")';
        }

        if(isset($data_filter['pdf_report']) && $data_filter['pdf_report'] == 1){
            $where .= ' AND (add_to_pdf = "1")';
        }

        $from_date = '';
        $to_date   = '';
        if ($data_filter['from_date']) {
            $from_date = to_sql_date($data_filter['from_date']);
        }

        if ($data_filter['to_date']) {
            $to_date = to_sql_date($data_filter['to_date']);
        }

        if ($from_date != '' && $to_date != '') {
            $where .= ' AND (DATE_FORMAT(time, "%Y-%m-%d") >= "' . $from_date . '" and DATE_FORMAT(time, "%Y-%m-%d") <= "' . $to_date . '")';
        } elseif ($from_date != '') {
            $where .= ' AND (DATE_FORMAT(time, "%Y-%m-%d") >= "' . $from_date . '")';
        } elseif ($to_date != '') {
            $where .= ' AND (DATE_FORMAT(time, "%Y-%m-%d") <= "' . $to_date . '")';
        }

        return $where;
    }

    /**
     * [get_data_mentions_by_category_chart]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_data_mentions_by_category($data_filter){
        $where = $this->get_where_report($data_filter);
        if($where != ''){
            $this->db->where($where);
        }

        $analytics = $this->db->get(db_prefix().'rep_mentions')->result_array();
        $data_return = [];
        
        $data_summary = [];
        $data_summary['data_google_news'] = 0;
        $data_summary['data_facebook'] = 0;
        $data_summary['data_instagram'] = 0;
        $data_summary['data_youtube'] = 0;
        $data_summary['data_x_twitter'] = 0;
           
        foreach ($analytics as $key => $value) {
            if($value['platform'] == 'google_news'){
                $data_summary['data_google_news'] += 1;
            }elseif($value['platform'] == 'facebook'){
                $data_summary['data_facebook'] += 1;
            }elseif($value['platform'] == 'instagram'){
                $data_summary['data_instagram'] += 1;
            }elseif($value['platform'] == 'youtube'){
                $data_summary['data_youtube'] += 1;
            }elseif($value['platform'] == 'x_twitter'){
                $data_summary['data_x_twitter'] += 1;
            }
        }

        $post_total = $data_summary['data_google_news'] + $data_summary['data_facebook'] + $data_summary['data_instagram'] + $data_summary['data_youtube'] + $data_summary['data_x_twitter'];

        if($post_total > 0){
            $google_news_percentage = round(($data_summary['data_google_news']/$post_total*100), 2);
            $facebook_percentage = round(($data_summary['data_facebook']/$post_total*100), 2);
            $instagram_percentage = round(($data_summary['data_instagram']/$post_total*100), 2);
            $x_twitter_percentage = round(($data_summary['data_x_twitter']/$post_total*100), 2);
            $youtube_percentage = round(100 - $google_news_percentage - $facebook_percentage - $instagram_percentage - $x_twitter_percentage, 2);
        }else{
            $google_news_percentage = 0;
            $facebook_percentage = 0;
            $instagram_percentage = 0;
            $x_twitter_percentage = 0;
            $youtube_percentage = 0;
        }

        $data_summary['google_news_percentage'] = $google_news_percentage;
        $data_summary['facebook_percentage'] = $facebook_percentage;
        $data_summary['instagram_percentage'] = $instagram_percentage;
        $data_summary['x_twitter_percentage'] = $x_twitter_percentage;
        $data_summary['youtube_percentage'] = $youtube_percentage;

        $data_chart = [
            'data' => [
                ['name' => _l('google_news'), 'y' => $google_news_percentage],
                ['name' => _l('facebook'), 'y' => $facebook_percentage],
                ['name' => _l('instagram'), 'y' => $instagram_percentage],
                ['name' => _l('youtube'), 'y' => $youtube_percentage],
                ['name' => _l('x_twitter'), 'y' => $x_twitter_percentage],
            ],
        ];

        $data_return = [
            'data_summary' => $data_summary,
            'data_chart' => $data_chart,
        ];
        return $data_return;
    }

    /**
     * [get_instagram_data]
     * @param  [integer] $id instagram account id
     * @return [boolean]    
     */
    public function get_instagram_data($project_id, $id){
        $cases = $this->get_case('', ['active' => 1]);
        $project = $this->get_project($project_id);
        $keywords = json_decode($project->keywords, true);
        $excluded_keywords = explode(',', $project->excluded_keywords);

        $this->db->where('platform', 'instagram');
        $mentions = $this->db->get(db_prefix() . 'rep_mentions')->result_array();
        $mention_arr = array_column($mentions, 'comment_id');

        $keywords = array_map('mb_strtoupper', $keywords);


        $topics = $this->get_topic('', ['active' => 1]);
        $positive_topics = [];
        $negative_topics = [];
        $scales_topics = [];
        foreach ($topics as $key => $value) {
            $scales_topics[$value['id']] = $value['scales'];

            if($value['type'] == 'negative'){
                $negative_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }else{
                $positive_topics[$value['id']] = mb_strtoupper(trim($value['content']));
            }
        }

        $config = rep_get_instagram_config();
        $fb = new \Facebook\Facebook($config);
        $account = $this->get_social_accounts($id);

        $keywords = json_decode($project->keywords);

        foreach ($keywords as $keyword) {
            try {
                $node_default = [
                    'datecreated' => date('Y-m-d H:i:s'),
                    'account_id' => $account->id,
                ];

                $data_insert = [];
                $response = $fb->get('/ig_hashtag_search?user_id='.$account->page_id.'&q='.$keyword, $account->access_token);
                $hashtags = $response->getDecodedBody();
                if(isset($hashtags['data'])){
                    foreach ($hashtags['data'] as $hashtag) {

                        $nextPage = null;

                        $initialUrl = '/'.$hashtag['id'].'/recent_media?user_id='.$account->page_id.'&fields=id,caption,permalink,timestamp,comments_count,like_count';
                        do {
                            if ($nextPage) {
                                $initialUrl = $nextPage;
                            }

                            $response = $fb->get($initialUrl, $account->access_token);
                            $media = $response->getDecodedBody();
                            if(isset($media['data'])){
                                foreach ($media['data'] as $item) {
                                    $check_excluded = false;

                                    if(in_array($item['id'], $mention_arr)){
                                        continue;
                                    }

                                    $message = mb_strtoupper(trim($item['caption']));

                                    foreach ($excluded_keywords as $key => $excluded) {
                                        if (stripos($message, mb_strtoupper(trim($excluded))) !== false) {
                                            $check_excluded = false;
                                        }
                                    }

                                    if(!$check_excluded){
                                        $sentiment = 'Neutral';
                                        $scales = 0;
                                        $topic = 0;
                                        foreach ($positive_topics as $key => $positive) {
                                            if (stripos($message, $positive) !== false) {
                                                $sentiment = 'Positive';
                                                $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                                                $topic = $key;
                                            }
                                        }

                                        foreach ($negative_topics as $key => $negative) {
                                            if (stripos($message, $negative) !== false) {
                                                $sentiment = 'Negative';
                                                $scales = isset($scales_topics[$key]) ? $scales_topics[$key] : 0;
                                                $topic = $key;
                                            }
                                        }

                                        foreach ($keywords as $keyword) {
                                            if (stripos($message, $keyword) !== false) { 
                                                $timestamp = strtotime($item['timestamp']);
                                                $node = $node_default;
                                                $node['project_id'] = $project_id;
                                                $node['platform'] = 'instagram';
                                                $node['content'] = $item['caption'];
                                                $node['time'] = date('Y-m-d H:i:s', $timestamp);
                                                $node['sentiment'] = $sentiment;
                                                $node['link'] = $item['permalink'];
                                                $node['status'] = 'new';
                                                $node['title'] = '';
                                                $node['likes'] = $item['like_count'] ?? 0;
                                                $node['comments'] = $item['comments_count'] ?? 0;
                                                $node['keyword'] = $keyword;
                                                $node['scales'] = $scales;
                                                $node['topic'] = $topic;
                                                $data_insert[] = $node;
                                            }
                                        }
                                    }
                                }
                            }
                        $nextPage = $user_result['paging']['next'] ?? null;
                        } while ($nextPage);
                    }
                }

               if (count($data_insert) > 0) {
                    foreach ($data_insert as $key => $mention) {
                        $this->db->insert(db_prefix() . 'rep_mentions', $mention);
                        $insert_id = $this->db->insert_id();    
                        
                        if ($insert_id) {
                            $mention['id'] = $insert_id;
                            foreach ($cases as $key => $case) {
                                $this->check_case($mention, $case);
                            }
                        }
                    }
                }
     
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            }
        }

        return true;
    }

    /**
     * get_tag_stats_data
     * @param  [array] $data_filter 
     * @return [array]              
     */
    public function get_tag_stats_data($data_filter){
        $where = $this->get_where_report($data_filter);

        $data = $this->db->query('SELECT 
          '.db_prefix().'tags.name AS tag_name,
          COUNT(*) AS usage_count
        FROM '.db_prefix().'taggables
        JOIN '.db_prefix().'tags ON '.db_prefix().'taggables.tag_id = '.db_prefix().'tags.id
        JOIN '.db_prefix().'rep_mentions ON '.db_prefix().'taggables.rel_id = '.db_prefix().'rep_mentions.id
        WHERE '.db_prefix().'taggables.rel_type = \'rep_mention\'
         ' .($where != '' ? ' AND '. $where : ''). '
        GROUP BY '.db_prefix().'tags.id
        ORDER BY usage_count DESC 
        LIMIT 10;')->result_array();

        return $data;
    }

    /**
     * get_keyword_stats_data
     * @param  [array] $data_filter 
     * @return [array]              
     */
    public function get_keyword_stats_data($data_filter){
        $where = $this->get_where_report($data_filter);

        $data = $this->db->query('SELECT keyword, COUNT(*) AS usage_count
        FROM '.db_prefix().'rep_mentions
        WHERE keyword IS NOT NULL AND keyword != \'\'
         '.($where != '' ? ' AND '. $where : '').'
        GROUP BY keyword
        ORDER BY usage_count DESC
        LIMIT 10;')->result_array();

        return $data;
    }

    /**
     * [cron_reputation]
     */
    public function cron_reputation(){
        $this->db->where('active', 1);
        $projects = $this->db->get(db_prefix() . 'rep_projects')->result_array();

        foreach ($projects as $project) {
            if($value['type'] == 'active_sources_news'){
                $this->reputation_model->get_google_news($project['id']);
            }

            $accounts = $this->get_account('', ['active' => 1, 'project_id' => $project['id']]);
            foreach ($accounts as $key => $account) {
                switch ($account['type']) {
                    case 'facebook':
                        if($value['type'] == 'active_sources_facebook'){
                            $this->get_facebook_data($project['id'], $account['id']);
                        }

                        break;
                    case 'youtube':
                        if($value['type'] == 'active_sources_youtube'){
                            $this->get_youtube_data($project['id'], $account['id']);
                        }

                        break;

                    case 'twitter':
                        if($value['type'] == 'active_sources_x_twitter'){
                            $this->get_twitter_data($project['id'], $account['id']);
                        }
                        break;

                    case 'instagram':
                        if($value['type'] == 'active_sources_instagram'){
                            $this->get_instagram_data($project['id'], $account['id']);
                        }
                        break;
                    
                    default:
                        // code...
                        break;
                }
            }

            $this->rep_cron_send_notifications($project['id']);
        }
    }

    /**
     * rep_cron_send_notifications
     * @param  integer $project_id 
     */
    public function rep_cron_send_notifications($project_id){
        $this->load->model('emails_model');
        $notifications = $this->get_notification('', ['project_id' => $project_id]);

        foreach ($notifications as $key => $notification) {

            $last_cron_run  = $notification['last_send_time'];

            $timestamp = $this->get_time_from_frequency($notification, $last_cron_run);

            if ($last_cron_run == '' || time() >= $timestamp) {

                $where = 'status != "deleted" AND project_id = "'.$project_id.'"';

                $from_date = '';
                $to_date   = '';
                if ($last_cron_run != '') {
                    $from_date = date('Y-m-d', $last_cron_run);
                }

                $to_date = date('Y-m-d', $timestamp);

                if ($from_date != '' && $to_date != '') {
                    $where .= ' AND (DATE_FORMAT(time, "%Y-%m-%d") >= "' . $from_date . '" and DATE_FORMAT(time, "%Y-%m-%d") <= "' . $to_date . '")';
                } elseif ($from_date != '') {
                    $where .= ' AND (DATE_FORMAT(time, "%Y-%m-%d") >= "' . $from_date . '")';
                } elseif ($to_date != '') {
                    $where .= ' AND (DATE_FORMAT(time, "%Y-%m-%d") <= "' . $to_date . '")';
                }

                if(isset($notification['tags']) && $notification['tags'] != ''){
                    $where .= ' AND (';
                    $where .= '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'rep_mentions.id and rel_type="rep_mention" ORDER by tag_order ASC) LIKE "%'.$notification['tags'].'%"';
                    $where .= ')';
                }

                if(isset($notification['sources']) && $notification['sources'] != ''){
                    $sources_where = '';
                    $sources = explode(',' , $notification['sources']);
                    foreach ($sources as $key => $value) {
                        if($sources_where == ''){
                            $sources_where .= 'platform = "'.$value.'"';
                        }else{
                            $sources_where .= ' OR platform = "'.$value.'"';
                        }
                    }

                    $where .= ' AND ('.$sources_where.')';
                }

                if(isset($notification['sentiment']) && $notification['sentiment'] != ''){
                    $sentiment = explode(',' , $notification['sentiment']);
                    $sentiment_where = '';
                    foreach ($sentiment as $key => $value) {
                        if($sentiment_where == ''){
                            $sentiment_where .= 'sentiment = "'.$value.'"';
                        }else{
                            $sentiment_where .= ' OR sentiment = "'.$value.'"';
                        }
                    }

                    $where .= ' AND ('.$sentiment_where.')';
                }
                
                if(isset($notification['visited']) && $notification['visited'] != 'all'){
                    $where .= ' AND (visit = "'.$notification['visited'].'")';
                }

                $this->db->where($where);
                $count = $this->db->count_all_results(db_prefix().'rep_mentions');

                if($count > 0){
                    $email = $notification['email'];

                    $body = '
                                    <p>Hello,</p>

                                    <p>
                                      There are <strong>'.$count.' new mentions</strong> in the <strong>Reputation</strong> module.
                                    </p>

                                    <p>
                                      👉 <a href="'.admin_url('reputation/mentions').'" style="color: #1a73e8; text-decoration: none;">
                                        Click here to view them
                                      </a>
                                    </p>
                                ';
                    $this->emails_model->send_simple_email($email, _l('you_have_x_new_posts_in_the_reputation_module', $count), $body);

                    $this->db->where('id', $notification['id']);
                    $this->db->update(db_prefix().'rep_notifications', ['last_send_time' => $timestamp]);
                }
            }
        }
    }

    /**
     * get_time_from_frequency
     * @param  array $notification  
     * @param  integer $last_cron_run 
     * @return integer                
     */
    public function get_time_from_frequency($notification, $last_cron_run){
        $time = time();

        switch ($notification['frequency']) {
            case 'every_hour':
                if($last_cron_run != ''){
                    $time = $last_cron_run + 3600;
                }else{
                    $time = time();
                }
                break;
            case 'every_6_hour':
                if($last_cron_run != ''){
                    $time = $last_cron_run + (6 * 3600);
                }else{
                    $time = time();
                }
                break;
            case 'every_12_hour':
                if($last_cron_run != ''){
                    $time = $last_cron_run + (12 * 3600);
                }else{
                    $time = time();
                }
                break;
            case 'once_a_day':
                if($last_cron_run != ''){
                    $time = strtotime('today '. $notification['frequency_time']);
                }else{
                    $time = time();
                }
                break;
            case 'every_week':
                if($last_cron_run != ''){
                    $monday_this_week = strtotime($notification['frequency_day_of_week'].' this week');
                    $time = strtotime($notification['frequency_time'], $monday_this_week);
                }else{
                    $time = time();
                }
                break;
            case 'every_month':
                $timestamp = $this->get_fixed_monthly_timestamp_this_month($notification['frequency_day'], '11:00:00');

                if ($timestamp) {
                    $time = $timestamp;
                } else {
                    $time = time() + 3600;
                }
                break;
            
            default:
                // code...
                break;
        }

        return $time;
    }

    /**
     * get_fixed_monthly_timestamp_this_month
     * @param  string $day_of_month 
     * @param  string $time_of_day  
     * @return integer               
     */
    public function get_fixed_monthly_timestamp_this_month($day_of_month, $time_of_day = '11:00:00') {
        if($day_of_month == 'last'){
            $day_of_month = date('t');
        }

        $today = new DateTime();
        
        $fixed = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $today->format('Y-m-') . str_pad($day_of_month, 2, '0', STR_PAD_LEFT) . ' ' . $time_of_day
        );

        if (!$fixed || $fixed->format('d') != str_pad($day_of_month, 2, '0', STR_PAD_LEFT)) {
            return false; 
        }

        return $fixed->getTimestamp();
    }

    /**
     * Adds a contact.
     *
     * @param      <type>   $data                The data
     * @param      <type>   $customer_id         The customer identifier
     * @param      boolean  $not_manual_request  Not manual request
     *
     * @return     boolean  or contact id
     */
    public function add_contact($data, $customer_id, $not_manual_request = false)
    {
        $send_set_password_email = isset($data['send_set_password_email']) ? true : false;

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if (isset($data['permissions'])) {
            $permissions = $data['permissions'];
            unset($data['permissions']);
        }

        $data['email_verified_at'] = date('Y-m-d H:i:s');

        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        if (isset($data['is_primary'])) {
            $data['is_primary'] = 1;
            $this->db->where('userid', $customer_id);
            $this->db->update(db_prefix() . 'pur_contacts', [
                'is_primary' => 0,
            ]);
        } else {
            $data['is_primary'] = 0;
        }

        $password_before_hash = '';
        $data['userid']       = $customer_id;
        if (isset($data['password'])) {
            $password_before_hash = $data['password'];
            $data['password'] = app_hash_password($data['password']);
        }

        $data['datecreated'] = date('Y-m-d H:i:s');

        $data['email'] = trim($data['email']);


        $this->db->insert(db_prefix() . 'pur_contacts', $data);
        $contact_id = $this->db->insert_id();

        if ($contact_id) {

            if (isset($custom_fields)) {
                handle_custom_fields_post($contact_id, $custom_fields);
            }
     
            hooks()->do_action('after_vendor_contact_added', $contact_id);

            return $contact_id;
        }

        return false;
    }


    /**
     * { update contact }
     *
     * @param      <type>   $data            The data
     * @param      <type>   $id              The identifier
     * @param      boolean  $client_request  The client request
     *
     * @return     boolean 
     */
    public function update_contact($data, $id, $client_request = false)
    {
        $affectedRows = 0;
        $contact      = $this->get_contact($id);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password']             = app_hash_password($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }

        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }


        $send_set_password_email = isset($data['send_set_password_email']) ? true : false;
        $set_password_email_sent = false;
      
        $data['is_primary'] = isset($data['is_primary']) ? 1 : 0;

        // Contact cant change if is primary or not
        if ($client_request == true) {
            unset($data['is_primary']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pur_contacts', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if (isset($data['is_primary']) && $data['is_primary'] == 1) {
                $this->db->where('userid', $contact->userid);
                $this->db->where('id !=', $id);
                $this->db->update(db_prefix() . 'pur_contacts', [
                    'is_primary' => 0,
                ]);
            }
        }

       
        if ($affectedRows > 0 ) {
            return true;
        } 

        return false;
    }

    /**
     * { delete contact }
     *
     * @param      <type>   $id     The identifier
     *
     * @return     boolean  
     */
    public function delete_contact($id)
    {
        hooks()->do_action('before_delete_vendor_contact', $id);

        $this->db->where('id', $id);
        $result      = $this->db->get(db_prefix() . 'pur_contacts')->row();
        $customer_id = $result->userid;

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'pur_contacts');

        if ($this->db->affected_rows() > 0) {
            
            hooks()->do_action('vendor_contact_deleted', $id, $result);

            return true;
        }

        return false;
    }

    /**
     * Gets the contacts.
     *
     * @param      string  $vendor_id  The vendor identifier
     * @param      array   $where      The where
     *
     * @return     <type>  The contacts.
     */
    public function get_contacts($vendor_id = '', $where = ['active' => 1])
    {
        $this->db->where($where);
        if ($vendor_id != '') {
            $this->db->where('userid', $vendor_id);
        }
        $this->db->order_by('is_primary', 'DESC');

        return $this->db->get(db_prefix() . 'pur_contacts')->result_array();
    }

    /**
     * Gets the contact.
     *
     * @param      <type>  $id     The identifier
     *
     * @return     <type>  The contact.
     */
    public function get_contact($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'pur_contacts')->row();
    }

    /**
     * Gets the primary contacts.
     *
     * @param      <type>  $id     The identifier
     *
     * @return     <type>  The primary contacts.
     */
    public function get_primary_contacts($id)
    {
        $this->db->where('userid', $id);
        $this->db->where('is_primary', 1);
        return $this->db->get(db_prefix() . 'pur_contacts')->row();
    }

    /**
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update contact status Active/Inactive
     */
    public function change_contact_status($id, $status)
    {

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pur_contacts', [
            'active' => $status,
        ]);
        if ($this->db->affected_rows() > 0) {
            
            return true;
        }

        return false;
    }

    /**
     * check_case 
     * @param  [array] $mentions 
     * @param  [array] $case     
     * @return [array]           
     */
    public function check_case($mentions, $case)
    {
        if($case['workflow'] == ''){
            return $mentions;
        }

        $this->load->model('emails_model');

        $workflow = json_decode($case['workflow'], true);

        foreach ($workflow['trigger'] as $key => $value) {
            $check_trigger = false;
            
            switch ($value) {
                case 'contains_this_word':
                        $word = $workflow['word'][$key];
                        if($word != ''){
                            $title = mb_strtoupper($mentions['title']);
                            $content = mb_strtoupper($mentions['content']);
                            if (stripos($content, mb_strtoupper(trim($word))) !== false || stripos($title, mb_strtoupper(trim($word))) !== false) {
                                $check_trigger = true;
                            }
                        }
                    break;

                case 'does_not_contain_this_word':
                        $word = $workflow['word'][$key];
                        if($word != ''){
                            $title = mb_strtoupper($mentions['title']);
                            $content = mb_strtoupper($mentions['content']);
                            if (!stripos($content, mb_strtoupper(trim($word))) !== false && !stripos($title, mb_strtoupper(trim($word))) !== false) {
                                $check_trigger = true;
                            }
                        }
                    break;

                case 'topic_is_detected':
                        $topic = $workflow['topic'][$key];

                        if($mentions['topic'] != 0 && $mentions['topic'] == $topic){

                            $check_trigger = true;
                        }
                    break;

                case 'sentiment_is_detected':
                        $sentiment = $workflow['sentiment'][$key];

                        if($mentions['sentiment'] == $sentiment){
                            $check_trigger = true;
                        }
                    break;

                case 'matches_source':
                        $sources = $workflow['sources'][$key];

                        if($mentions['platform'] == $sources){
                            $check_trigger = true;
                        }
                    break;
                
                default:
                    break;
            }

            if($check_trigger){
                switch ($workflow['action'][$key]) {
                    case 'hide_mentions':
                        $this->db->where('id', $mentions['id']);
                        $this->db->update(db_prefix() . 'rep_mentions', ['status' => 'deleted']);
                        break;
                    case 'send_a_push_notification':
                        $staff = $this->staff_model->get($workflow['staff'][$key]);
                        $link = 'reputation/mentions?id='.$mentions['id'];
                        $notified = add_notification([
                            'description' => _l('a_new_mention_in_the_reputation_module'),
                            'touserid' => $staff->staffid,
                            'link' => $link,
                            'additional_data' => serialize([
                                _l('reputation'),
                            ]),
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$staff->staffid]);
                        }

                        break;
                    case 'send_an_email':
                        $staff = $this->staff_model->get($workflow['staff'][$key]);
                        $email = $staff->email;
                        $link = admin_url('reputation/mentions?id='.$mentions['id']);
                        $body = '<p>Hello,</p>
                                        <p>You have a new mention in the <strong>Reputation</strong> module.</p>
                                        <p>
                                          👉 <a href="'.$link.'" style="color: #1a73e8; text-decoration: none;">
                                            View the mention
                                          </a>
                                        </p>';

                        $this->emails_model->send_simple_email($email, _l('a_new_mention_in_the_reputation_module'), $body);

                        break;
                    case 'add_tag':

                        $tag = $workflow['tag'][$key];
                        $success = handle_tags_save($tag, $mentions['id'], 'rep_mention');

                        break;
                    case 'add_to_pdf_report':
                        $this->db->where('id', $mentions['id']);
                        $this->db->update(db_prefix() . 'rep_mentions', ['add_to_pdf' => 1]);

                        break;
                    
                    default:

                        break;
                }
            }
        }

        return $mentions;
    }
}
