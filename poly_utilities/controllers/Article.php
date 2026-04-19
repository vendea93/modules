<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Article extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper("security");
    }

    public function details($slug)
    {
        if (!$slug) {
            show_404();
        }

        // Load menu from database
        $CI = &get_instance();
        $CI->load->model('poly_utilities/custom_menu_model');
        $menu = $CI->custom_menu_model->get_by_slug($slug, 'clients');
        
        if (!$menu) {
            show_404();
        }
        
        // Check require_login from database (integer 0 or 1)
        if (isset($menu['require_login']) && $menu['require_login'] == 1 && !is_client_logged_in()) {
            show_404();
        }
        
        // Use menu from database
        $object = $menu;

        if (!empty($object['clients']) && $object['clients'] != '[]') {
            $current_client_id = get_client_user_id();
            $clients = poly_utilities_common_helper::json_decode($object['clients'], true);
            $client_can_access = poly_utilities_common_helper::get_item_by($clients, 'id', $current_client_id);

            if (!$client_can_access)
                show_404();
        }

        $data['custom_menu'] = $object;
        $data['title'] = $object['name'];
        $this->data($data);
        $this->view('custom_menu/details_clients');
        $this->layout();
    }
}
