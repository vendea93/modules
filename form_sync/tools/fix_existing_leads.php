<?php
/**
 * Fix Existing Leads Visibility
 * 
 * This script fixes visibility settings for leads that were already created
 * but are not visible in the leads table.
 * 
 * Usage: php modules/form_sync/tools/fix_existing_leads.php
 */

// Bootstrap CodeIgniter
define('BASEPATH', true);
require_once(__DIR__ . '/../../index.php');

$CI =& get_instance();
$CI->load->database();

echo "FormSync - Fix Existing Leads Visibility\n";
echo "========================================\n\n";

// Find all leads with addedfrom = 0 that don't have is_public = 1
$CI->db->where('addedfrom', 0);
$CI->db->where('(is_public IS NULL OR is_public != 1)');
$leads_to_fix = $CI->db->get(db_prefix() . 'leads')->result();

echo "Found " . count($leads_to_fix) . " leads that need fixing\n\n";

if (count($leads_to_fix) > 0) {
    foreach ($leads_to_fix as $lead) {
        echo "Fixing Lead ID: {$lead->id}\n";
        echo "  Name: " . ($lead->name ?? 'N/A') . "\n";
        echo "  Current addedfrom: " . ($lead->addedfrom ?? 'NULL') . "\n";
        echo "  Current is_public: " . ($lead->is_public ?? 'NULL') . "\n";
        
        $CI->db->where('id', $lead->id);
        $update_result = $CI->db->update(db_prefix() . 'leads', [
            'addedfrom' => 0,
            'is_public' => 1
        ]);
        
        $affected = $CI->db->affected_rows();
        echo "  Update result: " . ($update_result ? 'success' : 'failed') . ", affected rows: {$affected}\n";
        
        // Verify
        $CI->db->where('id', $lead->id);
        $fixed = $CI->db->get(db_prefix() . 'leads')->row();
        echo "  After fix - addedfrom: " . ($fixed->addedfrom ?? 'NULL') . ", is_public: " . ($fixed->is_public ?? 'NULL') . "\n";
        echo "\n";
    }
    
    echo "✓ Fixed " . count($leads_to_fix) . " leads\n";
} else {
    echo "✓ No leads need fixing\n";
}

echo "\n========================================\n";
echo "Fix complete. Check the Leads table to verify leads are now visible.\n";











