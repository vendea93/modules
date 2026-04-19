<?php 
defined('BASEPATH') or exit('No direct script access allowed');

$select = [
    'name',
    'description',
    'status',
    'active',
    'id',
];

$where = [];
array_push($where, 'AND '.db_prefix() . 'ga_accounts.type = "'.$type.'"');
array_push($where, 'AND '.db_prefix() . 'ga_accounts.workspace_id = "'.ga_get_base_workspace_id().'"');


$aColumns     = $select;
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'ga_accounts';
$join         = [
];
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'ga_accounts.id as id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row   = [];
   
    $row[] = $aRow['name'];
    $row[] = $aRow['description'];

    $status_name = _l('ga_not_connected_yet');
    $label_class = 'default';

    if ($aRow['status'] == 1) {
        $label_class = 'success';
        $status_name = _l('ga_connected');
    } 

    $row[] = '<span class="label label-' . $label_class . ' s-status payment-status-' . $aRow['id'] . '">' . $status_name . '</span>';

    $checked = '';
    if ($aRow['active'] == 1) {
        $checked = 'checked';
    }

    $active = '<div class="onoffswitch">
    <input type="checkbox" data-switch-url="' . site_url() . 'admin/google_analytic/change_account_active" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
    <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
    </div>';

    $row[] = $active;

    $options = '<a href="#"  onclick="edit_account(this); return false;" data-id="'.$aRow['id'].'"  data-name="'.$aRow['name'].'"  data-description="'.$aRow['description'].'"
    class="btn btn-default mright5">
        <i class="fa fa-edit"></i>
    </a>';  

    $title = _l('ga_connect');
    if($aRow['status'] == 1){
        $title = _l('ga_reconnect');
    }
    $options .= '<a href="' . admin_url('google_analytic/'.$type.'_connect/' . $aRow['id']) . '"
    class="btn btn-success mright5"  data-toggle="tooltip" data-original-title="'.$title.'">
        <i class="fa fa-sign-in"></i>
    </a>';
    $options .= '<a href="' . admin_url('google_analytic/delete_account/' . $aRow['id']) . '"
    class="btn btn-danger _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';  
    $row[] = $options;
    $output['aaData'][] = $row;
}