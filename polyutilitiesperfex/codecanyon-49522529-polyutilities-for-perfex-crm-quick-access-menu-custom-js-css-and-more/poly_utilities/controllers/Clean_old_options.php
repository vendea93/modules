<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Clean Old Options Controller
 * Delete old JSON-based menu options
 * 
 * URL: admin/poly_utilities/clean_old_options
 */
class Clean_old_options extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Main cleanup method
     */
    public function index()
    {
        if (!is_admin()) {
            access_denied('Clean Old Options');
        }
        
        $results = $this->delete_old_menu_options();
        
        // Display results
        $data['title'] = 'Clean Old Menu Options';
        $data['results'] = $results;
        
        $this->load->view('admin/clean_old_options', $data);
    }
    
    /**
     * AJAX cleanup
     */
    public function ajax_clean()
    {
        if (!is_admin()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        
        $results = $this->delete_old_menu_options();
        
        echo json_encode([
            'success' => true,
            'message' => 'Old options cleaned successfully',
            'results' => $results
        ]);
        exit;
    }
    
    /**
     * Delete old menu options
     */
    private function delete_old_menu_options()
    {
        $results = [
            'deleted' => [],
            'not_found' => [],
            'errors' => []
        ];
        
        // List of old option keys to delete
        $old_options = [
            'poly_utilities_global_menu_sidebar_custom',
            'poly_utilities_global_menu_sidebar_custom_active',
            'poly_utilities_global_menu_setup_custom',
            'poly_utilities_global_menu_setup_custom_active',
            'poly_utilities_global_menu_clients_custom',
            'poly_utilities_global_menu_clients_custom_active',
        ];
        
        foreach ($old_options as $option_name) {
            try {
                // Check if option exists
                $exists = $this->db->where('name', $option_name)
                                  ->count_all_results(db_prefix() . 'options') > 0;
                
                if ($exists) {
                    // Delete from database
                    $this->db->where('name', $option_name)
                           ->delete(db_prefix() . 'options');
                    
                    $results['deleted'][] = $option_name;
                } else {
                    $results['not_found'][] = $option_name;
                }
            } catch (Exception $e) {
                $results['errors'][] = [
                    'option' => $option_name,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}

