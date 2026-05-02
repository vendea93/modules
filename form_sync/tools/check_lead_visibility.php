<?php
/**
 * Diagnostic Script: Check Lead Visibility
 * 
 * This script checks if leads created by FormSync are visible in the leads table
 * by querying the database directly and checking visibility settings.
 * 
 * Usage: php modules/form_sync/tools/check_lead_visibility.php [lead_id]
 */

// Bootstrap CodeIgniter
define('BASEPATH', true);
require_once(__DIR__ . '/../../index.php');

$lead_id = isset($argv[1]) ? (int)$argv[1] : null;

echo "FormSync Lead Visibility Diagnostic\n";
echo "====================================\n\n";

// Get database connection
$CI =& get_instance();
$CI->load->database();

if ($lead_id) {
    // Check specific lead
    echo "Checking Lead ID: {$lead_id}\n";
    echo "----------------------------------------\n";
    
    $CI->db->where('id', $lead_id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();
    
    if (!$lead) {
        echo "❌ Lead ID {$lead_id} NOT FOUND in database\n";
        exit(1);
    }
    
    echo "✓ Lead found in database\n\n";
    echo "Lead Details:\n";
    echo "  ID: {$lead->id}\n";
    echo "  Name: " . ($lead->name ?? 'N/A') . "\n";
    echo "  Email: " . ($lead->email ?? 'N/A') . "\n";
    echo "  addedfrom: " . ($lead->addedfrom ?? 'NULL') . "\n";
    echo "  is_public: " . ($lead->is_public ?? 'NULL') . "\n";
    echo "  status: " . ($lead->status ?? 'NULL') . "\n";
    echo "  assigned: " . ($lead->assigned ?? 'NULL') . "\n";
    echo "  junk: " . ($lead->junk ?? '0') . "\n";
    echo "  lost: " . ($lead->lost ?? '0') . "\n\n";
    
    // Check visibility
    $current_user_id = get_staff_user_id();
    $has_view_permission = staff_can('view', 'leads');
    
    echo "Visibility Check:\n";
    echo "  Current User ID: {$current_user_id}\n";
    echo "  Has 'view' permission: " . ($has_view_permission ? 'YES' : 'NO') . "\n\n";
    
    if (!$has_view_permission) {
        $visible = false;
        $reasons = [];
        
        if ($lead->assigned == $current_user_id) {
            $visible = true;
            $reasons[] = "Assigned to current user";
        }
        if ($lead->addedfrom == $current_user_id) {
            $visible = true;
            $reasons[] = "Created by current user";
        }
        if (isset($lead->is_public) && $lead->is_public == 1) {
            $visible = true;
            $reasons[] = "is_public = 1";
        }
        
        echo "  Visibility: " . ($visible ? "✓ VISIBLE" : "✗ HIDDEN") . "\n";
        if ($visible) {
            echo "  Reasons: " . implode(', ', $reasons) . "\n";
        } else {
            echo "  ✗ Lead does NOT meet visibility criteria:\n";
            echo "    - assigned ({$lead->assigned}) != user ({$current_user_id})\n";
            echo "    - addedfrom ({$lead->addedfrom}) != user ({$current_user_id})\n";
            echo "    - is_public ({$lead->is_public}) != 1\n";
        }
    } else {
        echo "  ✓ User has full 'view' permission - all leads should be visible\n";
    }
    
} else {
    // Check all FormSync-created leads (addedfrom = 0)
    echo "Checking all FormSync-created leads (addedfrom = 0)\n";
    echo "----------------------------------------\n\n";
    
    $CI->db->where('addedfrom', 0);
    $leads = $CI->db->get(db_prefix() . 'leads')->result();
    
    echo "Found " . count($leads) . " leads with addedfrom = 0\n\n";
    
    if (count($leads) > 0) {
        $current_user_id = get_staff_user_id();
        $has_view_permission = staff_can('view', 'leads');
        
        echo "Current User ID: {$current_user_id}\n";
        echo "Has 'view' permission: " . ($has_view_permission ? 'YES' : 'NO') . "\n\n";
        
        foreach ($leads as $lead) {
            echo "Lead ID: {$lead->id}\n";
            echo "  Name: " . ($lead->name ?? 'N/A') . "\n";
            echo "  Email: " . ($lead->email ?? 'N/A') . "\n";
            echo "  addedfrom: {$lead->addedfrom}\n";
            echo "  is_public: " . ($lead->is_public ?? 'NULL') . "\n";
            echo "  status: " . ($lead->status ?? 'NULL') . "\n";
            echo "  assigned: " . ($lead->assigned ?? 'NULL') . "\n";
            
            if (!$has_view_permission) {
                $visible = false;
                if ($lead->assigned == $current_user_id || $lead->addedfrom == $current_user_id || (isset($lead->is_public) && $lead->is_public == 1)) {
                    $visible = true;
                }
                echo "  Visibility: " . ($visible ? "✓ VISIBLE" : "✗ HIDDEN") . "\n";
            } else {
                echo "  Visibility: ✓ VISIBLE (full permission)\n";
            }
            echo "\n";
        }
        
        // Check for leads with is_public != 1
        $CI->db->where('addedfrom', 0);
        $CI->db->where('(is_public IS NULL OR is_public != 1)');
        $hidden_leads = $CI->db->get(db_prefix() . 'leads')->result();
        
        if (count($hidden_leads) > 0) {
            echo "\n⚠️  WARNING: Found " . count($hidden_leads) . " leads with addedfrom=0 but is_public != 1\n";
            echo "These leads will NOT be visible to users without full 'view' permission.\n";
            echo "Lead IDs: " . implode(', ', array_column($hidden_leads, 'id')) . "\n";
        }
    }
}

echo "\n====================================\n";
echo "Diagnostic complete.\n";











