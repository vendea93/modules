<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    #menu { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    .status-hold { background-color: #f0ad4e; color: white; }
</style>
<div id="wrapper">
    <div class="content-wrapper" style="margin-left: 0;">
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo _l('form_sync') . ' - ' . _l('form_sync_pending_review'); ?></h4>
                            <hr class="hr-panel-heading" />
                            
                            <!-- Filters -->
                            <div class="row">
                                <div class="col-md-12">
                                    <?php echo form_open(admin_url('form_sync/pending_review'), ['method' => 'get']); ?>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Form</label>
                                                <select name="form_id" class="form-control selectpicker" data-live-search="true">
                                                    <option value="">All Forms</option>
                                                    <?php foreach ($form_configs as $config): ?>
                                                        <option value="<?php echo html_escape($config['form_id']); ?>" 
                                                                <?php echo (isset($filters['form_id']) && $filters['form_id'] == $config['form_id']) ? 'selected' : ''; ?>>
                                                            <?php echo html_escape($config['form_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Target Type</label>
                                                <select name="target_type" class="form-control">
                                                    <option value="">All Types</option>
                                                    <option value="lead" <?php echo (isset($filters['target_type']) && $filters['target_type'] == 'lead') ? 'selected' : ''; ?>>
                                                        <?php echo _l('form_sync_target_type_lead'); ?>
                                                    </option>
                                                    <option value="customer" <?php echo (isset($filters['target_type']) && $filters['target_type'] == 'customer') ? 'selected' : ''; ?>>
                                                        <?php echo _l('form_sync_target_type_customer'); ?>
                                                    </option>
                                                    <option value="estimate_request" <?php echo (isset($filters['target_type']) && $filters['target_type'] == 'estimate_request') ? 'selected' : ''; ?>>
                                                        <?php echo _l('form_sync_target_type_estimate_request'); ?>
                                                    </option>
                                                    <option value="ticket" <?php echo (isset($filters['target_type']) && $filters['target_type'] == 'ticket') ? 'selected' : ''; ?>>
                                                        <?php echo _l('form_sync_target_type_ticket'); ?>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-info btn-block">
                                                    <i class="fa fa-filter"></i> Filter
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                            
                            <hr />
                            
                            <?php if (empty($logs)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> No submissions held for review.
                                </div>
                            <?php else: ?>
                                <!-- Bulk Actions -->
                                <div class="row" style="margin-bottom: 15px;">
                                    <div class="col-md-12">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="bulk-actions-btn" disabled>
                                                <i class="fa fa-tasks"></i> Bulk Actions <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" id="bulk-approve-btn"><i class="fa fa-check"></i> Approve Selected</a></li>
                                                <li><a href="#" id="bulk-ignore-btn"><i class="fa fa-times"></i> Ignore Selected</a></li>
                                            </ul>
                                        </div>
                                        <span id="selected-count" style="margin-left: 10px; color: #666;"></span>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table dt-table table-data">
                                        <thead>
                                            <tr>
                                                <th style="width: 30px;">
                                                    <input type="checkbox" id="select-all-checkbox" title="Select All">
                                                </th>
                                                <th>Date</th>
                                                <th>Form</th>
                                                <th style="width: 80px;">ID</th>
                                                <th>Target Type</th>
                                                <th>Hold Reason</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="submission-checkbox" value="<?php echo $log['id']; ?>" data-log-id="<?php echo $log['id']; ?>">
                                                    </td>
                                                    <td><?php echo _dt($log['datecreated']); ?></td>
                                                    <td><?php echo html_escape(isset($log['form_name']) ? $log['form_name'] : $log['form_id']); ?></td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-default copy-submission-id" 
                                                                data-submission-id="<?php echo html_escape($log['submission_id']); ?>"
                                                                data-toggle="tooltip" 
                                                                title="Click to copy Submission ID">
                                                            <i class="fa fa-copy"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <span class="label label-info">
                                                            <?php 
                                                            $target_type = isset($log['target_type']) ? trim(strtolower($log['target_type'])) : 'customer';
                                                            if ($target_type === 'lead') {
                                                                echo _l('form_sync_target_type_lead');
                                                            } elseif ($target_type === 'estimate_request') {
                                                                echo _l('form_sync_target_type_estimate_request');
                                                            } elseif ($target_type === 'ticket') {
                                                                echo _l('form_sync_target_type_ticket');
                                                            } else {
                                                                echo _l('form_sync_target_type_customer');
                                                            }
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $hold_reason_type = isset($log['hold_reason_type']) ? $log['hold_reason_type'] : 'none';
                                                        
                                                        if ($hold_reason_type === 'duplicate'): ?>
                                                            <span class="label label-warning">
                                                                <i class="fa fa-exclamation-triangle"></i> Duplicate
                                                            </span>
                                                            <br><br>
                                                            <?php if ($log['duplicate_entity_id']): ?>
                                                                <strong><?php echo $log['duplicate_entity_type'] === 'lead' ? 'Duplicate in Leads' : 'Duplicate in Customers'; ?></strong>
                                                                <br>
                                                                <small>
                                                                    <?php echo html_escape($log['duplicate_reason']); ?>
                                                                    <br>
                                                                    <?php if ($log['duplicate_entity_type'] === 'lead'): ?>
                                                                        <a href="<?php echo admin_url('leads/lead/' . $log['duplicate_entity_id']); ?>" target="_blank">
                                                                            View Lead #<?php echo $log['duplicate_entity_id']; ?>
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <a href="<?php echo admin_url('clients/client/' . $log['duplicate_entity_id']); ?>" target="_blank">
                                                                            View Customer #<?php echo $log['duplicate_entity_id']; ?>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </small>
                                                            <?php else: ?>
                                                                <?php echo html_escape($log['duplicate_reason']); ?>
                                                            <?php endif; ?>
                                                        <?php elseif ($hold_reason_type === 'no_mappings'): ?>
                                                            <span class="label label-info">
                                                                <i class="fa fa-map"></i> No Mappings
                                                            </span>
                                                            <br><br>
                                                            <small>
                                                                <?php echo html_escape($log['error_message']); ?>
                                                                <?php if (!isset($log['has_mappings']) || !$log['has_mappings']): ?>
                                                                    <br><br>
                                                                    <a href="<?php echo admin_url('form_sync/form_configurations'); ?>" class="text-primary">
                                                                        <i class="fa fa-cog"></i> Configure mappings
                                                                    </a>
                                                                <?php endif; ?>
                                                            </small>
                                                        <?php elseif ($hold_reason_type === 'manual_review'): ?>
                                                            <span class="label label-primary">
                                                                <i class="fa fa-clock-o"></i> Manual Review
                                                            </span>
                                                        <?php else: ?>
                                                            <?php if (!empty($log['duplicate_reason'])): ?>
                                                                <?php echo html_escape($log['duplicate_reason']); ?>
                                                            <?php elseif (!empty($log['error_message'])): ?>
                                                                <?php echo html_escape($log['error_message']); ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $hold_reason_type = isset($log['hold_reason_type']) ? $log['hold_reason_type'] : 'none';
                                                        $has_mappings = isset($log['has_mappings']) ? $log['has_mappings'] : true;
                                                        $approve_disabled = ($hold_reason_type === 'no_mappings' && !$has_mappings);
                                                        $approve_tooltip = $approve_disabled 
                                                            ? 'Field mappings must be configured before approval' 
                                                            : 'Approve';
                                                        ?>
                                                        <a href="<?php echo admin_url('form_sync/approve_held/' . $log['id']); ?>" 
                                                           class="btn btn-success btn-icon _delete <?php echo $approve_disabled ? 'disabled' : ''; ?>" 
                                                           data-toggle="tooltip" 
                                                           title="<?php echo html_escape($approve_tooltip); ?>"
                                                           data-confirm="<?php echo $approve_disabled ? '' : 'Are you sure you want to approve this submission and create the entity?'; ?>"
                                                           data-form-id="<?php echo html_escape($log['form_id']); ?>"
                                                           data-target-type="<?php echo html_escape($log['target_type']); ?>"
                                                           <?php if ($approve_disabled): ?>
                                                               onclick="alert('Please configure field mappings first.'); return false;"
                                                               style="pointer-events: none; opacity: 0.5; cursor: not-allowed;"
                                                           <?php endif; ?>>
                                                            <i class="fa fa-check"></i>
                                                        </a>
                                                        <a href="<?php echo admin_url('form_sync/ignore_held/' . $log['id']); ?>" 
                                                           class="btn btn-warning btn-icon _delete" 
                                                           data-toggle="tooltip" 
                                                           title="Ignore"
                                                           data-confirm="Are you sure you want to ignore this submission?">
                                                            <i class="fa fa-times"></i>
                                                        </a>
                                                        <a href="<?php echo admin_url('form_sync/view_submission_data/' . $log['id']); ?>" 
                                                           class="btn btn-default btn-icon" 
                                                           target="_blank" 
                                                           data-toggle="tooltip" 
                                                           title="View Data">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
(function($) {
    'use strict';
    
    // Copy submission ID to clipboard function
    function copySubmissionIdToClipboard(submissionId) {
        if (!submissionId) {
            if (typeof alert_float !== 'undefined') {
                alert_float('warning', 'No submission ID to copy');
            } else {
                alert('No submission ID to copy');
            }
            return;
        }
        
        // Create a temporary textarea element
        var tempTextarea = document.createElement('textarea');
        tempTextarea.value = submissionId;
        tempTextarea.style.position = 'fixed';
        tempTextarea.style.opacity = '0';
        document.body.appendChild(tempTextarea);
        tempTextarea.select();
        tempTextarea.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            document.execCommand('copy');
            if (typeof alert_float !== 'undefined') {
                alert_float('success', 'Submission ID copied to clipboard');
            } else {
                alert('Submission ID copied to clipboard');
            }
        } catch (err) {
            if (typeof alert_float !== 'undefined') {
                alert_float('danger', 'Failed to copy Submission ID');
            } else {
                alert('Failed to copy Submission ID');
            }
        } finally {
            document.body.removeChild(tempTextarea);
        }
    }
    
    // Handle copy button clicks
    $(document).on('click', '.copy-submission-id', function(e) {
        e.preventDefault();
        var submissionId = $(this).data('submission-id');
        copySubmissionIdToClipboard(submissionId);
    });
    
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Function to check mapping status and update button states
        function updateApproveButtonStates() {
            $('.approve-btn-no-mappings').each(function() {
                var $btn = $(this);
                var formId = $btn.data('form-id');
                var targetType = $btn.data('target-type');
                
                if (!formId || !targetType) {
                    return;
                }
                
                // Check if mappings exist via AJAX
                $.ajax({
                    url: '<?php echo admin_url('form_sync/check_mappings'); ?>',
                    type: 'GET',
                    data: {
                        form_id: formId,
                        target_type: targetType
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.has_mappings) {
                            // Enable button
                            $btn.removeClass('disabled')
                                .removeAttr('onclick')
                                .removeAttr('style')
                                .attr('data-confirm', 'Are you sure you want to approve this submission and create the entity?')
                                .attr('title', 'Approve')
                                .removeClass('approve-btn-no-mappings')
                                .css({
                                    'pointer-events': '',
                                    'opacity': '',
                                    'cursor': ''
                                });
                            
                            // Update tooltip
                            $btn.tooltip('destroy').tooltip();
                        }
                    },
                    error: function() {
                        // Silently fail - button stays disabled
                    }
                });
            });
        }
        
        // Mark buttons that need mapping checks
        $('a.btn-success._delete').each(function() {
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var holdReasonType = $row.find('td:eq(4)').find('.label-info').length > 0 ? 'no_mappings' : 
                                 ($row.find('td:eq(4)').find('.label-warning').length > 0 ? 'duplicate' : 'none');
            
            // Get form_id and target_type from data attributes (set in PHP)
            var formId = $btn.data('form-id');
            var targetType = $btn.data('target-type');
            
            if (holdReasonType === 'no_mappings' && $btn.hasClass('disabled') && formId && targetType) {
                $btn.addClass('approve-btn-no-mappings');
            }
        });
        
        // Check mapping status on page load
        updateApproveButtonStates();
        
        // Optionally: Poll for mapping changes every 30 seconds (if admin has mapping page open)
        // Uncomment if you want automatic updates:
        // setInterval(updateApproveButtonStates, 30000);
        
        // Listen for storage events (if mappings are configured in another tab)
        if (typeof Storage !== 'undefined') {
            $(window).on('storage', function(e) {
                if (e.originalEvent.key === 'form_sync_mappings_updated') {
                    updateApproveButtonStates();
                }
            });
        }
        
        // Bulk operations functionality
        var $selectAllCheckbox = $('#select-all-checkbox');
        var $submissionCheckboxes = $('.submission-checkbox');
        var $bulkActionsBtn = $('#bulk-actions-btn');
        var $selectedCount = $('#selected-count');
        var $bulkApproveBtn = $('#bulk-approve-btn');
        var $bulkIgnoreBtn = $('#bulk-ignore-btn');
        var isProcessing = false;
        
        // Update selected count and enable/disable bulk actions button
        function updateBulkActionsState() {
            var selectedCount = $submissionCheckboxes.filter(':checked').length;
            
            if (selectedCount > 0) {
                $bulkActionsBtn.prop('disabled', false);
                $selectedCount.text(selectedCount + ' submission' + (selectedCount > 1 ? 's' : '') + ' selected');
            } else {
                $bulkActionsBtn.prop('disabled', true);
                $selectedCount.text('');
            }
            
            // Update select all checkbox state
            if (selectedCount === 0) {
                $selectAllCheckbox.prop('indeterminate', false).prop('checked', false);
            } else if (selectedCount === $submissionCheckboxes.length) {
                $selectAllCheckbox.prop('indeterminate', false).prop('checked', true);
            } else {
                $selectAllCheckbox.prop('indeterminate', true);
            }
        }
        
        // Select all checkbox handler
        $selectAllCheckbox.on('change', function() {
            if (isProcessing) return;
            $submissionCheckboxes.prop('checked', $(this).prop('checked'));
            updateBulkActionsState();
        });
        
        // Individual checkbox handler
        $submissionCheckboxes.on('change', function() {
            if (isProcessing) return;
            updateBulkActionsState();
        });
        
        // Bulk approve handler
        $bulkApproveBtn.on('click', function(e) {
            e.preventDefault();
            
            if (isProcessing) return;
            
            var selectedIds = $submissionCheckboxes.filter(':checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedIds.length === 0) {
                if (typeof alert_float !== 'undefined') {
                    alert_float('warning', 'Please select at least one submission.');
                } else {
                    alert('Please select at least one submission.');
                }
                return;
            }
            
            if (!confirm('Are you sure you want to approve ' + selectedIds.length + ' submission' + (selectedIds.length > 1 ? 's' : '') + '? This will create the corresponding entities in the CRM.')) {
                return;
            }
            
            // Disable checkboxes and show loading state
            isProcessing = true;
            $submissionCheckboxes.prop('disabled', true);
            $selectAllCheckbox.prop('disabled', true);
            $bulkActionsBtn.prop('disabled', true);
            $selectedCount.html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            // Submit bulk approve request
            $.ajax({
                url: '<?php echo admin_url('form_sync/bulk_approve_held'); ?>',
                type: 'POST',
                data: {
                    log_ids: selectedIds,
                    csrf_token_name: $('input[name="csrf_token_name"]').val() || $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    if (response && response.success !== undefined) {
                        if (response.success) {
                            if (typeof alert_float !== 'undefined') {
                                alert_float('success', response.message || 'Submissions approved successfully.');
                            } else {
                                alert(response.message || 'Submissions approved successfully.');
                            }
                            // Reload page after short delay
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            if (typeof alert_float !== 'undefined') {
                                alert_float('warning', response.message || 'Some submissions failed to approve.');
                            } else {
                                alert(response.message || 'Some submissions failed to approve.');
                            }
                            // Reload page to show updated state
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        }
                    } else {
                        // Fallback for non-JSON response
                        window.location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Bulk approve error:', error);
                    if (typeof alert_float !== 'undefined') {
                        alert_float('danger', 'An error occurred while approving submissions. Please try again.');
                    } else {
                        alert('An error occurred while approving submissions. Please try again.');
                    }
                    // Re-enable checkboxes
                    isProcessing = false;
                    $submissionCheckboxes.prop('disabled', false);
                    $selectAllCheckbox.prop('disabled', false);
                    updateBulkActionsState();
                }
            });
        });
        
        // Bulk ignore handler
        $bulkIgnoreBtn.on('click', function(e) {
            e.preventDefault();
            
            if (isProcessing) return;
            
            var selectedIds = $submissionCheckboxes.filter(':checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedIds.length === 0) {
                if (typeof alert_float !== 'undefined') {
                    alert_float('warning', 'Please select at least one submission.');
                } else {
                    alert('Please select at least one submission.');
                }
                return;
            }
            
            if (!confirm('Are you sure you want to ignore ' + selectedIds.length + ' submission' + (selectedIds.length > 1 ? 's' : '') + '? These submissions will be removed from pending review.')) {
                return;
            }
            
            // Disable checkboxes and show loading state
            isProcessing = true;
            $submissionCheckboxes.prop('disabled', true);
            $selectAllCheckbox.prop('disabled', true);
            $bulkActionsBtn.prop('disabled', true);
            $selectedCount.html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            // Submit bulk ignore request
            $.ajax({
                url: '<?php echo admin_url('form_sync/bulk_ignore_held'); ?>',
                type: 'POST',
                data: {
                    log_ids: selectedIds,
                    csrf_token_name: $('input[name="csrf_token_name"]').val() || $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    if (response && response.success !== undefined) {
                        if (response.success) {
                            if (typeof alert_float !== 'undefined') {
                                alert_float('success', response.message || 'Submissions ignored successfully.');
                            } else {
                                alert(response.message || 'Submissions ignored successfully.');
                            }
                            // Reload page after short delay
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            if (typeof alert_float !== 'undefined') {
                                alert_float('warning', response.message || 'Some submissions failed to ignore.');
                            } else {
                                alert(response.message || 'Some submissions failed to ignore.');
                            }
                            // Reload page to show updated state
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        }
                    } else {
                        // Fallback for non-JSON response
                        window.location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Bulk ignore error:', error);
                    if (typeof alert_float !== 'undefined') {
                        alert_float('danger', 'An error occurred while ignoring submissions. Please try again.');
                    } else {
                        alert('An error occurred while ignoring submissions. Please try again.');
                    }
                    // Re-enable checkboxes
                    isProcessing = false;
                    $submissionCheckboxes.prop('disabled', false);
                    $selectAllCheckbox.prop('disabled', false);
                    updateBulkActionsState();
                }
            });
        });
        
        // Initialize bulk actions state
        updateBulkActionsState();
    });
})(jQuery);
</script>
