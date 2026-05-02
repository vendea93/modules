<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Flowquest_office_theme
{
    protected $perfex;
    
    public function __construct()
    {
        $this->perfex =& get_instance();
        
        // Add our custom CSS and JS files
        $this->perfex->hooks('head_component_options')['head_tag'] = '
            <link rel="stylesheet" type="text/css" href="' . module_dir_url('flowquest_office_theme', 'assets/css/flowquest-integration.css') . '">
            <link rel="stylesheet" type="text/css" href="' . module_dir_url('flowquest_office_theme', 'assets/css/theme_styles.css') . '">
            <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap">
        ';
        
        // Add body class for our theme
        $this->perfex->hooks('body_class_options')['body_class'] = 'flowquest-theme';
    }
    
    public function pre_head()
    {
        // Add custom CSS variables in head
        echo "<style>
            :root {
                --fq-primary: #2563eb;
                --fq-primary-hover: #3b82f6;
                --fq-accent: #10b981;
                --fq-success: #10b981;
                --fq-danger: #ef4444;
                --fq-warning: #f59e0b;
                --fq-info: #0ea5e9;
                --fq-text: #0f172a;
                --fq-text-muted: #64748b;
                --fq-text-light: #94a3b8;
                --fq-bg: #f8fafc;
                --fq-surface: #ffffff;
                --fq-card-bg: #ffffff;
                --fq-border: #e2e8f0;
                --fq-border-light: #f1f5f9;
                --fq-nav-bg: rgba(255, 255, 255, 0.8);
                --fq-sidebar-bg: #ffffff;
                --fq-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                --fq-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                --fq-radius: 0.5rem;
                --fq-radius-sm: 0.25rem;
                --fq-radius-lg: 0.75rem;
                --fq-font: 'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            }
            
            /* Dark theme variables */
            @media (prefers-color-scheme: dark) {
                :root {
                    --fq-text: #f1f5f9;
                    --fq-text-muted: #94a3b8;
                    --fq-text-light: #64748b;
                    --fq-bg: #0f172a;
                    --fq-surface: #1e293b;
                    --fq-card-bg: #1e293b;
                    --fq-border: #334155;
                    --fq-border-light: #1e293b;
                    --fq-nav-bg: rgba(30, 41, 59, 0.8);
                    --fq-sidebar-bg: #0f172a;
                }
            }
        </style>";
    }
    
    public function pre_footer()
    {
        // Add our custom JavaScript
        echo '<script src="' . module_dir_url('flowquest_office_theme', 'assets/js/flowquest-theme.js') . '"></script>';
    }
}