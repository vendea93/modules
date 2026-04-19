<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    #menu { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    .status-hold { background-color: #f0ad4e; color: white; }
    .status-approved { background-color: #5cb85c; color: white; }
    .status-ignored { background-color: #777; color: white; }
</style>
<div id="wrapper">
    <div class="content-wrapper" style="margin-left: 0;">
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="no-margin"><?php echo _l('form_sync') . ' - ' . _l('form_sync_logs'); ?></h4>
                                </div>
                            </div>
                            <hr class="hr-panel-heading" />
                            
                            <!-- Filters -->
                            <div class="row">
                                <div class="col-md-12">
                                    <?php echo form_open(admin_url('form_sync/logs'), ['method' => 'get']); ?>
                                    <div class="row">
                                        <div class="col-md-3">
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
                                        <div class="col-md-2">
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
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="">All Statuses</option>
                                                    <option value="success" <?php echo (isset($filters['status']) && $filters['status'] == 'success') ? 'selected' : ''; ?>>
                                                        Success
                                                    </option>
                                                    <option value="failed" <?php echo (isset($filters['status']) && $filters['status'] == 'failed') ? 'selected' : ''; ?>>
                                                        Failed
                                                    </option>
                                                    <option value="hold" <?php echo (isset($filters['status']) && $filters['status'] == 'hold') ? 'selected' : ''; ?>>
                                                        Hold
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Provider</label>
                                                <select name="provider" class="form-control">
                                                    <option value="">All Providers</option>
                                                    <option value="framer" <?php echo (isset($filters['provider']) && $filters['provider'] == 'framer') ? 'selected' : ''; ?>>
                                                        Framer
                                                    </option>
                                                    <option value="webflow" <?php echo (isset($filters['provider']) && $filters['provider'] == 'webflow') ? 'selected' : ''; ?>>
                                                        Webflow
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="submit" class="btn btn-info">
                                                        <i class="fa fa-filter"></i> Filter
                                                    </button>
                                                    <a href="<?php echo admin_url('form_sync/logs'); ?>" class="btn btn-default">
                                                        <i class="fa fa-times"></i> Clear
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                            
                            <hr />
                            
                            <?php if (empty($logs)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> No submission logs found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table dt-table table-data">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Form</th>
                                                <th>Provider</th>
                                                <th>Entity</th>
                                                <th>Target Type</th>
                                                <th>Status</th>
                                                <th>Hold Status</th>
                                                <th>Duplicate</th>
                                                <th>Error</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($logs as $log): ?>
                                                <tr>
                                                    <td><?php echo _dt($log['datecreated']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $form_name = isset($log['form_name']) ? $log['form_name'] : (isset($log['form_id']) ? $log['form_id'] : '-');
                                                        echo html_escape($form_name);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <span class="label <?php echo ($log['provider'] ?? 'framer') === 'framer' ? 'label-primary' : 'label-info'; ?>">
                                                            <?php echo ucfirst($log['provider'] ?? 'framer'); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($log['customer_id'])): ?>
                                                            <a href="<?php echo admin_url('clients/client/' . $log['customer_id']); ?>" target="_blank" class="text-info">
                                                                <?php 
                                                                $customer_name = isset($log['customer_name']) ? $log['customer_name'] : 'Customer #' . $log['customer_id'];
                                                                echo html_escape($customer_name);
                                                                ?>
                                                            </a>
                                                        <?php elseif (!empty($log['lead_id'])): ?>
                                                            <a href="<?php echo admin_url('leads/lead/' . $log['lead_id']); ?>" target="_blank" class="text-info">
                                                                <?php 
                                                                $lead_name = isset($log['lead_name']) ? $log['lead_name'] : 'Lead #' . $log['lead_id'];
                                                                echo html_escape($lead_name);
                                                                ?>
                                                            </a>
                                                        <?php elseif (!empty($log['estimate_request_id'])): ?>
                                                            <a href="<?php echo admin_url('estimate_request/view/' . $log['estimate_request_id']); ?>" target="_blank" class="text-info">
                                                                Estimate Request #<?php echo $log['estimate_request_id']; ?>
                                                            </a>
                                                        <?php elseif (!empty($log['ticket_id'])): ?>
                                                            <a href="<?php echo admin_url('tickets/ticket/' . $log['ticket_id']); ?>" target="_blank" class="text-info">
                                                                Ticket #<?php echo $log['ticket_id']; ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not Created</span>
                                                        <?php endif; ?>
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
                                                        $status_class = 'label-default';
                                                        if ($log['status'] == 'success') {
                                                            $status_class = 'label-success';
                                                        } elseif ($log['status'] == 'failed') {
                                                            $status_class = 'label-danger';
                                                        } elseif ($log['status'] == 'hold') {
                                                            $status_class = 'label-warning';
                                                        }
                                                        ?>
                                                        <span class="label <?php echo $status_class; ?>">
                                                            <?php 
                                                            if ($log['status'] == 'success') {
                                                                echo 'Success';
                                                            } elseif ($log['status'] == 'failed') {
                                                                echo 'Failed';
                                                            } elseif ($log['status'] == 'hold') {
                                                                echo 'Hold';
                                                            } else {
                                                                echo 'Pending';
                                                            }
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['hold_status'] && $log['hold_status'] !== 'none'): ?>
                                                            <?php
                                                            $hold_class = 'label-default';
                                                            if ($log['hold_status'] == 'hold') {
                                                                $hold_class = 'label-warning';
                                                            } elseif ($log['hold_status'] == 'approved') {
                                                                $hold_class = 'label-success';
                                                            } elseif ($log['hold_status'] == 'ignored') {
                                                                $hold_class = 'label-default';
                                                            }
                                                            ?>
                                                            <span class="label <?php echo $hold_class; ?>">
                                                                <?php
                                                                if ($log['hold_status'] == 'hold') {
                                                                    echo 'Hold';
                                                                } elseif ($log['hold_status'] == 'approved') {
                                                                    echo 'Approved';
                                                                } elseif ($log['hold_status'] == 'ignored') {
                                                                    echo 'Ignored';
                                                                }
                                                                ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['duplicate_reason']): ?>
                                                            <div>
                                                                <?php if ($log['duplicate_entity_type'] == 'customer'): ?>
                                                                    <strong class="text-warning">Duplicate in Customers</strong>
                                                                    <?php if ($log['duplicate_entity_id']): ?>
                                                                        <br>
                                                                        <a href="<?php echo admin_url('clients/client/' . $log['duplicate_entity_id']); ?>" class="text-info" target="_blank">
                                                                            View Customer #<?php echo $log['duplicate_entity_id']; ?>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                <?php elseif ($log['duplicate_entity_type'] == 'lead'): ?>
                                                                    <strong class="text-warning">Duplicate in Leads</strong>
                                                                    <?php if ($log['duplicate_entity_id']): ?>
                                                                        <br>
                                                                        <a href="<?php echo admin_url('leads/lead/' . $log['duplicate_entity_id']); ?>" class="text-info" target="_blank">
                                                                            View Lead #<?php echo $log['duplicate_entity_id']; ?>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                                <?php if ($log['duplicate_reason']): ?>
                                                                    <br>
                                                                    <small class="text-muted"><?php echo html_escape($log['duplicate_reason']); ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['error_message']): ?>
                                                            <span class="text-danger" style="cursor: pointer;" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="<?php echo html_escape($log['error_message']); ?>">
                                                                <i class="fa fa-exclamation-triangle"></i>
                                                                <?php echo html_escape(mb_substr($log['error_message'], 0, 50)); ?>
                                                                <?php echo (mb_strlen($log['error_message']) > 50) ? '...' : ''; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-success">
                                                                <i class="fa fa-check-circle"></i> No Errors
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['submission_data']): ?>
                                                            <a href="<?php echo admin_url('form_sync/view_submission_data/' . $log['id']); ?>" 
                                                               class="btn btn-default btn-icon" 
                                                               target="_blank" 
                                                               data-toggle="tooltip" 
                                                               title="View Data">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($log['hold_status'] == 'hold'): ?>
                                                            <a href="<?php echo admin_url('form_sync/approve_held/' . $log['id']); ?>" 
                                                               class="btn btn-success btn-icon _delete" 
                                                               data-toggle="tooltip" 
                                                               title="Approve"
                                                               data-confirm="Are you sure you want to approve this submission and create the entity?">
                                                                <i class="fa fa-check"></i>
                                                            </a>
                                                            <a href="<?php echo admin_url('form_sync/ignore_held/' . $log['id']); ?>" 
                                                               class="btn btn-warning btn-icon _delete" 
                                                               data-toggle="tooltip" 
                                                               title="Ignore"
                                                               data-confirm="Are you sure you want to ignore this submission?">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        <?php endif; ?>
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
