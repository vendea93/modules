<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['app']);
    }

    public function get_settings()
    {
        // Get theme settings from database
        return [
            'primary_color' => '#2563eb',
            'accent_color' => '#10b981',
            'font_family' => 'DM Sans'
        ];
    }

    public function save_settings($data)
    {
        // Save theme settings to database
        return true;
    }
}