<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'created_at',
    'flow_id',
    'node_type',
    'node_id',
    'rel_type',
    'rel_id',
    'condition_field',
    'output',
    'result',
    'action',
    
    ];

$join = [
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'wa_flows_logs';

$where  = [];

if ($this->ci->input->post('from_date')
    && $this->ci->input->post('from_date') != '') {
    array_push($where, 'AND date(created_at) >= "'.to_sql_date($this->ci->input->post('from_date')).'"');
}

if ($this->ci->input->post('to_date')
    && $this->ci->input->post('to_date') != '') {
    array_push($where, 'AND date(created_at) <= "'.to_sql_date($this->ci->input->post('to_date')).'"');
}

if ($this->ci->input->post('workflow') && count($this->ci->input->post('workflow')) > 0) {
    array_push($where, 'AND flow_id IN (' . implode(',', $this->ci->input->post('workflow')) . ')');
}

$user = get_staff_user_id();

$filter = [];


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id', db_prefix().'wa_flows_logs.condition as log_condition', 
    
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];


    $row[] = _dt($aRow['created_at']);

    $row[] = '<a href="'.admin_url('workflow_automation/workflow_detail/'.$aRow['flow_id']).'">'.wa_get_workflow_name($aRow['flow_id']).'</a>';

    $row[] = $aRow['node_type'] != '' ? _l('wa_'.$aRow['node_type']).' - '.$aRow['node_id'] : '';

    $row[] = _l('wa_'.$aRow['rel_type']);

    $related_to_str = wa_get_related_to_info($aRow['rel_type'], $aRow['rel_id']);
   

    $row[] = $related_to_str;

    $row[] = ($aRow['node_type'] == 'condition') ? _l($aRow['condition_field']) : '';

    $row[] = ($aRow['node_type'] == 'condition') ? _l('wa_'.$aRow['log_condition']) : '';

    $row[] = ($aRow['node_type'] == 'action') ? _l('wa_'.$aRow['action']) : '';

    $row[] = ($aRow['node_type'] == 'condition') ? _l('wa_'.$aRow['output']) : '';

    $result_str = '';
    if($aRow['result'] == 'success'){
        $result_str =  ($aRow['node_type'] == 'action' || $aRow['node_type'] == 'flow_start') ? '<span class="label label-success">'._l('wa_'.$aRow['result']).'</span>' : '';
    }else if($aRow['result'] == 'fail'){
        $result_str =  ($aRow['node_type'] == 'action' || $aRow['node_type'] == 'flow_start') ?  '<span class="label label-danger">'._l('wa_'.$aRow['result']).'</span>' : '';
    }

    $row[] = $result_str;

    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
