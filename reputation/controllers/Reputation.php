<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * This class describes a Reputation.
 */

require 'modules/reputation/vendor/autoload.php';

class Reputation extends AdminController {
	public function __construct() {
		parent::__construct();
		$this->load->model('reputation_model');
        hooks()->do_action('reputation_init');
	}

	/**
     * { vendors }
     */
    public function vendors(){

        $data['title']          = _l('vendor');

        $this->load->view('vendors/manage', $data);
    }

    /**
     * { table vendor }
     */
    public function table_vendor(){
        $this->app->get_table_data(module_views_path('reputation', 'vendors/table_vendor'));
    }

    /**
     * { vendor }
     *
     * @param      string  $id     The vendor
     * @return      view
     */
    public function vendor($id = '')
    {

        if ($this->input->is_ajax_request()) {
            if ($id == '') {
                $data = $this->input->post();
                $id = $this->reputation_model->add_vendor($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('vendor')));

                    echo json_encode([
                        'url'       => admin_url('reputation/vendors'),
                        'success' => true,
                    ]);
                    die;
                }
            } else {

                $success = $this->reputation_model->update_vendor($this->input->post(), $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('vendor')));
                }

                echo json_encode([
                    'url'       => admin_url('reputation/vendors'),
                    'success' => $success,
                ]);
                die;
            }
        }

        $group         = !$this->input->get('group') ? 'profile' : $this->input->get('group');
        $data['group'] = $group;

        if ($id == '') {
            $title = _l('add_new', _l('vendor_lowercase'));
        } else {
            $client                = $this->reputation_model->get_vendor($id);
            $data['customer_tabs'] = get_customer_profile_tabs();

            if (!$client) {
                show_404();
            }


            $data['group'] = $this->input->get('group');

            $data['title']                 = _l('acc_vendor');

            $data['tab'][] = ['name' => 'profile', 'icon' => '<i class="fa fa-user-circle menu-icon"></i>'];
            $data['tab'][] = ['name' => 'contacts','icon' => '<i class="fa fa-users menu-icon"></i>'];


            if($data['group'] == ''){
                $data['group'] = 'profile';
            }
            $data['tabs']['view'] = 'vendors/groups/'.$data['group'];

            $data['staff'] = $this->staff_model->get('', ['active' => 1]);

            $data['client'] = $client;
            $title          = $client->company;

            // Get all active staff members (used to add reminder)
            $data['members'] = $data['staff'];

            if (!empty($data['client']->company)) {
                // Check if is realy empty client company so we can set this field to empty
                // The query where fetch the client auto populate firstname and lastname if company is empty
                if (acc_is_empty_vendor_company($data['client']->userid)) {
                    $data['client']->company = '';
                }
            }
        }

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        if ($id != '') {
            $customer_currency = $data['client']->default_currency;

            foreach ($data['currencies'] as $currency) {
                if ($customer_currency != 0) {
                    if ($currency['id'] == $customer_currency) {
                        $customer_currency = $currency;

                        break;
                    }
                } else {
                    if ($currency['isdefault'] == 1) {
                        $customer_currency = $currency;

                        break;
                    }
                }
            }

            if (is_array($customer_currency)) {
                $customer_currency = (object) $customer_currency;
            }

            $data['customer_currency'] = $customer_currency;


        }

        $data['bodyclass'] = 'customer-profile dynamic-create-groups';

        $data['title']     = $title;

        $this->load->view('vendors/vendor', $data);
    }

    /**
     * { delete vendor }
     *
     * @param      <type>  $id     The identifier
     * @return      redirect
     */
    public function delete_vendor($id){

        if (!$id) {
            redirect(admin_url('reputation/vendors'));
        }
        $response = $this->reputation_model->delete_vendor($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('customer_delete_transactions_warning'));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('vendor')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('vendor_lowercase')));
        }
        redirect(admin_url('reputation/vendors'));
    }

    /**
     * Determines if vendor code exists.
     */
    public function vendor_code_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if the email is the same
                $id = $this->input->post('userid');
                if ($id != '') {
                    $this->db->where('userid', $id);
                    $pur_vendor = $this->db->get(db_prefix().'pur_vendor')->row();
                    if ($pur_vendor->vendor_code == $this->input->post('vendor_code')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('vendor_code', $this->input->post('vendor_code'));
                $total_rows = $this->db->count_all_results(db_prefix().'pur_vendor');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * [vendor_bulk_action]
     */
    public function vendor_bulk_action()
    {
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids    = $this->input->post('ids');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($this->reputation_model->delete_vendor($id)) {
                            $total_deleted++;
                        }
                    } 
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_vendors_deleted', $total_deleted));
        }
    }


    /**
     * { import vendor }
     */
    public function vendor_import()
    {
        if (!has_permission('reputation_vendor', '', 'create')) {
            access_denied('reputation');
        }

        $this->load->model('staff_model');
        $data_staff = $this->staff_model->get(get_staff_user_id());

        /*get language active*/
        if ($data_staff) {
            if ($data_staff->default_language != '') {
                $data['active_language'] = $data_staff->default_language;

            } else {

                $data['active_language'] = get_option('active_language');
            }

        } else {
            $data['active_language'] = get_option('active_language');
        }
        $data['title'] = _l('import_excel');

        $this->load->view('vendors/import_excel', $data);
    }

    /**
     * { import job position excel }
     */
    public function import_file_xlsx_vendor()
    {
        if(!class_exists('XLSXReader_fin')){
            require_once(module_dir_path(REPUTATION_MODULE_NAME).'/assets/plugins/XLSXReader/XLSXReader.php');
        }
        require_once(module_dir_path(REPUTATION_MODULE_NAME).'/assets/plugins/XLSXWriter/xlsxwriter.class.php');


        $filename ='';
        if($this->input->post()){
            if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {

                $this->delete_error_file_day_before();

                // Get the temp file path
                $tmpFilePath = $_FILES['file_csv']['tmp_name'];                
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    $tmpDir = TEMP_FOLDER . '/' . time() . uniqid() . '/';

                    if (!file_exists(TEMP_FOLDER)) {
                        mkdir(TEMP_FOLDER, 0755);
                    }

                    if (!file_exists($tmpDir)) {
                        mkdir($tmpDir, 0755);
                    }

                    // Setup our new file path
                    $newFilePath = $tmpDir . $_FILES['file_csv']['name'];                    

                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        //Writer file
                        $writer_header = array(
                            _l('vendor_code')          =>'string',
                            _l('company')                      =>'string',
                            _l('vat')                     =>'string',
                            _l('phonenumber')                     =>'string',
                            _l('country')                     =>'string',
                            _l('city')                     =>'string',
                            _l('zip')                     =>'string',
                            _l('state')                     =>'string',
                            _l('address')                     =>'string',
                            _l('website')                     =>'string',
                            _l('pur_billing_street')                     =>'string',
                            _l('pur_billing_city')                     =>'string',
                            _l('pur_billing_state')                     =>'string',
                            _l('pur_billing_zip')                     =>'string',
                            _l('pur_billing_country')                     =>'string',
                            _l('pur_shipping_street')                     =>'string',
                            _l('pur_shipping_city')                     =>'string',
                            _l('pur_shipping_state')                     =>'string',
                            _l('pur_shipping_zip')                     =>'string',
                            _l('pur_shipping_country')                     =>'string',
                            _l('error')                     =>'string',
                        );

                        $widths_arr = array();
                        for($i = 1; $i <= count($writer_header); $i++ ){
                            $widths_arr[] = 40;
                        }

                        $writer = new XLSXWriter();
                        $writer->writeSheetHeader('Sheet1', $writer_header,  $col_options = ['widths'=>$widths_arr ]);

                        //Reader file
                        $xlsx = new XLSXReader_fin($newFilePath);
                        $sheetNames = $xlsx->getSheetNames();
                        $data = $xlsx->getSheetData($sheetNames[1]);

                        $total_rows = 0;
                        $total_row_false    = 0; 

                        for ($row = 1; $row < count($data); $row++) {

                            $total_rows++;

                            $rd = array();
                            $flag = 0;
                            $flag2 = 0;

                            $string_error ='';

                            $value_vendor_code    = isset($data[$row][0]) ? $data[$row][0] : '' ;
                            $value_company            = isset($data[$row][1]) ? $data[$row][1] : '';
                            $value_vat            = isset($data[$row][2]) ? $data[$row][2] : '';
                            $value_phonenumber            = isset($data[$row][3]) ? $data[$row][3] : '';
                            $value_country            = isset($data[$row][4]) ? $data[$row][4] : '';
                            $value_city            = isset($data[$row][5]) ? $data[$row][5] : '';
                            $value_zip            = isset($data[$row][6]) ? $data[$row][6] : '';
                            $value_state            = isset($data[$row][7]) ? $data[$row][7] : '';
                            $value_address            = isset($data[$row][8]) ? $data[$row][8] : '';
                            $value_website            = isset($data[$row][9]) ? $data[$row][9] : '';
                            $value_pur_billing_street            = isset($data[$row][10]) ? $data[$row][10] : '';
                            $value_pur_billing_city            = isset($data[$row][11]) ? $data[$row][11] : '';
                            $value_pur_billing_state            = isset($data[$row][12]) ? $data[$row][12] : '';
                            $value_pur_billing_zip            = isset($data[$row][13]) ? $data[$row][13] : '';
                            $value_pur_billing_country            = isset($data[$row][14]) ? $data[$row][14] : '';
                            $value_pur_shipping_street            = isset($data[$row][15]) ? $data[$row][15] : '';
                            $value_pur_shipping_city            = isset($data[$row][16]) ? $data[$row][16] : '';
                            $value_pur_shipping_state            = isset($data[$row][17]) ? $data[$row][17] : '';
                            $value_pur_shipping_zip            = isset($data[$row][18]) ? $data[$row][18] : '';
                            $value_pur_shipping_country            = isset($data[$row][19]) ? $data[$row][19] : '';

                            if(is_null($value_vendor_code) == true || $value_vendor_code ==''){
                                $string_error .=_l('vendor_code'). _l('not_yet_entered');
                                $flag = 1;
                            }else{
                                $this->db->where('vendor_code', $value_vendor_code);
                                $total_rows_check = $this->db->count_all_results(db_prefix().'pur_vendor');
                                if ($total_rows_check > 0) {
                                    $string_error .=_l('vendor_code'). _l('already_exist');
                                    $flag = 1;
                                }
                            }

                            if(is_null($value_company) == true || $value_company ==''){
                                $string_error .=_l('company'). _l('not_yet_entered');
                                $flag = 1;
                            }

                            if(($flag == 1) || $flag2 == 1 ){
                                //write error file
                                $writer->writeSheetRow('Sheet1', [
                                    $value_vendor_code,
                                    $value_company,
                                    $value_vat,
                                    $value_phonenumber,
                                    $value_country,
                                    $value_city,
                                    $value_zip,
                                    $value_state,
                                    $value_address,
                                    $value_website,
                                    $value_pur_billing_street,
                                    $value_pur_billing_city,
                                    $value_pur_billing_state,
                                    $value_pur_billing_zip,
                                    $value_pur_billing_country,
                                    $value_pur_shipping_street,
                                    $value_pur_shipping_city,
                                    $value_pur_shipping_state,
                                    $value_pur_shipping_zip,
                                    $value_pur_shipping_country,
                                    $string_error,
                                ]);

                                $total_row_false++;
                            }

                            if($flag == 0 && $flag2 == 0){
                                $rd['vendor_code']                = $value_vendor_code;
                                $rd['company']                         = $value_company;
                                $rd['vat']                         = $value_vat;
                                $rd['phonenumber']                         = $value_phonenumber;
                                $rd['country']                         = $value_country;
                                $rd['city']                         = $value_city;
                                $rd['zip']                         = $value_zip;
                                $rd['state']                         = $value_state;
                                $rd['address']                         = $value_address;
                                $rd['website']                         = $value_website;
                                $rd['billing_street']                         = $value_pur_billing_street;
                                $rd['billing_city']                         = $value_pur_billing_city;
                                $rd['billing_state']                         = $value_pur_billing_state;
                                $rd['billing_zip']                         = $value_pur_billing_zip;
                                $rd['billing_country']                         = $value_pur_billing_country;
                                $rd['shipping_street']                         = $value_pur_shipping_street;
                                $rd['shipping_city']                         = $value_pur_shipping_city;
                                $rd['shipping_state']                         = $value_pur_shipping_state;
                                $rd['shipping_zip']                         = $value_pur_shipping_zip;
                                $rd['shipping_country']                         = $value_pur_shipping_country;

                                $rows[] = $rd;
                                $response = $this->reputation_model->add_vendor($rd);

                            }


                        }

                        $total_rows = $total_rows;
                        $total_row_success = isset($rows) ? count($rows) : 0;
                        $dataerror = '';
                        $message ='Not enought rows for importing';

                        if($total_row_false != 0){
                            $filename = 'Import_vendor_error_'.get_staff_user_id().'_'.strtotime(date('Y-m-d H:i:s')).'.xlsx';
                            $writer->writeToFile(str_replace($filename, REPUTATION_IMPORT_ITEM_ERROR.$filename, $filename));
                        }

                    }
                }
            }
        }


        if (file_exists($newFilePath)) {
            @unlink($newFilePath);
        }

        echo json_encode([
            'message'           => $message,
            'total_row_success' => $total_row_success,
            'total_row_false'   => $total_row_false,
            'total_rows'        => $total_rows,
            'site_url'          => site_url(),
            'staff_id'          => get_staff_user_id(),
            'filename'          => REPUTATION_IMPORT_ITEM_ERROR.$filename,
        ]);
    }

    /**
     * topic management
     * @return view
     */
    public function topic_management(){
        if (!has_permission('reputation_topic', '', 'view')) {
            access_denied('reputation');
        }

        $data['title']         = _l('topic_management');

        $this->load->view('topics/manage', $data);
    }

    /**
     * add topic
     * @return json
     */
    public function topic(){
        $data = $this->input->post();
        if($data['id'] == ''){
            if (!has_permission('reputation_topic', '', 'create')) {
                access_denied('reputation');
            }
            $success = $this->reputation_model->add_topic($data);
            if($success){
                $message = _l('added_successfully');
            }else {
                $message = _l('topics_failed');
            }
        }else{
            if (!has_permission('reputation_topic', '', 'edit')) {
                access_denied('reputation');
            }
            $id = $data['id'];
            unset($data['id']);
            $success = $this->reputation_model->update_topic($data, $id);
            if ($success) {
                $message = _l('updated_successfully', _l('topic'));
            }
        }
        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * topic table
     * @return json
     */
    public function topic_table()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');

            $select = [
                'content',
                'type',
                'scales',
                'active',
                'datecreated',
            ];

            $where = [];

            $type = $this->input->post("type");
            if($type != ''){
                array_push($where, 'AND type ="'.$type.'"');
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'rep_topics';
            $join         = [
            ];
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id']);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row   = [];
                $categoryOutput = $aRow['content'];

                $categoryOutput .= '<div class="row-options">';

                if (has_permission('reputation_topic', '', 'edit')) {
                    $categoryOutput .= '<a href="javascript:void(0)" onclick="edit_topic(' . $aRow['id'] . '); return false;">' . _l('edit') . '</a>';
                }

                if (has_permission('reputation_topic', '', 'delete')) {
                    $categoryOutput .= ' | <a href="' . admin_url('reputation/delete_topic/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }

                $categoryOutput .= '</div>';
                $row[] = $categoryOutput;

                $class = 'success';
                if($aRow['type'] == 'negative'){
                	$class = 'danger';
                }


                $row[] = '<span class="label label-'.$class.'">'._l($aRow['type']).'</span>';
                $row[] = $aRow['scales'];

                $checked = '';
			    if ($aRow['active'] == 1) {
			        $checked = 'checked';
			    }

                $disabled = '';
                if (!has_permission('reputation_topic', '', 'edit')) {
                    $disabled = 'disabled';
                }

			    $active = '<div class="onoffswitch">
			    <input type="checkbox" data-switch-url="' . site_url() . 'admin/reputation/change_topic_active" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . ' ' . $disabled . '>
			    <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
			    </div>';

			    $row[] = $active;

                $row[] = _dt($aRow['datecreated']);

                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    /**
     * [change_topic_active]
     * @param  [integer] $id     account id
     * @param  [string] $status 
     */
    public function change_topic_active($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->reputation_model->change_topic_active($id, $status);
        }
    }

    /**
     * { import topic }
     */
    public function topic_import()
    {
        if (!has_permission('reputation_topic', '', 'create')) {
            access_denied('reputation');
        }

        $this->load->model('staff_model');
        $data_staff = $this->staff_model->get(get_staff_user_id());

        /*get language active*/
        if ($data_staff) {
            if ($data_staff->default_language != '') {
                $data['active_language'] = $data_staff->default_language;

            } else {

                $data['active_language'] = get_option('active_language');
            }

        } else {
            $data['active_language'] = get_option('active_language');
        }
        $data['title'] = _l('import_excel');

        $this->load->view('topics/import_excel', $data);
    }

    /**
     * { import job position excel }
     */
    public function import_file_xlsx_topic()
    {
        if(!class_exists('XLSXReader_fin')){
            require_once(module_dir_path(REPUTATION_MODULE_NAME).'/assets/plugins/XLSXReader/XLSXReader.php');
        }
        require_once(module_dir_path(REPUTATION_MODULE_NAME).'/assets/plugins/XLSXWriter/xlsxwriter.class.php');


        $filename ='';
        if($this->input->post()){
            if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {

                $this->delete_error_file_day_before();

                // Get the temp file path
                $tmpFilePath = $_FILES['file_csv']['tmp_name'];                
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    $tmpDir = TEMP_FOLDER . '/' . time() . uniqid() . '/';

                    if (!file_exists(TEMP_FOLDER)) {
                        mkdir(TEMP_FOLDER, 0755);
                    }

                    if (!file_exists($tmpDir)) {
                        mkdir($tmpDir, 0755);
                    }

                    // Setup our new file path
                    $newFilePath = $tmpDir . $_FILES['file_csv']['name'];                    

                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        //Writer file
                        $writer_header = array(
                            _l('content')          =>'string',
                            _l('type')                      =>'string',
                            _l('scales')                     =>'string',
                            _l('error')                     =>'string',
                        );

                        $widths_arr = array();
                        for($i = 1; $i <= count($writer_header); $i++ ){
                            $widths_arr[] = 40;
                        }

                        $writer = new XLSXWriter();
                        $writer->writeSheetHeader('Sheet1', $writer_header,  $col_options = ['widths'=>$widths_arr ]);

                        //Reader file
                        $xlsx = new XLSXReader_fin($newFilePath);
                        $sheetNames = $xlsx->getSheetNames();
                        $data = $xlsx->getSheetData($sheetNames[1]);

                        $total_rows = 0;
                        $total_row_false    = 0; 

                        for ($row = 1; $row < count($data); $row++) {

                            $total_rows++;

                            $rd = array();
                            $flag = 0;
                            $flag2 = 0;

                            $string_error ='';

                            $value_content    = isset($data[$row][0]) ? $data[$row][0] : '' ;
                            $value_type            = isset($data[$row][1]) ? strtolower($data[$row][1]) : '';
                            $value_scales            = isset($data[$row][2]) ? $data[$row][2] : '';
                           

                            if(is_null($value_content) == true || $value_content ==''){
                                $string_error .=_l('content'). _l('not_yet_entered');
                                $flag = 1;
                            }

                            if(is_null($value_type) == true || $value_type ==''){
                                $string_error .=_l('type'). _l('not_yet_entered');
                                $flag = 1;
                            }

                            if(($flag == 1) || $flag2 == 1 ){
                                //write error file
                                $writer->writeSheetRow('Sheet1', [
                                    $value_content,
                                    $value_type,
                                    $value_scales,
                                    $string_error,
                                ]);

                                $total_row_false++;
                            }

                            if($flag == 0 && $flag2 == 0){
                                $rd['content']                = $value_content;
                                $rd['type']                         = $value_type;
                                $rd['scales']                         = $value_scales;

                                $rows[] = $rd;
                                $response = $this->reputation_model->add_topic($rd);

                            }


                        }

                        $total_rows = $total_rows;
                        $total_row_success = isset($rows) ? count($rows) : 0;
                        $dataerror = '';
                        $message ='Not enought rows for importing';

                        if($total_row_false != 0){
                            $filename = 'Import_topic_error_'.get_staff_user_id().'_'.strtotime(date('Y-m-d H:i:s')).'.xlsx';
                            $writer->writeToFile(str_replace($filename, REPUTATION_IMPORT_ITEM_ERROR.$filename, $filename));
                        }

                    }
                }
            }
        }


        if (file_exists($newFilePath)) {
            @unlink($newFilePath);
        }

        echo json_encode([
            'message'           => $message,
            'total_row_success' => $total_row_success,
            'total_row_false'   => $total_row_false,
            'total_rows'        => $total_rows,
            'site_url'          => site_url(),
            'staff_id'          => get_staff_user_id(),
            'filename'          => REPUTATION_IMPORT_ITEM_ERROR.$filename,
        ]);
    }

    /**
     * project management
     * @return view
     */
    public function projects(){
        if (!has_permission('reputation_project', '', 'view')) {
            access_denied('reputation');
        }

        $data['title']         = _l('projects');

        $this->load->view('projects/manage', $data);
    }

    /**
     * project table
     * @return json
     */
    public function project_table()
    {
        if ($this->input->is_ajax_request()) {
            $base_workspace_id = rep_get_base_workspace_id();

            $select = [
                'project_name',
                '(select count(1) from '.db_prefix().'rep_mentions where project_id = '.db_prefix() . 'rep_projects.id AND visit = 0) as new_mention',
                'active',
                'datecreated',
            ];

            $where = [];

            $type = $this->input->post("type");
            if($type != ''){
                array_push($where, 'AND type ="'.$type.'"');
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'rep_projects';
            $join         = [
            ];
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id']);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row   = [];
                $categoryOutput = $aRow['project_name'] . ($aRow['id'] == $base_workspace_id ? ' <span class="label label-success">'._l('is_default').'</span>' : '');

                $categoryOutput .= '<div class="row-options">';


                if (has_permission('reputation_project', '', 'edit')) {
                    $categoryOutput .= '<a href="' . admin_url('reputation/project/' . $aRow['id']) . '">' . _l('view') . '</a>';
                }

                if ($aRow['id'] != $base_workspace_id && $aRow['active'] == 1) {
                    $categoryOutput .= ' | <a href="javascript:void(0)" class="text-success" onclick="set_default('.$aRow['id'].'); return false;">' . _l('set_default') . '</a>';
                }

                if ((has_permission('reputation_project', '', 'delete')) && $aRow['id'] != $base_workspace_id) {
                    $categoryOutput .= ' | <a href="' . admin_url('reputation/delete_project/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }

                $categoryOutput .= '</div>';
                $row[] = $categoryOutput;
                $row[] = $aRow['new_mention'];

                $checked = '';
			    if ($aRow['active'] == 1) {
			        $checked = 'checked';
			    }

                $disabled = '';
                if (!has_permission('reputation_project', '', 'edit')) {
                    $disabled = 'disabled';
                }

			    $active = '<div class="onoffswitch">
			    <input type="checkbox" data-switch-url="' . site_url() . 'admin/reputation/change_project_active" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . ' '.$disabled.'>
			    <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
			    </div>';

			    $row[] = $active;

                $row[] = _dt($aRow['datecreated']);

                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    /**
     * project
     */
    public function project($id = '')
    {

        if ($this->input->post()) {
            $data = $this->input->post();
            $message = '';
            if ($id == '') {
                $success = $this->reputation_model->add_project($data);
                if ($success) {
                    $message = _l('added_successfully', _l('project'));
	            }
            } else {

                $success = $this->reputation_model->update_project($data, $id);
                if ($success == true) {
                    $message = _l('updated_successfully', _l('project'));
                }
            }

            if($this->input->is_ajax_request()){
                echo json_encode(['success' => $success, 'message' => $message]);
                die();
            }else{
                if ($success) {
                    set_alert('success', $message);
                }
            }
        }

        $group = !$this->input->get('group') ? 'keywords' : $this->input->get('group');

        $data['tab'] = $group;

        $project                = $this->reputation_model->get_project($id);

        if (!$project) {
            show_404();
        }

        $tabs = [
        	[
        		'key' => 'keywords',
        		'name' => _l('keywords'),
        		'icon' => 'fa fa-search',
        	],
        	[
        		'key' => 'sources',
        		'name' => _l('sources'),
        		'icon' => 'fa fa-share-alt',
        	],
        	[
        		'key' => 'notifications',
        		'name' => _l('notifications'),
        		'icon' => 'fa fa-envelope',
        	],
        ];

        $data['tabs'] = $tabs;

        $data['project'] = $project;

        $other_projects       = [];
        $other_projects_where = 'id != ' . $id;

        $data['other_projects'] = $this->reputation_model->get_project('', $other_projects_where);

        $title          = $project->project_name;

        $data['title']     = $title;

        $this->load->view('projects/project', $data);
    }

    /**
     * notification table
     * @return json
     */
    public function notification_table()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');

            $select = [
                'email',
                'frequency',
                'visited',
                'sources',
                'sentiment',
                'tags',
            ];

            $where = [];

            $type = $this->input->post("type");
            if($type != ''){
                array_push($where, 'AND type ="'.$type.'"');
            }

            $project_id = $this->input->post("project_id");
            if($project_id != ''){
                array_push($where, 'AND (project_id ="'.$project_id.'")');
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'rep_notifications';
            $join         = [
            ];
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id', 'project_id']);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row   = [];
                $categoryOutput = $aRow['email'];

                $categoryOutput .= '<div class="row-options">';

                if (has_permission('reputation_project', '', 'edit')) {
                    $categoryOutput .= '<a href="javascript:void(0);" onclick="edit_notification(' . $aRow['id'].')">' . _l('edit') . '</a>';
                }

                if (has_permission('reputation_project', '', 'delete')) {
                    $categoryOutput .= ' | <a href="' . admin_url('reputation/delete_notification/' . $aRow['id'].'/' . $aRow['project_id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }

                $categoryOutput .= '</div>';
                $row[] = $categoryOutput;

                $row[] = _l($aRow['frequency']);
                $visited = _l('all');
                if($aRow['visited'] == 1){
                    $visited = _l('only_visited');

                }elseif ($aRow['visited'] == 0) {
                    $visited = _l('only_not_visited');
                }

                $row[] = $visited;

                $sources = '';
                $sources_arr = explode(',', $aRow['sources']);
                foreach ($sources_arr as $key => $value) {
                    if($sources != ''){
                        $sources .= ', '._l($value);
                    }else{
                        $sources .= _l($value);
                    }
                }
               
                $row[] = $sources;

                $sentiment = '';
                $sentiment_arr = explode(',', $aRow['sentiment']);
                foreach ($sentiment_arr as $key => $value) {
                    if($sentiment != ''){
                        $sentiment .= ', '.$value;
                    }else{
                        $sentiment .= $value;
                    }
                }
               
                $row[] = $sentiment;

                $row[] = render_tags($aRow['tags']);

                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    /**
     * add notification
     * @return json
     */
    public function notification(){
        $data = $this->input->post();
        if($data['id'] == ''){
            if (!has_permission('reputation_project', '', 'create')) {
                access_denied('reputation');
            }
            $success = $this->reputation_model->add_notification($data);
            if($success){
                $message = _l('added_successfully');
            }else {
                $message = _l('notifications_failed');
            }
        }else{
            if (!has_permission('reputation_project', '', 'edit')) {
                access_denied('reputation');
            }
            $id = $data['id'];
            unset($data['id']);
            $success = $this->reputation_model->update_notification($data, $id);
            if ($success) {
                $message = _l('updated_successfully', _l('notifications'));
            }
        }
        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * manage settings
     */
    public function settings()
    {
    	$data['title'] = _l('setting');

        $data          = [];
        $data['group'] = $this->input->get('group');

        $data['tab'][] = 'facebook';
        $data['tab'][] = 'instagram';
        $data['tab'][] = 'twitter';
        $data['tab'][] = 'youtube';

        $data['tab_2'] = $this->input->get('tab');
        if ($data['group'] == '') {
            $data['group'] = 'facebook';
        }

        $data['title']         = _l('setting');
        $data['tabs']['view'] = 'settings/' . $data['group'];

    	$this->load->view('settings/manage', $data);
    }

    /**
     * [update_setting]
     */
    public function update_setting(){
        $data = $this->input->post();
        $type = $data['type'];
        unset($data['type']);
        $success = $this->reputation_model->update_setting($data);
        if($success == true){
            $message = _l('updated_successfully', _l('setting'));
            set_alert('success', $message);
        }
        redirect(admin_url('reputation/settings?group='.$type));
    }


    /**
     * [delete_social_account]
     * @param  [integer] $id social account id
     */
    public function delete_social_account($id)
    {
        $success = $this->reputation_model->delete_social_account($id);
        $message = '';
        if ($success) {
            $message = _l('deleted', _l('social_account'));
            set_alert('success', $message);
        } else {
            $message = _l('can_not_delete');
            set_alert('warning', $message);
        }

        redirect(admin_url('reputation/social_accounts'));
    }

    /**
     * { delete notification }
     *
     * @param      <int>  $id     The identifier
     * @param      <int>  $project_id     The project identifier
     * @return      redirect
     */
    public function delete_notification($id, $project_id){

        $response = $this->reputation_model->delete_notification($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('notification')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('notification')));
        }
        redirect(admin_url('reputation/project/'.$project_id.'?group=notifications'));
    }

    /**
     * [Add mention form]
     */
    public function add_mention_form()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                unset($data['id']);
                if (!has_permission('reputation_project', '', 'create')) {
                    access_denied('reputation');
                }

                $id = $this->reputation_model->add_mention($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('mention')));
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                if (!has_permission('reputation_project', '', 'edit')) {
                    access_denied('reputation');
                }
                $success = $this->reputation_model->update_mention($data, $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('mention')));
                }
            }
        }

        redirect(admin_url('reputation/project/' . $data['project_id'].'?group=sources'));
    }

    /**
    * get data notification
    * @param  integer $id 
    */
    public function get_data_notification($id = ''){
        $data = [];
        if($id != ''){
            $data['notification'] = $this->reputation_model->get_notification($id);
        }
        
        $this->load->view('projects/includes/notifications_modal', $data);
    }

    /**
     * mention table
     * @return json
     */
    public function mentions_table()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');

            $select = [
                'title',
                'platform',
                'sentiment',
                'link',
            ];

            $where = [];

            $type = $this->input->post("type");
            if($type != ''){
                array_push($where, 'AND (type ="'.$type.'")');
            }

            $project_id = $this->input->post("project_id");
            if($project_id != ''){
                array_push($where, 'AND (project_id ="'.$project_id.'")');
            }

            array_push($where, 'AND (add_manually = 1 AND status != "deleted")');

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'rep_mentions';
            $join         = [
            ];
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id', 'project_id']);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row   = [];
                $categoryOutput = $aRow['title'];

                $categoryOutput .= '<div class="row-options">';

                if (has_permission('reputation_mention', '', 'edit')) {
                    $categoryOutput .= '<a href="javascript:void(0)" onclick="edit_mention(' . $aRow['id'].')">' . _l('edit') . '</a>';
                }

                if (has_permission('reputation_mention', '', 'delete')) {
                    $categoryOutput .= ' | <a href="' . admin_url('reputation/delete_mention/' . $aRow['id'].'/' . $aRow['project_id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }

                $categoryOutput .= '</div>';
                $row[] = $categoryOutput;


                $row[] = _l($aRow['platform']);
                $row[] = _l($aRow['sentiment']);
                $row[] = '<a href="' . $aRow['link'] . '">' . _l('Link') . '</a>';

                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    /**
     * { delete mention }
     *
     * @param      <int>  $id     The identifier
     * @param      <int>  $project_id     The project identifier
     * @return      redirect
     */
    public function delete_mention($id, $project_id){

        $response = $this->reputation_model->delete_mention($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('mention')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('mention')));
        }
        redirect(admin_url('reputation/project/'.$project_id.'?group=sources'));
    }

    /**
    * get data mention
    * @param  integer $id 
    */
    public function get_data_mention($id){
        $data_mention = $this->reputation_model->get_mention($id);
       
        echo json_encode($data_mention);
        die;
    }

    /**
     * [social_accounts]
     */
    public function social_accounts(){
        $this->check_base_workspace();
        
        $data['type'] = $this->input->get('type');
        $data['tab'][] = 'facebook';
        $data['tab'][] = 'instagram';
        $data['tab'][] = 'twitter';
        $data['tab'][] = 'youtube';
        if ($data['type'] == '') {
            $data['type'] = 'facebook';
        }

        $data['title']         = _l('social_accounts');
        $data['connect_url'] = '';
        $data['check_config'] = 0;

        if($data['type'] == 'facebook'){
            $config = rep_get_facebook_config();
            if($config['app_id'] != '' && $config['app_secret'] != ''){
                $data['check_config'] = 1;
            }
        }elseif ($data['type'] == 'instagram') {
            $config = rep_get_instagram_config();
            if($config['app_id'] != '' && $config['app_secret'] != ''){
                $data['check_config'] = 1;
            }
        }elseif ($data['type'] == 'tiktok') {
            $config = rep_get_tiktok_config();
            if($config['client_key'] != '' && $config['client_secret'] != ''){
                $data['check_config'] = 1;
            }
        }elseif ($data['type'] == 'twitter') {
            $config = rep_get_twitter_config();
            if($config['client_id'] != '' && $config['client_secret'] != ''){
                $data['check_config'] = 1;
            }
        }elseif ($data['type'] == 'youtube') {
            $config = rep_get_youtube_config();
            if($config['client_id'] != '' && $config['client_secret'] != ''){
                $data['check_config'] = 1;
            }
        }

        $data['tabs']['view'] = 'includes/' . $data['type'];
        $this->load->view('social_accounts/manage', $data);
    }

    /**
     * [social_accounts_table]
     */
    public function social_accounts_table(){

        $this->app->get_table_data(module_views_path('reputation', 'social_accounts/table_social_accounts'), ['type' => $this->input->post('type')]);
    }

    /**
     * [social_account]
     */
    public function social_account(){
        $data = $this->input->post();
        $message = '';
        if($data['id'] == ''){
            unset($data['id']);

            $success = $this->reputation_model->add_social_account($data);
            if($success){
                $message = _l('added_successfully', _l('account'));
            }
        }else{
            $id = $data['id'];
            unset($data['id']);
            $success = $this->reputation_model->update_social_account($data, $id);
            if ($success) {
                $message = _l('updated_successfully', _l('account'));
            }
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * [check_base_workspace]
     */
    public function check_base_workspace(){
        if(rep_get_base_workspace_id() == 0){
            set_alert('warning', _l('please_set_default_project'));
            redirect(admin_url('reputation/projects'));
        }
    }

    /**
     * [set_default_project]
     * @param [integer] $project_id project id
     */
    public function set_default_project($project_id){
        $message = '';
        $success = $this->reputation_model->set_default_project($project_id);
        if ($success) {
            $message = _l('updated_successfully', _l('project'));
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * delete project
     * @param  integer $id
     * @return
     */
    public function delete_project($id)
    {
        $success = $this->reputation_model->delete_project($id);
        $message = '';
        if ($success) {
            $message = _l('deleted', _l('project'));
            set_alert('success', $message);
        } else {
            $message = _l('can_not_delete');
            set_alert('warning', $message);
        }

        redirect(admin_url('reputation/projects'));
    }

    /**
     * [change_account_active]
     * @param  [integer] $id     account id
     * @param  [string] $status 
     */
    public function change_account_active($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->reputation_model->change_account_active($id, $status);
        }
    }

    /**
     * [change_project_active]
     * @param  [integer] $id     project id
     * @param  [string] $status 
     */
    public function change_project_active($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->reputation_model->change_project_active($id, $status);
        }
    }

    /**
     * [facebook_callback]
     */
    public function facebook_callback(){
        $config = rep_get_facebook_config();
        $fb = new \Facebook\Facebook($config);
        $oAuth2Client = $fb->getOAuth2Client();
        $helper = $fb->getRedirectLoginHelper();

        try {
            $code = isset($_GET['code']) ? $_GET['code'] : null;
            $accessToken = $oAuth2Client->getAccessTokenFromCode($code, admin_url('reputation/facebook_callback'));
            $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken->getValue());
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        if (! isset($longLivedAccessToken)) {
          if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
          } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
          }
          exit;
        }

        try {
            $data['account_id'] = isset($_GET['state']) ? $_GET['state'] : null;

            $data['access_token'] = $longLivedAccessToken->getValue();
            $data['expires_in'] = time() + $longLivedAccessToken->getExpiresAt();

            $response = $fb->get('/me', $longLivedAccessToken->getValue());
            $data_account = $response->getDecodedBody();
            $data['account'] = $data_account;
            $response = $fb->get('/'.$data_account['id'].'/accounts', $longLivedAccessToken->getValue());
            $data_pages = $response->getDecodedBody();
            $data['pages'] = $data_pages;
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        $this->load->view('social_accounts/connects/facebook', $data);
    }

    /**
     * [facebook_connect]
     * @param  [integer] $account_id facebook account id
     */
    public function facebook_connect($account_id){
        $config = rep_get_facebook_config();
        $fb = new \Facebook\Facebook($config);

        $oAuth2Client = $fb->getOAuth2Client();
        $permissions = ['pages_show_list', 'pages_read_engagement', 'pages_manage_engagement', 'instagram_basic', 'instagram_manage_comments'];
        $data['connect_url'] = $oAuth2Client->getAuthorizationUrl(admin_url('reputation/facebook_callback'), $account_id, $permissions);

        redirect($data['connect_url']);
    }

    /**
     * [instagram_connect]
     * @param  [integer] $account_id instagram account id
     */
    public function instagram_connect($account_id){
        $config = rep_get_instagram_config();
        $fb = new \Facebook\Facebook($config);

        $oAuth2Client = $fb->getOAuth2Client();
        $permissions = ['pages_show_list', 'pages_read_engagement', 'pages_manage_engagement', 'instagram_basic', 'instagram_manage_comments'];
        $data['connect_url'] = $oAuth2Client->getAuthorizationUrl(admin_url('reputation/instagram_callback'), $account_id, $permissions);

        redirect($data['connect_url']);
    }
    /**
     * [instagram_callback]
     */
    public function instagram_callback(){
        $config = rep_get_instagram_config();
        $fb = new \Facebook\Facebook($config);
        $oAuth2Client = $fb->getOAuth2Client();
        $helper = $fb->getRedirectLoginHelper();

        try {
            $code = isset($_GET['code']) ? $_GET['code'] : null;
            $accessToken = $oAuth2Client->getAccessTokenFromCode($code, admin_url('reputation/instagram_callback'));
            $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken->getValue());
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        if (! isset($longLivedAccessToken)) {
          if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
          } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
          }
          exit;
        }

        try {
            $data['account_id'] = isset($_GET['state']) ? $_GET['state'] : null;

            $data['access_token'] = $longLivedAccessToken->getValue();
            $data['expires_in'] = time() + $longLivedAccessToken->getExpiresAt();

            $response = $fb->get('/me', $longLivedAccessToken->getValue());
            $data_account = $response->getDecodedBody();
            $data['account'] = $data_account;
            $response = $fb->get('/'.$data_account['id'].'/accounts', $data['access_token']);
            $data_pages = $response->getDecodedBody();
            $data['pages'] = [];

            foreach ($data_pages['data'] as $key => $value) {
                $response = $fb->get('/'.$value['id'].'?fields=instagram_business_account', $data['access_token']);
                $instagram_data = $response->getDecodedBody();

                if(isset($instagram_data['instagram_business_account']['id'])){
                    $response = $fb->get('/'.$instagram_data['instagram_business_account']['id'].'?fields=id,name,username', $data['access_token']);
                    $instagram_data = $response->getDecodedBody();

                    $data['pages'][] = $instagram_data;
                }
            }
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        $this->load->view('social_accounts/connects/instagram', $data);
    }

    /**
     * [instagram_connect_save]
     */
    public function instagram_connect_save(){
        $data = $this->input->post();
        $success = $this->reputation_model->instagram_connect_save($data);
        if ($success) {
            $message = _l('connected_successfully', _l('account'));
            set_alert('success', $message);
        }
        redirect(admin_url('reputation/social_accounts?type=instagram'));
    }


    /**
     * [tiktok_connect]
     * @param  [integer] $account_id tiktok account id
     */
    public function tiktok_connect($account_id){
        $config = rep_get_tiktok_config();

        $data['connect_url'] = 'https://www.tiktok.com/v2/auth/authorize?client_key='.$config['client_key'].'&response_type=code&scope=user.info.basic,user.info.stats&redirect_uri='.admin_url('reputation/tiktok_callback').'&state='.$account_id;

        redirect($data['connect_url']);
    }

    /**
     * [tiktok_callback]
     */
    public function tiktok_callback(){
        $config = rep_get_tiktok_config();
        $code = $this->input->get("code") ?? '';
        $account_id = $this->input->get("state") ?? '';

        if ($code != '')
        {   
            $parameters = array(
              'code' => $code,
              'grant_type' => 'authorization_code',
              'redirect_uri' => admin_url('reputation/tiktok_callback'),
              'client_key' => $config['client_key'],
              'client_secret' => $config['client_secret'],
              'code' => $code,
            );

            $http_header = array(
                 'Accept' => 'application/json',
                 'Content-Type' => 'application/x-www-form-urlencoded',
            );

            $url = 'https://open.tiktokapis.com/v2/oauth/token/';

            $token_result = $this->reputation_model->executeRequest($url,  $parameters, $http_header, 'POST');

            $data = [];
            if($token_result){
                $data['status'] = 1;
                $data['refresh_token'] = $token_result['refresh_token'];
                $data['access_token'] = $token_result['access_token'];
                $data['expires_in'] = time() + $token_result['expires_in'];
                
                $header = array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer '. $token_result['access_token']);

                $user_url = $config['api_domain'].'/user/info/?fields=open_id,union_id,avatar_url_100,display_name,follower_count,following_count,likes_count,video_count';

                $user_result = $this->reputation_model->callAPI($user_url,  [], $header, 'GET');
                $data['page_id'] = $user_result['data']['user']['union_id'];
                $data['user_id'] = $user_result['data']['user']['union_id'];
                $data['avatar_url'] = $user_result['data']['user']['avatar_url_100'];

                $this->reputation_model->account_connect_save($data, $account_id);
                $message = _l('added_successfully', _l('account'));
                set_alert('success', $message);
            }
        }

        redirect(admin_url('reputation/social_accounts?type=tiktok'));
    }

    /**
     * [twitter_connect ]
     * @param  [type] $account_id tiktok account id
     */
    public function twitter_connect($account_id){
        $config = rep_get_twitter_config();
        $data['connect_url'] = 'https://twitter.com/i/oauth2/authorize?response_type=code&client_id='.$config['client_id'].'&redirect_uri='.admin_url('reputation/twitter_callback').'&scope=offline.access%20tweet.read%20users.read%20follows.read%20space.read%20like.read%20list.read%20block.read%20bookmark.read&state='.$account_id.'&code_challenge=challenge&code_challenge_method=plain';
        redirect($data['connect_url']);
    }

    /**
     * [twitter_callback]
     */
    public function twitter_callback(){
        $config = rep_get_twitter_config();
        $code = $this->input->get("code") ?? '';
        $account_id = $this->input->get("state") ?? '';

        if ($code != '')
        {   
            $parameters = array(
              'code' => $code,
              'grant_type' => 'authorization_code',
              'redirect_uri' => admin_url('reputation/twitter_callback'),
              'client_id' => $config['client_id'],
              'code_verifier' => 'challenge'
            );

            $http_header = array(
                 'Accept' => 'application/json',
                 'Content-Type' => 'application/x-www-form-urlencoded',
                 'Authorization' => 'Basic AAAAAAAAAAAAAAAAAAAAAIeZwgEAAAAAo2R8WYXWalocy2B2X3KOnZXc03E%3DdnSYJOkF50vFkvZUI5t8cHneRyv9ZdHgKqvjmYZr5xtCHDrFRk'
            );

            $url = $config['api_domain'].'/2/oauth2/token';
            $token_result = $this->reputation_model->executeRequest($url,  $parameters, $http_header, 'POST');
            $data = [];
            if($token_result){
                $data['access_token'] = $token_result['access_token'];
                $data['expires_in'] = time() + $token_result['expires_in'];
                $data['refresh_token'] = $token_result['refresh_token'];
                
                $header = array(
                'Authorization: Bearer '. $token_result['access_token']);

                $user_url = $config['api_domain'].'/2/users/me';
                $user_result = $this->reputation_model->callAPI($user_url,  [], $header, 'GET');
                $data['page_id'] = $user_result['data']['id'];
                $data['user_id'] = $user_result['data']['id'];
                $data['status'] = 1;

                $this->reputation_model->account_connect_save($data, $account_id);
                $message = _l('added_successfully', _l('account'));
                set_alert('success', $message);
            }
        }

        redirect(admin_url('reputation/social_accounts?type=twitter'));
    }

    /**
     * [youtube_connect]
     * @param  [type] $account_id youtube account id
     */
    public function youtube_connect($account_id){
        $config = rep_get_youtube_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setRedirectUri(admin_url('reputation/youtube_callback'));
        $client->setState($account_id);
        $client->setAccessType("offline");
        $client->setPrompt("consent");
        $client->addScope("email");
        $client->addScope("profile");
        $client->addScope("https://www.googleapis.com/auth/youtube.readonly");
        $data['connect_url'] = $client->createAuthUrl();
        redirect($data['connect_url']);
    }

    /**
     * [youtube_callback]
     */
    public function youtube_callback(){
        $config = rep_get_youtube_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setRedirectUri(admin_url('reputation/youtube_callback'));
        $code = $this->input->get("code") ?? '';
        $account_id = $this->input->get("state") ?? '';

        if ($code != '')
        {   
            $token_result = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            if($token_result){
                $data['access_token'] = $token_result['access_token'];
                $data['refresh_token'] = $token_result['refresh_token'];
                $data['expires_in'] = time() + $token_result['expires_in'];
                

                $client->setAccessToken($token_result['access_token']);
                $youtube = new Google_Service_YouTube($client);
                $response = $youtube->channels->listChannels('snippet,statistics', ['mine' => true]);

                $data['channels'] = $response->getItems();
                $data['account_id'] = $account_id;
            }
        } 

        $this->load->view('social_accounts/connects/youtube', $data);
    }

    /**
     * [youtube_connect_save]
     */
    public function youtube_connect_save(){
        $data = $this->input->post();
        $success = $this->reputation_model->youtube_connect_save($data);
        if ($success) {
            $message = _l('connected_successfully', _l('account'));
            set_alert('success', $message);
        }
        redirect(admin_url('reputation/social_accounts?type=youtube'));
    }

    /**
     * [facebook_connect_save]
     */
    public function facebook_connect_save(){
        $data = $this->input->post();
        $success = $this->reputation_model->facebook_connect_save($data);
        if ($success) {
            $message = _l('connected_successfully', _l('account'));
            set_alert('success', $message);
        }
        redirect(admin_url('reputation/social_accounts'));
    }

    /**
     * [get_facebook_data description]
     * @param  [integer] $id facebook account id
     */
    public function get_facebook_data($project_id, $id){
        $message = '';
        $this->reputation_model->get_facebook_data($project_id, $id);
        
        die;
    }

    /**
     * [analysis description]
     */
    public function analysis(){
        $this->check_base_workspace();
        
        $data['from_date'] = date('Y-m-01');
        $data['to_date'] = date('Y-m-d');
        $data['title']         = _l('analysis');
        $this->load->view('analysis/manage', $data);
    }

    /**
     * [summary description]
     */
    public function summary(){
        $this->check_base_workspace();

        $data['from_date'] = date('Y-m-01');
        $data['to_date'] = date('Y-m-d');
        $data['title']         = _l('summary');
        $this->load->view('summary/manage', $data);
    }

    /**
     * [mentions description]
     */
    public function mentions(){
        $this->check_base_workspace();
        $data['from_date'] = date('Y-m-01');
        $data['to_date'] = date('Y-m-d');
       
        $data['id'] = $this->input->get('id');

        $data['title']         = _l('mentions');
        $this->load->view('mentions/manage', $data);
    }

    /**
     * [get_html_mention_list]
     * @return view
     */
    public function get_html_mention_list(){
        $data_filter = $this->input->post();

        $mentions_data = $this->reputation_model->get_mention_list($data_filter);
        $data['mention_list'] = $mentions_data['mentions'];
        $data['mention_total'] = $mentions_data['total'];
        $data['page'] = $data_filter['page'];

        $this->load->view('mentions/mention_list', $data);
    }

    /**
     * [add_to_pdf_report]
     * @param integer $mention_id
     * @return json
     */
    public function add_to_pdf_report($mention_id){
        $success = $this->reputation_model->add_to_pdf_report($mention_id);
        $message = '';

        if($success){
            $message = _l('added_successfully');
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * [remove_from_pdf_report description]
     * @param  [integer] $mention_id
     * @return [json]            
     */
    public function remove_from_pdf_report($mention_id){
        $success = $this->reputation_model->remove_from_pdf_report($mention_id);
        $message = '';

        if($success){
            $message = _l('deleted_successfully');
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * [add_mention_tag description]
     * @return [json]            
     */
    public function add_mention_tag(){
        $mention_id = $this->input->post('mention_id');
        $tags = $this->input->post('tags');

        $success = handle_tags_save($tags, $mention_id, 'rep_mention');
        $message = '';

        if($success){
            $message = _l('added_successfully');
        }

        echo json_encode(['success' => $success, 'message' => $message, 'mention_id' => $mention_id]);
        die();
    }

    /**
     * get_mention_tag_modal_ajax
     * @param  [integer] $mention_id 
     * @return [view]             
     */
    public function get_mention_tag_modal_ajax($mention_id){
        $data['mention_id'] = $mention_id;

        $this->load->view('mentions/tag_modal_html', $data);
    }

    /**
     * [delete_mention_ajax]
     * @param  [integer] $mention_id
     * @return [json]             
     */
    public function delete_mention_ajax($mention_id){
        $success = $this->reputation_model->delete_mention($mention_id);
        $message = '';

        if($success){
            $message = _l('deleted_successfully');
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }
    
    /**
     * mention sentiment mark as
     * @param  [type] $sentiment 
     * @param  [type] $id     
     * @return json        
     */
    public function mention_sentiment_mark_as($sentiment, $id)
    {
        $success = $this->reputation_model->mention_sentiment_mark_as($sentiment, $id);
        $message = '';

        if ($success) {
            $message = _l('rep_change_sentiment_successfully');
        }
        echo json_encode([
            'success'  => $success,
            'message'  => $message
        ]);
        die;
    }

    /**
     * get_html_mention
     * @param  [integer] $mention_id 
     * @return [view]             
     */
    public function get_html_mention($mention_id){
        $data_filter = $this->input->post();

        $data['mention'] = (array)$this->reputation_model->get_mention($mention_id);

        $this->load->view('mentions/mention_detail', $data);
    }

    /**
     * visit_mention
     * @param  [integer] $mention_id 
     * @return [json]             
     */
    public function visit_mention($mention_id)
    {
        $success = $this->reputation_model->visit_mention($mention_id);

        echo json_encode([
            'success'  => $success,
        ]);
        die;
    }

    /**
     * get data dashboard
     * @return json
     */
    public function get_data_analytics(){
        $data_filter = $this->input->post();
        $data = [];
        switch ($data_filter['type']) {
            case 'mentions_reach_chart':
                $data['mentions_reach_chart'] = $this->reputation_model->get_data_mentions_reach_chart($data_filter);
                echo json_encode($data);
                break;
            case 'sentiment_chart':
                $data['sentiment_chart'] = $this->reputation_model->get_data_sentiment_chart($data_filter);
                echo json_encode($data);
                break;
            case 'mentions_chart':
                $data['mentions_chart'] = $this->reputation_model->get_data_mentions_chart($data_filter);
                echo json_encode($data);
                break;
            case 'social_media_reach_chart':
                $data['social_media_reach_chart'] = $this->reputation_model->get_data_social_media_reach_chart($data_filter);
                echo json_encode($data);
                break;
            case 'summary_top_stats':
                $where = $this->reputation_model->get_where_report($data_filter);
              
                $data_filter['where'] = $where;

                $this->load->view('summary/top_stats', $data_filter);
                break;
            case 'summary_stats':
                $where = $this->reputation_model->get_where_report($data_filter);
              
                $data_filter['where'] = $where;

                $this->load->view('summary/summary_stats', $data_filter);
                break;
            case 'summary_sources':
                $where = $this->reputation_model->get_where_report($data_filter);
              
                $data_filter['where'] = $where;

                $this->load->view('summary/summary_sources', $data_filter);
                break;
            case 'the_most_influential_sites':
                $where = $this->reputation_model->get_where_report($data_filter);
              
                $data_filter['where'] = $where;

                $this->load->view('summary/the_most_influential_sites', $data_filter);
                break;
            case 'analysis_top_stats':
                $where = $this->reputation_model->get_where_report($data_filter);
              
                $data_filter['where'] = $where;

                $this->load->view('analysis/top_stats', $data_filter);
                break;
            case 'the_most_popular_mentions':
                $data_filter['mentions'] = $this->reputation_model->get_the_most_popular_mentions($data_filter);
                $this->load->view('analysis/the_most_popular_mentions', $data_filter);
                break;

            case 'mentions_by_category':
                $data_mentions_by_category = $this->reputation_model->get_data_mentions_by_category($data_filter);
                $data['content_html'] =  $this->load->view('analysis/mentions_by_category', $data_mentions_by_category['data_summary'], true);
                $data['mentions_by_category_chart'] = $data_mentions_by_category['data_chart'];
                
                echo json_encode($data);
                break;

            case 'summary_tag_stats':
                $data['tag_stats'] = $this->reputation_model->get_tag_stats_data($data_filter);

                $this->load->view('summary/tag_stats', $data);
                break;

            case 'summary_keyword_stats':
                $data['keyword_stats'] = $this->reputation_model->get_keyword_stats_data($data_filter);

                $this->load->view('summary/keyword_stats', $data);
                break;
                
            default:
                // code...
                break;
        }

    }

    /**
     * case management
     * @return view
     */
    public function case_management(){
        if (!has_permission('reputation_case', '', 'view')) {
            access_denied('reputation');
        }

        $data['title']         = _l('case_management');

        $this->load->view('cases/manage', $data);
    }

    /**
     * add case
     * @return json
     */
    public function case(){
        $data = $this->input->post();
        if($data['id'] == ''){
            if (!has_permission('reputation_case', '', 'create')) {
                access_denied('reputation');
            }
            $success = $this->reputation_model->add_case($data);
            if($success){
                $message = _l('added_successfully');
            }else {
                $message = _l('cases_failed');
            }
        }else{
            if (!has_permission('reputation_case', '', 'edit')) {
                access_denied('reputation');
            }
            $id = $data['id'];
            unset($data['id']);
            $success = $this->reputation_model->update_case($data, $id);
            if ($success) {
                $message = _l('updated_successfully', _l('cases'));
            }
        }
        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * case table
     * @return json
     */
    public function case_table()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('currencies_model');

            $select = [
                'name',
                'description',
                'addedfrom',
                'datecreated',
                'active',
            ];

            $where = [];

            $type = $this->input->post("type");
            if($type != ''){
                array_push($where, 'AND type ="'.$type.'"');
            }

            $aColumns     = $select;
            $sIndexColumn = 'id';
            $sTable       = db_prefix() . 'rep_cases';
            $join         = [
            ];
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id']);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            foreach ($rResult as $aRow) {
                $row   = [];
                $categoryOutput = $aRow['name'];

                $categoryOutput .= '<div class="row-options">';

                $categoryOutput .= '<a href="'.admin_url('reputation/case_detail/'.$aRow['id']).'">' . _l('view') . '</a>';

                if (has_permission('reputation_case', '', 'edit')) {
                    $categoryOutput .= ' | <a href="javascript:void(0)" onclick="edit_case(' . $aRow['id'] . '); return false;">' . _l('edit') . '</a>';
                }

                if (has_permission('reputation_case', '', 'delete')) {
                    $categoryOutput .= ' | <a href="' . admin_url('reputation/delete_case/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }

                $categoryOutput .= '</div>';
                $row[] = $categoryOutput;

                $row[] = ($aRow['description']);
                $row[] = get_staff_full_name($aRow['addedfrom']);

                $row[] = _dt($aRow['datecreated']);

                $checked = '';
                if ($aRow['active'] == 1) {
                    $checked = 'checked';
                }

                $disabled = '';
                if (!has_permission('reputation_case', '', 'edit')) {
                    $disabled = 'disabled';
                }

                $active = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . site_url() . 'admin/reputation/change_case_active" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . ' ' . $disabled . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
                </div>';

                $row[] = $active;

                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    /**
     * [change_case_active]
     * @param  [integer] $id     account id
     * @param  [string] $status 
     */
    public function change_case_active($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->reputation_model->change_case_active($id, $status);
        }
    }

    /**
     * case_detail
     * @param  [integer] $id 
     * @return [view]     
     */
    public function case_detail($id)
    {
        if (!has_permission('reputation_case', '', 'view')) {
            access_denied('reputation');
        }
        $data['case']         = $this->reputation_model->get_case($id);
        $data['topics']         = $this->reputation_model->get_topic('', ['active' => 1]);
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);

        $data['title']         = _l('case_management');

        $this->load->view('cases/case_detail', $data);
    }

    /**
     * { delete case }
     *
     * @param      <type>  $id     The identifier
     * @return      redirect
     */
    public function delete_case($id){

        if (!$id) {
            redirect(admin_url('reputation/cases'));
        }
        $response = $this->reputation_model->delete_case($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('case')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('case_lowercase')));
        }
        redirect(admin_url('reputation/case_management'));
    }

    /**
     * delete error file day before
     * @param  string $before_day  
     * @param  string $folder_name 
     * @return boolean              
     */
    public function delete_error_file_day_before($before_day ='', $folder_name='')
    {
        if($before_day != ''){
            $day = $before_day;
        }else{
            $day = '7';
        }

        if($folder_name != ''){
            $folder = $folder_name;
        }else{
            $folder = REPUTATION_IMPORT_ITEM_ERROR;
        }

        //Delete old file before 7 day
        $date = date_create(date('Y-m-d H:i:s'));
        date_sub($date,date_interval_create_from_date_string($day." days"));
        $before_7_day = strtotime(date_format($date,"Y-m-d H:i:s"));

        foreach(glob($folder . '*') as $file) {

            $file_arr = explode("/",$file);
            $filename = array_pop($file_arr);

            if(file_exists($file)) {
                //don't delete index.html file
                if($filename != 'index.html'){
                    $file_name_arr = explode("_",$filename);
                    $date_create_file = array_pop($file_name_arr);
                    $date_create_file =  str_replace('.xlsx', '', $date_create_file);

                    if((float)$date_create_file <= (float)$before_7_day){
                        unlink($folder.$filename);
                    }
                }
            }
        }
        return true;
    }

    /**
     * { delete topic }
     *
     * @param      <int>  $id     The identifier
     * @param      <int>  $project_id     The project identifier
     * @return      redirect
     */
    public function delete_topic($id){

        $response = $this->reputation_model->delete_topic($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('topic')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('topic')));
        }
        redirect(admin_url('reputation/topic_management'));
    }

    /**
    * get data topic
    * @param  integer $id 
    */
    public function get_data_topic($id){
        $data_topic = $this->reputation_model->get_topic($id);
       
        echo json_encode($data_topic);
        die;
    }

    /**
    * get data case
    * @param  integer $id 
    */
    public function get_data_case($id){
        $data_case = $this->reputation_model->get_case($id);
       
        echo json_encode($data_case);
        die;
    }

    /**
     * get_instagram_data
     * @param  [integer] $project_id
     * @param  [integer] $id        
     */
    public function get_instagram_data($project_id, $id){
        $this->reputation_model->get_instagram_data($project_id, $id);
        die;
    }

    /**
     * get_youtube_data
     * @param  [integer] $project_id
     * @param  [integer] $id        
     */
    public function get_youtube_data($project_id, $id){
        $this->reputation_model->get_youtube_data($project_id, $id);
       
        die;
    }

    /**
     * get_twitter_data
     * @param  [integer] $project_id
     * @param  [integer] $id        
     */
    public function get_twitter_data($project_id, $id){
        $this->reputation_model->get_twitter_data($project_id, $id);
       
        die;
    }

    /**
     * get_google_news
     * @param  [integer] $project_id
     */
    public function get_google_news($project_id){
        $this->reputation_model->get_google_news($project_id);

        die;
    }

    /**
     * pdf_reports
     */
    public function pdf_reports(){
        $this->check_base_workspace();

        $data['from_date'] = date('Y-m-01');
        $data['to_date'] = date('Y-m-d');
        $data['title']         = _l('pdf_reports');
        $this->load->view('pdf_reports/manage', $data);
    }

    /**
     * create_pdf_reports
     * @return [view] 
     */
    public function create_pdf_reports(){

        $data_filter = $this->input->post();
        $data_filter['from_date'] = date('Y-m-01');
        $data_filter['to_date'] = date('Y-m-d');
        $data['title']         = _l('pdf_reports');
        
        $where = $this->reputation_model->get_where_report($data_filter);
        $data['where'] = $where;

        $this->load->view('pdf_reports/pdf_report', $data);
    }

    /**
     * get_html_mention_list_pdf
     * @return [view] 
     */
    public function get_html_mention_list_pdf(){
        $data_filter = $this->input->post();

        $mentions_data = $this->reputation_model->get_mention_list($data_filter);
        $data['mention_list'] = $mentions_data['mentions'];

        $this->load->view('pdf_reports/mention_list', $data);
    }

    /**
     * rep_cron_send_notifications
     * @param  [integer] $project_id 
     */
    public function rep_cron_send_notifications($project_id){
        $this->reputation_model->rep_cron_send_notifications($project_id);
        die;
    }
    
    /**
     * { vendor contacts }
     *
     * @param      <type>  $client_id  The client identifier
     */
    public function vendor_contacts($client_id)
    {
        $this->app->get_table_data(module_views_path('reputation', 'vendors/table_contacts'), [
            'client_id' => $client_id,
        ]);
    }

    /**
     * { delete vendor contact }
     *
     * @param      string  $customer_id  The customer identifier
     * @param      <type>  $id           The identifier
     * @return     redirect
     */
    public function delete_vendor_contact($customer_id, $id)
    {
        if (!has_permission('reputation_vendor', '', 'delete')) {
            if (!is_customer_admin($customer_id)) {
                access_denied('vendors');
            }
        }

        $this->reputation_model->delete_contact($id);
        
        redirect(admin_url('reputation/vendor/' . $customer_id . '?group=contacts'));
    }


    /**
     * Determines if contact email exists.
     */
    public function contact_email_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if the email is the same
                $userid = $this->input->post('userid');
                if ($userid != '') {
                    $this->db->where('id', $userid);
                    $_current_email = $this->db->get(db_prefix() . 'pur_contacts')->row();
                    if ($_current_email->email == $this->input->post('email')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('email', $this->input->post('email'));
                $total_rows = $this->db->count_all_results(db_prefix() . 'pur_contacts');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    /**
     * { form contact }
     *
     * @param      <type>  $customer_id  The customer identifier
     * @param      string  $contact_id   The contact identifier
     */
    public function form_contact($customer_id, $contact_id = '')
    {
        if (!has_permission('reputation_vendor', '', 'view') && !has_permission('reputation_vendor', '', 'view_own')) {
            if (!rep_is_vendor_admin($customer_id)) {
                echo _l('access_denied');
                die;
            }
        }
        $data['customer_id'] = $customer_id;
        $data['contactid']   = $contact_id;
        if ($this->input->post()) {
            $data             = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            unset($data['contactid']);
            if ($contact_id == '') {
                if (!has_permission('reputation_vendor', '', 'create')) {
                    if (!rep_is_vendor_admin($customer_id)) {
                        header('HTTP/1.0 400 Bad error');
                        echo json_encode([
                            'success' => false,
                            'message' => _l('access_denied'),
                        ]);
                        die;
                    }
                }
                $id      = $this->reputation_model->add_contact($data, $customer_id);
                $message = '';
                $success = false;
                if ($id) {
                   
                    $success = true;
                    $message = _l('added_successfully', _l('contact'));
                }
                echo json_encode([
                    'success'             => $success,
                    'message'             => $message,
                    'has_primary_contact' => (total_rows(db_prefix().'pur_contacts', ['userid' => $customer_id, 'is_primary' => 1]) > 0 ? true : false),
                    'is_individual'       => rep_is_empty_vendor_company($customer_id) && total_rows(db_prefix().'pur_contacts', ['userid' => $customer_id]) == 1,
                ]);
                die;
            }
            if (!has_permission('reputation_vendor', '', 'edit')) {
                if (!rep_is_vendor_admin($customer_id)) {
                    header('HTTP/1.0 400 Bad error');
                    echo json_encode([
                            'success' => false,
                            'message' => _l('access_denied'),
                        ]);
                    die;
                }
            }
            $original_contact = $this->reputation_model->get_contact($contact_id);
            $success          = $this->reputation_model->update_contact($data, $contact_id);
            $message          = '';
            $proposal_warning = false;
            $original_email   = '';
            $updated          = false;
            if (is_array($success)) {
                if (isset($success['set_password_email_sent'])) {
                    $message = _l('set_password_email_sent_to_client');
                } elseif (isset($success['set_password_email_sent_and_profile_updated'])) {
                    $updated = true;
                    $message = _l('set_password_email_sent_to_client_and_profile_updated');
                }
            } else {
                if ($success == true) {
                    $updated = true;
                    $message = _l('updated_successfully', _l('contact'));
                }
            }
       
            echo json_encode([
                    'success'             => $success,
                    'proposal_warning'    => $proposal_warning,
                    'message'             => $message,
                    'original_email'      => $original_email,
                    'has_primary_contact' => (total_rows(db_prefix().'pur_contacts', ['userid' => $customer_id, 'is_primary' => 1]) > 0 ? true : false),
                ]);
            die;
        }
        if ($contact_id == '') {
            $title = _l('add_new', _l('contact_lowercase'));
        } else {
            $data['contact'] = $this->reputation_model->get_contact($contact_id);

            if (!$data['contact']) {
                header('HTTP/1.0 400 Bad error');
                echo json_encode([
                    'success' => false,
                    'message' => 'Contact Not Found',
                ]);
                die;
            }
            $title = $data['contact']->firstname . ' ' . $data['contact']->lastname;
        }

        
        $data['title']                = $title;
        $this->load->view('vendors/modals/contact', $data);
    }

    /* Change client status / active / inactive */
    public function change_contact_status($id, $status)
    {
        if (has_permission('reputation_vendor', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->reputation_model->change_contact_status($id, $status);
            }
        }
    }
} 