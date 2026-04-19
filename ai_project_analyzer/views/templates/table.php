<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'id',
    'name',
    'staff_id',
    'prompt',
    'datecreated',
];
$sIndexColumn = 'datecreated';
$sTable = AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE;
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], []);
$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name') {
            $_data = '<a href="' . admin_url('ai_project_analyzer/templates/template/' . $aRow['id']) . '" class="tw-font-medium">' . e($_data) . '</a>';

            $_data .= '<div class="row-options">';

            if (staff_can('edit', 'ai_project_analyzer')) {
                $_data .= '<a href="' . admin_url('ai_project_analyzer/templates/template/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            }

            if (staff_can('delete', 'ai_project_analyzer')) {
                $_data .= ' | <a href="' . admin_url('ai_project_analyzer/templates/delete/' . $aRow['id']) . '" class="_delete">' . _l('delete') . '</a>';
            }
        }
        if ($aColumns[$i] == 'staff_id') {
            $_data = '<a href="' . admin_url('profile/' . $aRow['staff_id']) . '">' .
                staff_profile_image($aRow['staff_id'], [
                    'tw-inline-block tw-h-7 tw-w-7 tw-rounded-full tw-ring-2 tw-ring-white',
                ], 'small', [
                    'data-toggle' => 'tooltip',
                    'data-title' => get_staff_full_name($aRow['staff_id']),
                ]) . '</a>';
        }
        if ($aColumns[$i] == 'prompt') {
            $_data = substr(e($_data), 0, 150) . '...';
        }
        $_data .= '</div>';
        $row[] = $_data;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
