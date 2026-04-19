<?php 
defined('BASEPATH') or exit('No direct script access allowed');

$select = [
    'workspace_logo',
    'name',
    'super_admin',
    'timezone',
];

$where = [];

$base_workspace_id = ga_get_base_workspace_id();
$staffid = get_staff_user_id();

if(!is_admin()){
    array_push($where, 'AND (super_admin = "'.$staffid.'" OR ' . db_prefix() . 'ga_workspaces.id in (SELECT workspace_id FROM ' . db_prefix() . 'ga_workspace_members WHERE ' . db_prefix() . 'ga_workspace_members.workspace_id = ' . db_prefix() . 'ga_workspaces.id AND type = "staff" AND member_id = "'.$staffid.'"))');
}
$aColumns     = $select;
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'ga_workspaces';
$join         = [
];
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'ga_workspaces.id as id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row   = [];
    $row[] = ga_workspace_logo_html($aRow['id'], ['img', 'img-responsive'], 'small', ['width' => '60', 'height' => '60']);

    $categoryOutput = $aRow['name'] . ($aRow['id'] == $base_workspace_id ? ' <span class="label label-success">'._l('is_default').'</span>' : '');

    $categoryOutput .= '<div class="row-options">';

    $categoryOutput .= '<a href="' . admin_url('google_analytic/workspace_detail/' . $aRow['id']) . '" class="">' . _l('view') . '</a>';

    if ($aRow['id'] != $base_workspace_id) {
        $categoryOutput .= ' | <a href="#" class="text-success" onclick="set_default('.$aRow['id'].'); return false;">' . _l('set_default') . '</a>';
    }

    if ($aRow['id'] != $base_workspace_id && $aRow['super_admin'] == $staffid) {
        $categoryOutput .= ' | <a href="' . admin_url('google_analytic/delete_workspace/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $categoryOutput .= '</div>';
    $row[] = $categoryOutput;
    $row[] = get_staff_full_name($aRow['super_admin']);
    $row[] = $aRow['timezone'];


    $output['aaData'][] = $row;
}