<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FormSync Rate Limiter Library
 * 
 * Provides rate limiting functionality for webhook endpoints to prevent abuse.
 * Uses database-based tracking with automatic cleanup of old records.
 * 
 * @package    FormSync
 * @subpackage Libraries
 * @category   Module
 * @author     LiquidApps Studio
 */
class Form_sync_rate_limiter
{
    /**
     * CodeIgniter instance
     * 
     * @var object
     */
    private $ci;

    /**
     * Rate limit configuration
     * 
     * @var array
     */
    private $config = [
        'max_requests' => 100,      // Maximum requests per time window
        'time_window' => 3600,       // Time window in seconds (1 hour)
        'cleanup_interval' => 86400,  // Cleanup old records every 24 hours
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ci = &get_instance();
        
        // Load config from options if available
        $max_requests = get_option('form_sync_rate_limit_max_requests');
        if ($max_requests !== false && is_numeric($max_requests)) {
            $this->config['max_requests'] = (int)$max_requests;
        }
        
        $time_window = get_option('form_sync_rate_limit_time_window');
        if ($time_window !== false && is_numeric($time_window)) {
            $this->config['time_window'] = (int)$time_window;
        }
        
        // Ensure table exists
        $this->ensureTableExists();
    }

    /**
     * Check if request is within rate limit
     * 
     * @param string $identifier Unique identifier (IP address, form_id, etc.)
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
     */
    public function checkLimit($identifier)
    {
        // Sanitize identifier
        $identifier = $this->sanitizeIdentifier($identifier);
        
        if (empty($identifier)) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => time() + $this->config['time_window'],
                'message' => 'Invalid identifier'
            ];
        }

        // Cleanup old records periodically
        $this->cleanupOldRecords();

        // Get current request count
        $window_start = time() - $this->config['time_window'];
        
        $this->ci->db->where('identifier', $identifier);
        $this->ci->db->where('timestamp >', date('Y-m-d H:i:s', $window_start));
        $count = $this->ci->db->count_all_results(db_prefix() . 'form_sync_rate_limits');

        // Check if limit exceeded
        $allowed = $count < $this->config['max_requests'];
        $remaining = max(0, $this->config['max_requests'] - $count);
        $reset_time = time() + $this->config['time_window'];

        // Record this request if allowed
        if ($allowed) {
            $this->recordRequest($identifier);
        } else {
            log_message('warning', '[FormSync] Rate limit exceeded for identifier: ' . $identifier . ' (count: ' . $count . ', limit: ' . $this->config['max_requests'] . ')');
        }

        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'reset_time' => $reset_time,
            'limit' => $this->config['max_requests'],
            'count' => $count
        ];
    }

    /**
     * Record a request
     * 
     * @param string $identifier Unique identifier
     * @return void
     */
    private function recordRequest($identifier)
    {
        $this->ci->db->insert(db_prefix() . 'form_sync_rate_limits', [
            'identifier' => $identifier,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $this->getClientIp()
        ]);
    }

    /**
     * Cleanup old rate limit records
     * 
     * @return void
     */
    private function cleanupOldRecords()
    {
        // Only cleanup periodically to avoid overhead
        $last_cleanup = get_option('form_sync_rate_limit_last_cleanup');
        $current_time = time();
        
        if ($last_cleanup === false || ($current_time - (int)$last_cleanup) > $this->config['cleanup_interval']) {
            $cutoff_time = date('Y-m-d H:i:s', time() - ($this->config['time_window'] * 2));
            
            $this->ci->db->where('timestamp <', $cutoff_time);
            $this->ci->db->delete(db_prefix() . 'form_sync_rate_limits');
            
            update_option('form_sync_rate_limit_last_cleanup', $current_time);
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Rate limit cleanup completed');
            }
        }
    }

    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    private function getClientIp()
    {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_REAL_IP',         // Nginx proxy
            'HTTP_X_FORWARDED_FOR',   // Proxy
            'REMOTE_ADDR'             // Standard
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Sanitize identifier
     * 
     * @param string $identifier Raw identifier
     * @return string Sanitized identifier
     */
    private function sanitizeIdentifier($identifier)
    {
        // Remove any potentially dangerous characters
        $identifier = preg_replace('/[^a-zA-Z0-9._-]/', '', $identifier);
        // Limit length
        return substr($identifier, 0, 255);
    }

    /**
     * Ensure rate limits table exists
     * 
     * @return void
     */
    private function ensureTableExists()
    {
        if (!$this->ci->db->table_exists(db_prefix() . 'form_sync_rate_limits')) {
            $this->ci->db->query('CREATE TABLE `' . db_prefix() . "form_sync_rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `identifier` (`identifier`),
  KEY `timestamp` (`timestamp`),
  KEY `identifier_timestamp` (`identifier`, `timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $this->ci->db->char_set . ';');
            
            log_message('info', '[FormSync] Rate limits table created');
        }
    }

    /**
     * Reset rate limit for an identifier
     * 
     * @param string $identifier Unique identifier
     * @return bool Success
     */
    public function resetLimit($identifier)
    {
        $identifier = $this->sanitizeIdentifier($identifier);
        
        if (empty($identifier)) {
            return false;
        }

        $this->ci->db->where('identifier', $identifier);
        return $this->ci->db->delete(db_prefix() . 'form_sync_rate_limits');
    }
}









