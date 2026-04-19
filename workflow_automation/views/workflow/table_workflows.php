<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
    'created_at',
    'created_by',
    'private',
    'enabled',
    ];

$join = [
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'wa_workflows';

$where  = [];

if ($this->ci->input->post('from_date')
    && $this->ci->input->post('from_date') != '') {
    array_push($where, 'AND date(created_at) >= "'.to_sql_date($this->ci->input->post('from_date')).'"');
}

if ($this->ci->input->post('to_date')
    && $this->ci->input->post('to_date') != '') {
    array_push($where, 'AND date(created_at) <= "'.to_sql_date($this->ci->input->post('to_date')).'"');
}

if ($this->ci->input->post('categories') && count($this->ci->input->post('categories')) > 0) {
    array_push($where, 'AND category_id IN (' . implode(',', $this->ci->input->post('categories')) . ')');
}

$user = get_staff_user_id();

if(!has_permission('workflow_automation', '', 'view')){
    array_push($where, 'AND (created_by = '.$user.' OR private = 0)');
}

$filter = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id', 'start_email', 'description', 'category_id'
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];


    $row[] = '<a href="'.admin_url('workflow_automation/workflow_detail/'.$aRow['id']).'">'.$aRow['name'].'</a>';

    $row[] = get_staff_full_name($aRow['created_by']);

    $row[] = wa_get_category_name_by_id($aRow['category_id']);

    $row[] = _dt($aRow['created_at']);

    $toggleActive = '<div class="onoffswitch">
    <input type="checkbox" data-switch-url="' . admin_url() . 'workflow_automation/change_workflow_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . ($aRow['enabled'] == 1 ? 'checked' : '') . '>
    <label class="onoffswitch-label" for="' . $aRow['id'] . '"></label>
    </div>';

    // For exporting
    $toggleActive .= '<span class="hide">' . ($aRow['enabled'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

    $row[] = $toggleActive;

    $option_str = '';

    if(has_permission('workflow_automation', '', 'edit')){
        $option_str = '<a href="javascript:void(0);" class="btn btn-icon btn-warning" onclick="edit_workflow(this); return false;" data-id="'.$aRow['id'].'" data-name="'.$aRow['name'].'" data-description="'.$aRow['description'].'" data-private="'.$aRow['private'].'" data-enabled="'.$aRow['enabled'].'"  data-start_email="'.$aRow['start_email'].'" data-category_id="'.$aRow['category_id'].'" ><i class="fa fa-pencil"></i></a>';
    }

    if(has_permission('workflow_automation', '', 'delete')){
        $option_str .= '<a href="'.admin_url('workflow_automation/delete_workflow/'.$aRow['id']).'" class="btn btn-icon btn-danger" ><i class="fa fa-remove"></i></a>';
    }


    $row[] = $option_str;

    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
