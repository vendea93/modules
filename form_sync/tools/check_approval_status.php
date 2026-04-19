<?php
/**
 * Check Approval Status
 * 
 * This script checks the database to see if approvals are being processed
 * and if leads are being created.
 */

// Bootstrap CodeIgniter
define('BASEPATH', true);
require_once(__DIR__ . '/../../index.php');

$CI = &get_instance();
$CI->load->database();

echo "FormSync Approval Status Check\n";
echo "==============================\n\n";

// Check recent approvals
$CI->db->where('hold_status', 'approved');
$CI->db->order_by('datecreated', 'DESC');
$CI->db->limit(10);
$approved = $CI->db->get(db_prefix() . 'form_sync_submission_logs')->result_array();

echo "Recent Approved Submissions:\n";
echo "----------------------------\n";
if (count($approved) > 0) {
    foreach ($approved as $log) {
        echo "Log ID: {$log['id']}\n";
        echo "  Form ID: {$log['form_id']}\n";
        echo "  Target Type: {$log['target_type']}\n";
        echo "  Status: {$log['status']}\n";
        echo "  Lead ID: " . ($log['lead_id'] ?? 'NULL') . "\n";
        echo "  Customer ID: " . ($log['customer_id'] ?? 'NULL') . "\n";
        echo "  Date: {$log['datecreated']}\n";
        
        // Check if lead exists
        if ($log['target_type'] === 'lead' && !empty($log['lead_id'])) {
            $CI->db->where('id', $log['lead_id']);
            $lead = $CI->db->get(db_prefix() . 'leads')->row();
            if ($lead) {
                echo "  ✓ Lead exists in database\n";
                echo "    Name: {$lead->name}\n";
                echo "    Email: " . ($lead->email ?? 'N/A') . "\n";
                echo "    addedfrom: " . ($lead->addedfrom ?? 'NULL') . "\n";
                echo "    is_public: " . ($lead->is_public ?? 'NULL') . "\n";
                echo "    status: " . ($lead->status ?? 'NULL') . "\n";
            } else {
                echo "  ✗ Lead NOT found in database (ID: {$log['lead_id']})\n";
            }
        }
        echo "\n";
    }
} else {
    echo "No approved submissions found.\n\n";
}

// Check pending submissions
$CI->db->where('hold_status', 'hold');
$CI->db->order_by('datecreated', 'DESC');
$CI->db->limit(5);
$pending = $CI->db->get(db_prefix() . 'form_sync_submission_logs')->result_array();

echo "Pending Submissions (most recent 5):\n";
echo "-------------------------------------\n";
if (count($pending) > 0) {
    foreach ($pending as $log) {
        echo "Log ID: {$log['id']} - Form: {$log['form_id']} - Target: {$log['target_type']} - Date: {$log['datecreated']}\n";
    }
} else {
    echo "No pending submissions.\n";
}

echo "\n";











