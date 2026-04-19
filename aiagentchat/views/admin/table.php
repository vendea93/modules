<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'chat_name',
    'workflow_id',
    'is_enabled',
    'created_at'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'aiagentchat_chats';

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['chat_name'];
        $row[] = $aRow['workflow_id'];

        $checked = '';
        if ($aRow['is_enabled'] == 1) {
            $checked = 'checked';
        }
        $row[] = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . AIAGENTCHAT_MODULE_NAME . '/update_chat_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';


        $row[] = $aRow['created_at'];


        if (staff_can('view', 'aiagentchat')) {
            $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
            $options .= '<a href="' . admin_url('aiagentchat/create/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';
        }

        if (staff_can('assign_chat', 'aiagentchat')) {
            $assignUrl = admin_url('aiagentchat/assign/' . (int)$aRow['id']);
            $options .= '<a href="' . $assignUrl . '" class="btn btn-default btn-sm" title="' . _l('aiagentchat_assignments') . '">
  <i class="fa fa-link"></i>
</a>';
        }

        if (staff_can('delete', 'aiagentchat')) {
            $options .= '<a href="' . admin_url('aiagentchat/delete/' . $aRow['id']) . '"
    class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';
        }

        $options .= '</div>';

        $row[] = $options;
    }

    $output['aaData'][] = $row;
}
