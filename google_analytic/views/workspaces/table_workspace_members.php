<?php 
defined('BASEPATH') or exit('No direct script access allowed');

$select = [
    'member_id',
    db_prefix() . 'ga_workspace_members.addedfrom as member_addedfrom',
    db_prefix() . 'ga_workspace_members.dateadded as dateadded',
    db_prefix() . 'ga_workspace_members.id as id',
];

$where = [];
array_push($where, 'AND '.db_prefix() . 'ga_workspace_members.type = "'.$type.'"');
array_push($where, 'AND '.db_prefix() . 'ga_workspace_members.workspace_id = "'.$workspace_id.'"');

$aColumns     = $select;
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'ga_workspace_members';
$join         = [
    'LEFT JOIN ' . db_prefix() . 'ga_workspaces ON ' . db_prefix() . 'ga_workspaces.id = ' . db_prefix() . 'ga_workspace_members.workspace_id'
];
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['type', 'workspace_id', 'super_admin', db_prefix() . 'ga_workspaces.addedfrom as workspace_addedfrom']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row   = [];
    if($aRow['type'] == 'staff'){
        $_data = staff_profile_image($aRow['member_id'], [
                'staff-profile-image-small',
                ]).' <a href="'.admin_url('staff/member/'.$aRow['member_id']).'">' . get_staff_full_name($aRow['member_id']) . '</a>';
    }else{
        $userid = get_user_id_by_contact_id($aRow['member_id']);
        $_data = '<img src="' . e(contact_profile_image_url($aRow['member_id'])) . '" class="client-profile-image-small mright5"> '.get_contact_full_name($aRow['member_id']).'(<a href="'.admin_url('clients/client/'.$userid).'">' . get_company_name($userid) . '</a>)';

    }

    $row[] = $_data;
    $row[] = get_staff_full_name($aRow['member_addedfrom']);
    $row[] = _dt($aRow['dateadded']);

    if(is_admin() || $aRow['super_admin'] == get_staff_user_id() || $aRow['workspace_addedfrom'] == get_staff_user_id()){
        $row[] = '<a href="' . admin_url('google_analytic/delete_workspace_member/'. $aRow['workspace_id'].'/' . $aRow['id']) . '"
        class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
            <i class="fa-regular fa-trash-can fa-lg"></i>
        </a>';
    }else{
    $row[] = '';
    }

    $output['aaData'][] = $row;
}