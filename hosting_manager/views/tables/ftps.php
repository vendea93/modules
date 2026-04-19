<?php

defined('BASEPATH') or exit('No direct script access allowed');

$where = [];

if(isset($_GET['client']) && !empty($_GET['client'])){
    $where = [
        'AND client_id=' . $_GET['client'],
    ];
}
if(isset($_GET['hosting_id']) && !empty($_GET['hosting_id'])){
    $where = [
        'AND hosting_id=' . $_GET['hosting_id'],
    ];
}
if(isset($_GET['project']) && !empty($_GET['project'])){
    $where = [
        'AND project_id=' . $_GET['project'],
    ];
}

$aColumns = [
    db_prefix() . 'ftp_accounts.account_name',
    db_prefix() . 'ftp_accounts.hostname',
    db_prefix() . 'ftp_accounts.username',
    db_prefix() . 'ftp_accounts.port',
    db_prefix() . 'ftp_accounts.protocol',
    db_prefix() . 'ftp_accounts.root_directory',
    db_prefix() . 'ftp_accounts.status',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'ftp_accounts';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [
    'id',
    db_prefix() . 'ftp_accounts.hosting_id',
]);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {

    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        
        if ($aColumns[$i] == db_prefix() . 'ftp_accounts.account_name') {
            $_data =  e($_data);

            $_data .= '<div class="row-options">';

 
            if (staff_can('edit',  'ftp_accounts')) {
                 $_data .= ' <a href="#" onclick="get_ftp_modal('.$aRow['id'].')">' . _l('edit') . '</a>';
            }

            if (staff_can('delete',  'ftp_accounts')) {
                $_data .= ' | <a href="' . admin_url('hosting_manager/ftp/delete/' . $aRow['id'].'?hosting_id='.$aRow['hosting_id']) . '" class="_delete">' . _l('delete') . '</a>';
            }

            $_data .= '</div>';
        }elseif ($aColumns[$i] == db_prefix() . 'ftp_accounts.status') {
            if($_data == 'enable'){
                $_data = '<span class="label text-success" style="border: 1px solid rgb(0, 175, 53);">'.strtoupper(_l($_data)).'</span>';
            }else{
                $_data = '<span class="label text-danger " style="border: 1px solid #ff1100;">'._l($_data).'</span>';
            }
        }elseif ($aColumns[$i] ==  db_prefix() . 'ftp_accounts.root_directory') {
           
                $_data = '<code>'._l($_data).'</code>';
        }else{
            $_data = $_data;
        }
        
        $row[] = $_data;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
