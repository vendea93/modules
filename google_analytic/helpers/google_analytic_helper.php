<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * [ga_get_base_workspace_id]
 * @return [integer]
 */
function ga_get_base_workspace_id(){

    $staff_id = get_staff_user_id();

    $CI   = & get_instance();
    $CI->db->where('staffid', $staff_id);
    $staff = $CI->db->get(db_prefix().'staff')->row();
    if($staff && is_numeric($staff->ga_base_workspace_id) && $staff->ga_base_workspace_id > 0){

        $CI->db->where('id', $staff->ga_base_workspace_id);
        $workspace = $CI->db->get(db_prefix().'ga_workspaces')->row();
        if($workspace){
            return $staff->ga_base_workspace_id;
        }
    }
    return 0;
}

/**
 * Handles upload for workspace logo
 * @param  mixed $id expense id
 * @return void
 */
function ga_handle_workspace_logo($id)
{
    if (isset($_FILES['workspace_logo']['name']) && $_FILES['workspace_logo']['name'] != '' && _perfex_upload_error($_FILES['workspace_logo']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['workspace_logo']['error']);
        die;
    }
    $path = GOOGLE_ANALYTIC_UPLOAD_FOLDER . '/workspaces/' . $id . '/';
    $CI   = & get_instance();

    if (isset($_FILES['workspace_logo']['name'])) {

        // Get the temp file path
        $tmpFilePath = $_FILES['workspace_logo']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = $_FILES['workspace_logo']['name'];
            $newFilePath = $path . $filename;
            // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $CI->db->where('id', $id);
                $CI->db->update(db_prefix() . 'ga_workspaces', [
                    'workspace_logo' => $filename,
                ]);
            }
        }
    }
}

/**
 * [ga_workspace_logo_html]
 * @param  [integer] $id        
 * @param  array  $classes   
 * @param  string $type      
 * @param  array  $img_attrs 
 * @return [string]            
 */
function ga_workspace_logo_html($id, $classes = ['workspace-logo-image'], $type = 'small', $img_attrs = [])
{
    $url = base_url('assets/images/preview-not-available.jpg');

    $id = trim($id);

    $_attributes = '';
    foreach ($img_attrs as $key => $val) {
        $_attributes .= $key . '=' . '"' . e($val) . '" ';
    }

    $blankImageFormatted = '<img src="' . $url . '" ' . $_attributes . ' class="' . implode(' ', $classes) . '" />';

    $CI     = & get_instance();
    $result = $CI->app_object_cache->get('workspace-logo-image-data-' . $id);

    if (!$result) {
        $CI->db->select('workspace_logo,name');
        $CI->db->where('id', $id);
        $result = $CI->db->get(db_prefix() . 'ga_workspaces')->row();
        $CI->app_object_cache->add('workspace-logo-image-data-' . $id, $result);
    }

    if (!$result) {
        return $blankImageFormatted;
    }

    if ($result && $result->workspace_logo !== null) {
        $profileImagePath = 'modules/google_analytic/uploads/workspaces/' . $id . '/' . $result->workspace_logo;

        if (file_exists($profileImagePath)) {
            $profile_image = '<img ' . $_attributes . ' src="' . base_url($profileImagePath) . '" class="' . implode(' ', $classes) . '" />';
        } else {
            return $blankImageFormatted;
        }
    } else {
        $profile_image = '<img src="' . $url . '" ' . $_attributes . ' class="' . implode(' ', $classes) . '" />';
    }

    return $profile_image;
}

/**
 * [ga_get_google_config]
 * @return [array] 
 */
function ga_get_google_config(){
    $config = [
            'client_id' => get_option('ga_google_app_id'),
            'client_secret' => get_option('ga_google_app_secret'),
        ];

    return $config;
}

/**
 * [ga_get_account_ids_by_base_workspace]
 * @param  [string]  $type      
 * @param  boolean $return_ids
 * @return [array]             
 */
function ga_get_account_ids_by_base_workspace($type, $return_ids = false){
    $CI   = & get_instance();
    $workspace_id = ga_get_base_workspace_id();
    $CI->db->where('workspace_id', $workspace_id);
    $CI->db->where('type', $type);
    $CI->db->where('active', 1);
    $CI->db->where('status', 1);
    $accounts = $CI->db->get(db_prefix().'ga_accounts')->result_array();

    if($accounts){
        if($return_ids){
            $account_ids = [0];
            foreach ($accounts as $key => $value) {
                $account_ids[] = $value['id'];
            }

            return implode(',', $account_ids);
        }
    }
    
    return $accounts;
}

/**
 * [ga_get_contact_base_workspace_id]
 * @return [integer]
 */
