<?php

$sTable = db_prefix().'catering_menu_categories';
$sIndexColumn = 'id';

$aColumns = [
	'name',
	'parent_id',
	'display_order',
	$sTable.'.active as active',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id', 'icon', 'color']);

$output = $result['output'];
$rResult = $result['rResult'];

$CI = &get_instance();
$CI->load->model('catering_menu_categories_model');

foreach ($rResult as $aRow)
{
	$row = [];

	$categoryBadge = '<span class="label" '.catering_category_color($aRow['color']).'>'.
		($aRow['icon'] ? '<i class="'.$aRow['icon'].'"></i> ' : '').
		htmlspecialchars($aRow['name']).'</span>';
	$row[] = $categoryBadge;

	$parent = '';
	if ($aRow['parent_id'])
	{
		$parentCat = $CI->catering_menu_categories_model->get($aRow['parent_id']);
		if ($parentCat)
		{
			$parent = htmlspecialchars($parentCat->name);
		}
	}
	$row[] = $parent;

	$row[] = $aRow['display_order'];
	$row[] = catering_active_badge($aRow['active']);

	$options = '';
	if (staff_can('edit', 'catering_categories'))
	{
		$options .= '<a href="'.admin_url('catering_management_module/categories/category/'.$aRow['id']).'" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a> ';
	}
	if (staff_can('delete', 'catering_categories'))
	{
		$options .= '<a href="'.admin_url('catering_management_module/categories/delete_category/'.$aRow['id']).'" class="btn btn-danger btn-icon _delete"><i class="fa fa-trash"></i></a>';
	}
	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die;