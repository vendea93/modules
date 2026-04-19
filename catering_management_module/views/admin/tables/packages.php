<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'package_name',
    'description',
    'price_per_person',
    'min_guests',
    'max_guests',
    '(SELECT COUNT(id) FROM ' . db_prefix() . 'catering_package_items_link WHERE package_id = ' . db_prefix() . 'catering_packages.id) as items_count',
    'active',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'catering_packages';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $link = admin_url('catering_management_module/packages/package/' . $aRow['id']);

    // Package Name with link
    $row[] = '<a href="' . $link . '">' . $aRow['package_name'] . '</a>';

    // Description (truncated)
    $row[] = strlen($aRow['description']) > 100 ? substr($aRow['description'], 0, 100) . '...' : $aRow['description'];

    // Price
    $row[] = app_format_money($aRow['price_per_person'], get_base_currency());

    // Min/Max Guests
    $row[] = $aRow['min_guests'];
    $row[] = $aRow['max_guests'] ? $aRow['max_guests'] : _l('unlimited');

    // Items Count
    $row[] = '<span class="badge">' . $aRow['items_count'] . '</span>';

    // Active Status
    $toggleActive = '<div class="onoffswitch">
        <input type="checkbox" data-switch-url="' . admin_url('catering_management_module/packages/toggle_active') . '" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . ($aRow['active'] == 1 ? 'checked' : '') . '>
        <label class="onoffswitch-label" for="c_' . $aRow['id'] . '" title="' . _l('toggle_active_state') . '"></label>
    </div>';
    $row[] = $toggleActive;


    // Options
    $options = icon_btn($link, 'fa-regular fa-pen-to-square');
    if (staff_can('create', 'catering_packages')) {
        $options .= icon_btn('catering_management_module/packages/duplicate_package/' . $aRow['id'], 'fa fa-copy', 'btn-default', ['title' => _l('duplicate_package')]);
    }
    if (staff_can('delete', 'catering_packages')) {
        $options .= icon_btn('catering_management_module/packages/delete_package/' . $aRow['id'], 'fa fa-trash', 'btn-danger _delete');
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}
