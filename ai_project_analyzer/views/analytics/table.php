<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'owner',
    'COUNT(id) as total_analyses',
    'SUM(tokens_used) as total_tokens',
    'SUM(cost_usd) as total_cost',
    'MAX(created_at) as last_generated',
];

$sIndexColumn = 'owner';
$sTable = AI_PROJECT_ANALYZER_TABLE;

$join = [];
$where = [];
$groupBy = 'GROUP BY owner';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [], $groupBy);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<a href="' . admin_url('profile/' . $aRow['owner']) . '">' .
        staff_profile_image($aRow['owner'], [
            'tw-inline-block tw-h-7 tw-w-7 tw-rounded-full tw-ring-2 tw-ring-white',
        ], 'small', [
            'data-toggle' => 'tooltip',
            'data-title' => get_staff_full_name($aRow['owner']),
        ]) . '<span class="tw-font-medium tw-ml-1"> ' . get_staff_full_name($aRow['owner']) . '</span></a>';

    $row[] = (int) $aRow['total_analyses'];

    $row[] = number_format((int) $aRow['total_tokens']);

    $row[] = '$' . number_format((float) $aRow['total_cost'], 4);

    $row[] = _dt($aRow['last_generated']);

    $output['aaData'][] = $row;
}