function ga_get_contact_base_workspace_id(){

    $contact_id = get_contact_user_id();

    $CI   = & get_instance();
    $CI->db->where('id', $contact_id);
    $contact = $CI->db->get(db_prefix().'contacts')->row();
    if($contact && is_numeric($contact->ga_base_workspace_id) && $contact->ga_base_workspace_id > 0){

        $CI->db->where('id', $contact->ga_base_workspace_id);
        $workspace = $CI->db->get(db_prefix().'ga_workspaces')->row();
        if($workspace){
            return $contact->ga_base_workspace_id;
        }
    }

    $CI->db->where('member_id', $contact_id);
    $CI->db->where('type', 'contact');
    $member = $CI->db->get(db_prefix().'ga_workspace_members')->row();
    if($member){
        $CI->load->model('google_analytic/google_analytic_model');
        $CI->google_analytic_model->set_contact_default_workspace($member->workspace_id);
        
        return $member->workspace_id;
    }

    return 0;
}

/**
 * [ga_get_contact_workspace_ids]
 * @param  boolean $return_workspace_list
 * @return [array]                        
 */
function ga_get_contact_workspace_ids($return_workspace_list = false){

    $contact_id = get_contact_user_id();

    $CI   = & get_instance();
    $CI->db->where('member_id', $contact_id);
    $CI->db->where('type', 'contact');
    $members = $CI->db->get(db_prefix().'ga_workspace_members')->result_array();

    $workspace_ids = [0];
    foreach ($members as $key => $value) {
        $workspace_ids[] = $value['workspace_id'];
    }
   
    if($return_workspace_list){
        $CI->db->where('(id in ('. implode(',',$workspace_ids).'))');
        $workspaces = $CI->db->get(db_prefix().'ga_workspaces')->result_array();
        return $workspaces;
    }

    return $workspace_ids;
}

/**
 * [ga_get_contact_account_ids_by_base_workspace]
 * @param  [string]  $type      
 * @param  boolean $return_ids
 * @return [array]             
 */
function ga_get_contact_account_ids_by_base_workspace($type, $return_ids = false){
    $CI   = & get_instance();
    $workspace_id = ga_get_contact_base_workspace_id();
    $CI->db->where('workspace_id', $workspace_id);
    $CI->db->where('type', $type);
    $CI->db->where('active', 1);
    $CI->db->where('status', 1);
    $accounts = $CI->db->get(db_prefix().'ga_accounts')->result_array();

    if($accounts){
        if($return_ids){
            $account_ids = [0];
            foreach ($accounts as $key => $value) {
                $account_ids[] = $value['id'];
            }

            return implode(',', $account_ids);
        }
    }
    
    return $accounts;
}

/**
 * [ga_check_csrf_protection]
 * @return [string]
 */
function ga_check_csrf_protection()
{
    if(config_item('csrf_protection')){
        return 'true';
    }
    return 'false';
}

/**
 * [ga_check_workspace_client]
 * @return [boolean]
 */
function ga_check_workspace_client(){
    $CI   = & get_instance();
    $CI->db->where('member_id', get_contact_user_id());
    $CI->db->where('type', 'contact');
    $workspace = $CI->db->get(db_prefix().'ga_workspace_members')->row();
    if($workspace){
        return true;
    }

    return false;
}

/**
 * [ga_get_staff_metrics description]
 * @return [string]
 */
function ga_get_staff_metrics(){

    $staff_id = get_staff_user_id();
    $ga_analytic_metrics = get_option('ga_analytic_metrics');

    $CI   = & get_instance();
    $CI->db->where('staffid', $staff_id);
    $staff = $CI->db->get(db_prefix().'staff')->row();
    if($staff && $staff->ga_analytic_metrics != ''){
        return $staff->ga_analytic_metrics;
    }

    $CI->db->where('staffid', $staff_id);
    $member = $CI->db->update(db_prefix().'staff', ['ga_analytic_metrics' => $ga_analytic_metrics]);
    

    return $ga_analytic_metrics;
}

/**
 * [ga_get_contact_metrics description]
 * @return [string]
 */
function ga_get_contact_metrics(){

    $contact_id = get_contact_user_id();
    $ga_analytic_metrics = get_option('ga_analytic_metrics');

    $CI   = & get_instance();
    $CI->db->where('id', $contact_id);
    $contact = $CI->db->get(db_prefix().'contacts')->row();
    if($contact && $contact->ga_analytic_metrics != ''){
        return $contact->ga_analytic_metrics;
    }

    $CI->db->where('id', $contact_id);
    $member = $CI->db->update(db_prefix().'contacts', ['ga_analytic_metrics' => $ga_analytic_metrics]);
    

    return $ga_analytic_metrics;
}