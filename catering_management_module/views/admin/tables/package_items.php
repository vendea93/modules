<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'tblcatering_menu_items.id as item_id',
    'item_name',
    'tblcatering_menu_categories.name as category_name',
    'unit_price',
    'unit_cost',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'catering_menu_items';

$join = [
    'LEFT JOIN ' . db_prefix() . 'catering_menu_categories ON ' . db_prefix() . 'catering_menu_categories.id = ' . db_prefix() . 'catering_menu_items.category_id',
];

$where = ['AND ' . db_prefix() . 'catering_menu_items.active = 1'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'catering_menu_items.id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Checkbox
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['item_id'] . '"><label></label></div>';

    // Item Name
    $row[] = $aRow['item_name'];

    // Category
    $row[] = $aRow['category_name'];

    // Price and Cost
    $row[] = app_format_money($aRow['unit_price'], get_base_currency());
    $row[] = app_format_money($aRow['unit_cost'], get_base_currency());

    $output['aaData'][] = $row;
}
