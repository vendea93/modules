<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    #menu { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    .provider-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .provider-framer {
        background: #0066ff;
        color: white;
    }
    .provider-webflow {
        background: #4353ff;
        color: white;
    }
    .provider-google_forms {
        background: #4285f4;
        color: white;
    }
    .provider-elementor {
        background: #92003b;
        color: white;
    }
    .provider-universal {
        background: #6B46C1;
        color: white;
    }
    .webflow-secret-add {
        color: white !important;
    }
    .webflow-secret-add:hover,
    .webflow-secret-add:focus {
        color: white !important;
    }
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
                                    <h4 class="no-margin"><?php echo _l('form_sync_form_configurations'); ?></h4>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#addFormConfigModal">
                                        <i class="fa fa-plus"></i> <?php echo _l('add'); ?>
                                    </button>
                                </div>
                            </div>
                            <hr class="hr-panel-heading" />
                            
                            <?php if (empty($form_configs)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> No forms connected yet. Click "Add" to connect your first form.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table dt-table table-data">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('form_sync_form_name'); ?></th>
                                                <th><?php echo _l('form_sync_provider'); ?></th>
                                                <th><?php echo _l('form_sync_site_name'); ?></th>
                                                <th><?php echo _l('form_sync_form_id'); ?></th>
                                                <th><?php echo _l('form_sync_target_type'); ?></th>
                                                <th><?php echo _l('form_sync_webhook_url'); ?></th>
                                                <th><?php echo _l('form_sync_webhook_secret'); ?></th>
                                                <th><?php echo _l('status'); ?></th>
                                                <th><?php echo _l('options'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($form_configs as $config): ?>
                                                <tr>
                                                    <td><?php echo html_escape($config['form_name']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $provider_id = $config['provider'];
                                                        $provider_name = $provider_id;
                                                        if (isset($providers) && isset($providers[$provider_id])) {
                                                            $provider_name = $providers[$provider_id]->getName();
                                                        }
                                                        ?>
                                                        <span class="provider-badge provider-<?php echo html_escape($provider_id); ?>">
                                                            <?php echo html_escape(strtoupper($provider_name)); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo !empty($config['site_name']) ? html_escape($config['site_name']) : '<span class="text-muted">-</span>'; ?></td>
                                                    <td><code><?php echo html_escape($config['form_id']); ?></code></td>
                                                    <td>
                                                        <span class="label label-info">
                                                            <?php 
                                                            $target_type = isset($config['target_type']) ? trim(strtolower($config['target_type'])) : 'customer';
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
                                                        <?php if (!empty($config['webhook_url'])): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-default" 
                                                                    onclick="copyToClipboard('<?php echo html_escape($config['webhook_url']); ?>', 'webhook_url')"
                                                                    data-toggle="tooltip" 
                                                                    title="<?php echo _l('form_sync_webhook_url_copy'); ?>">
                                                                <i class="fa fa-copy"></i> <?php echo _l('form_sync_webhook_url_copy'); ?>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="webhook-secret-cell" data-config-id="<?php echo $config['id']; ?>" data-provider="<?php echo html_escape($config['provider']); ?>">
                                                        <?php if ($config['provider'] === 'webflow'): ?>
                                                            <?php if (!empty($config['webhook_secret'])): ?>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-default" 
                                                                        onclick="copyToClipboard('<?php echo html_escape($config['webhook_secret']); ?>', 'webhook_secret')"
                                                                        data-toggle="tooltip" 
                                                                        title="<?php echo _l('form_sync_webhook_secret'); ?>">
                                                                    <i class="fa fa-copy"></i> <?php echo _l('form_sync_webhook_secret'); ?>
                                                                </button>
                                                            <?php else: ?>
                                                                <a href="#" 
                                                                   class="btn btn-sm btn-info webflow-secret-add" 
                                                                   data-config-id="<?php echo $config['id']; ?>"
                                                                   data-form-name="<?php echo html_escape($config['form_name']); ?>"
                                                                   style="color: white !important;">
                                                                    <i class="fa fa-plus"></i> <?php echo _l('form_sync_webflow_secret_add_here'); ?>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <?php if (!empty($config['webhook_secret'])): ?>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-default" 
                                                                        onclick="copyToClipboard('<?php echo html_escape($config['webhook_secret']); ?>', 'webhook_secret')"
                                                                        data-toggle="tooltip" 
                                                                        title="<?php echo _l('form_sync_webhook_secret'); ?>">
                                                                    <i class="fa fa-copy"></i> <?php echo _l('form_sync_webhook_secret'); ?>
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($config['enabled']): ?>
                                                            <span class="label label-success"><?php echo _l('active'); ?></span>
                                                        <?php else: ?>
                                                            <span class="label label-default"><?php echo _l('inactive'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="#" 
                                                           class="btn btn-default btn-icon map-fields-btn <?php echo (isset($config['has_submissions']) && $config['has_submissions']) ? '' : 'disabled'; ?>" 
                                                           data-form-id="<?php echo html_escape($config['form_id']); ?>" 
                                                           data-target-type="<?php echo html_escape($config['target_type']); ?>" 
                                                           data-form-name="<?php echo html_escape($config['form_name']); ?>" 
                                                           data-provider="<?php echo html_escape($config['provider']); ?>"
                                                           data-has-submissions="<?php echo (isset($config['has_submissions']) && $config['has_submissions']) ? '1' : '0'; ?>"
                                                           data-toggle="tooltip" 
                                                           title="<?php echo (isset($config['has_submissions']) && $config['has_submissions']) ? 'Map Fields' : 'Submit at least one form to enable field mapping'; ?>"
                                                           <?php if (!isset($config['has_submissions']) || !$config['has_submissions']): ?>
                                                               style="opacity: 0.5; cursor: not-allowed; pointer-events: none;"
                                                           <?php endif; ?>>
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                        <a href="#" 
                                                           class="btn btn-warning btn-icon edit-config" 
                                                           data-config='<?php echo json_encode($config); ?>' 
                                                           data-toggle="tooltip" 
                                                           title="<?php echo _l('edit'); ?>">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                        <a href="<?php echo admin_url('form_sync/form_configurations'); ?>" 
                                                           class="btn btn-danger btn-icon _delete" 
                                                           data-toggle="tooltip" 
                                                           title="<?php echo _l('delete'); ?>" 
                                                           data-confirm="Are you sure you want to remove this form connection?" 
                                                           data-config-id="<?php echo $config['id']; ?>">
                                                            <i class="fa fa-remove"></i>
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

<!-- Add/Edit Form Config Modal -->
<div class="modal fade" id="addFormConfigModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalTitle"><?php echo _l('add'); ?> Form Setup</h4>
            </div>
            <?php echo form_open(admin_url('form_sync/form_configurations')); ?>
            <div class="modal-body">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="config_id" id="config_id" value="">
                
                <div class="row" style="padding-bottom: 16px;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_provider">
                                <?php echo _l('form_sync_provider'); ?> <span class="text-danger">*</span>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="<?php echo html_escape(_l('form_sync_provider_select')); ?>"
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="provider" id="modal_provider" class="form-control" required>
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php 
                                if (isset($providers) && !empty($providers)) {
                                    foreach ($providers as $provider_id => $provider) {
                                        echo '<option value="' . html_escape($provider_id) . '">' . html_escape($provider->getName()) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_site_name">
                                <?php echo _l('form_sync_site_name'); ?>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="<?php echo html_escape(_l('form_sync_site_name_help')); ?>"
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <input type="text" 
                                   name="site_name" 
                                   id="modal_site_name" 
                                   class="form-control" 
                                   placeholder="e.g., Main Website, Landing Page Site">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6" style="padding-bottom: 16px;">
                        <div class="form-group">
                            <label for="modal_form_id">
                                <?php echo _l('form_sync_form_id'); ?> <span class="text-danger">*</span>
                                <i id="modal_form_id_info" 
                                   class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="<?php echo html_escape(_l('form_sync_form_id_help')); ?>"
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <input type="text" 
                                   name="form_id" 
                                   id="modal_form_id" 
                                   class="form-control" 
                                   required
                                   placeholder="e.g., abc123xyz">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_form_name">
                                <?php echo _l('form_sync_form_name'); ?> <span class="text-danger">*</span>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="<?php echo html_escape(_l('form_sync_form_name_help')); ?>"
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <input type="text" 
                                   name="form_name" 
                                   id="modal_form_name" 
                                   class="form-control" 
                                   required
                                   placeholder="e.g., Contact Form, Newsletter Signup">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_target_type">
                                <?php echo _l('form_sync_target_type'); ?> <span class="text-danger">*</span>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="<?php echo html_escape(_l('form_sync_target_type_help')); ?>"
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="target_type" id="modal_target_type" class="form-control" required>
                                <option value="customer"><?php echo _l('form_sync_target_type_customer'); ?></option>
                                <option value="lead"><?php echo _l('form_sync_target_type_lead'); ?></option>
                                <option value="estimate_request"><?php echo _l('form_sync_target_type_estimate_request'); ?></option>
                                <option value="ticket"><?php echo _l('form_sync_target_type_ticket'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Customer Group (shown only when target_type is customer) -->
                    <div class="col-md-6" id="customer_group_col" style="display: none; padding-bottom: 16px;">
                        <div class="form-group" id="customer_group_field">
                            <label for="modal_customer_group_id">
                                <?php echo _l('customer_group'); ?>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="New customers from this form will be added to this group."
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="customer_group_id" id="modal_customer_group_id" class="form-control selectpicker" data-live-search="true">
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php foreach ($customer_groups as $group): ?>
                                    <option value="<?php echo $group['id']; ?>">
                                        <?php echo html_escape($group['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Lead Source (shown only when target_type is lead) - MANDATORY -->
                    <div class="col-md-6" id="lead_source_col" style="display: none;">
                        <div class="form-group" id="lead_source_field">
                            <label for="modal_lead_source_id">
                                <?php echo _l('lead_source'); ?> <span class="text-danger">*</span>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="New leads from this form will be tagged with this source."
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="lead_source_id" id="modal_lead_source_id" class="form-control selectpicker" data-live-search="true" required>
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php foreach ($lead_sources as $source): ?>
                                    <option value="<?php echo $source['id']; ?>">
                                        <?php echo html_escape($source['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Estimate Request Status (shown only when target_type is estimate_request) -->
                    <div class="col-md-6" id="estimate_request_status_col" style="display: none;">
                        <div class="form-group">
                            <label for="modal_estimate_request_status_id">
                                <?php echo _l('form_sync_estimate_request_status'); ?> <span class="text-danger">*</span>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="Estimate requests from this form will be created with this status."
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="estimate_request_status_id" id="modal_estimate_request_status_id" class="form-control selectpicker" data-live-search="true">
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php if (isset($estimate_request_statuses) && is_array($estimate_request_statuses)): ?>
                                    <?php foreach ($estimate_request_statuses as $status): ?>
                                        <option value="<?php echo $status['id']; ?>">
                                            <?php echo html_escape($status['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Estimate Request Assigned Staff (shown only when target_type is estimate_request) -->
                    <div class="col-md-6" id="estimate_request_assigned_col" style="display: none;">
                        <div class="form-group">
                            <label for="modal_estimate_request_assigned_id">
                                <?php echo _l('form_sync_estimate_request_assigned'); ?>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="Optionally assign estimate requests from this form to a staff member."
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="estimate_request_assigned_id" id="modal_estimate_request_assigned_id" class="form-control selectpicker" data-live-search="true">
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php if (isset($staff_members) && is_array($staff_members)): ?>
                                    <?php foreach ($staff_members as $staff): ?>
                                        <option value="<?php echo $staff['staffid']; ?>">
                                            <?php echo html_escape($staff['firstname'] . ' ' . $staff['lastname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Perfex Form Selection (shown only when target_type is estimate_request) -->
                    <div class="col-md-12" id="perfex_form_selection_col" style="display: none;">
                        <div class="form-group">
                            <label for="modal_perfex_form_id">
                                <?php echo _l('form_sync_perfex_form'); ?> <span class="text-danger">*</span>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="Select an existing Perfex CRM estimate request form to link with this FormSync configuration."
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="perfex_form_id" id="modal_perfex_form_id" class="form-control selectpicker" data-live-search="true">
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php if (isset($perfex_estimate_forms) && is_array($perfex_estimate_forms)): ?>
                                    <?php foreach ($perfex_estimate_forms as $form): ?>
                                        <option value="<?php echo $form['id']; ?>">
                                            <?php echo html_escape($form['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Ticket Department (shown only when target_type is ticket) -->
                    <div class="col-md-6" id="ticket_department_col" style="display: none;">
                        <div class="form-group">
                            <label for="modal_ticket_department_id">
                                <?php echo _l('form_sync_ticket_department'); ?> <span class="text-danger">*</span>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="Tickets from this form will be created in this department."
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="ticket_department_id" id="modal_ticket_department_id" class="form-control selectpicker" data-live-search="true">
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php if (isset($ticket_departments) && is_array($ticket_departments)): ?>
                                    <?php foreach ($ticket_departments as $dept): ?>
                                        <option value="<?php echo $dept['departmentid']; ?>">
                                            <?php echo html_escape($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Ticket Priority (shown only when target_type is ticket) -->
                    <div class="col-md-6" id="ticket_priority_col" style="display: none;">
                        <div class="form-group">
                            <label for="modal_ticket_priority">
                                <?php echo _l('form_sync_ticket_priority'); ?> <span class="text-danger">*</span>
                                <i class="fa fa-info-circle text-info" 
                                   data-toggle="tooltip" 
                                   data-placement="top" 
                                   title="Tickets from this form will be created with this priority."
                                   style="cursor: help; margin-left: 5px;"></i>
                            </label>
                            <select name="ticket_priority" id="modal_ticket_priority" class="form-control selectpicker" data-live-search="true">
                                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                                <?php if (isset($ticket_priorities) && is_array($ticket_priorities)): ?>
                                    <?php foreach ($ticket_priorities as $priority): ?>
                                        <option value="<?php echo $priority['priorityid']; ?>">
                                            <?php echo html_escape($priority['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="enabled" id="modal_enabled" value="1" checked>
                                <label for="modal_enabled"><?php echo _l('active'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Universal Provider Settings Section -->
                <div id="universal_provider_settings" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <h5 style="margin-bottom: 15px;"><?php echo _l('form_sync_universal_provider_settings'); ?></h5>
                    
                    <!-- Payload Structure -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="universal_payload_structure">
                                    <?php echo _l('form_sync_universal_payload_structure'); ?>
                                    <i class="fa fa-info-circle text-info" data-toggle="tooltip" title="<?php echo html_escape(_l('form_sync_universal_payload_structure_help')); ?>" style="cursor: help; margin-left: 5px;"></i>
                                </label>
                                <select name="universal_payload_structure" id="universal_payload_structure" class="form-control">
                                    <option value="flat"><?php echo _l('form_sync_universal_payload_flat'); ?></option>
                                    <option value="nested"><?php echo _l('form_sync_universal_payload_nested'); ?></option>
                                    <option value="array"><?php echo _l('form_sync_universal_payload_array'); ?></option>
                                    <option value="custom"><?php echo _l('form_sync_universal_payload_custom'); ?></option>
                                    <option value="auto-detect" selected><?php echo _l('form_sync_universal_payload_auto_detect'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="universal_data_path_container" style="display: none;">
                            <div class="form-group">
                                <label for="universal_data_path">
                                    <?php echo _l('form_sync_universal_data_path'); ?>
                                    <i class="fa fa-info-circle text-info" data-toggle="tooltip" title="<?php echo html_escape(_l('form_sync_universal_data_path_help')); ?>" style="cursor: help; margin-left: 5px;"></i>
                                </label>
                                <input type="text" name="universal_data_path" id="universal_data_path" class="form-control" placeholder="e.g., payload.data">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form ID Configuration -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="universal_form_id_source">
                                    <?php echo _l('form_sync_universal_form_id_source'); ?>
                                </label>
                                <select name="universal_form_id_source" id="universal_form_id_source" class="form-control">
                                    <option value="url" selected><?php echo _l('form_sync_universal_form_id_url'); ?></option>
                                    <option value="payload"><?php echo _l('form_sync_universal_form_id_payload'); ?></option>
                                    <option value="header"><?php echo _l('form_sync_universal_form_id_header'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="universal_form_id_path_container" style="display: none;">
                            <div class="form-group">
                                <label for="universal_form_id_path">
                                    <?php echo _l('form_sync_universal_form_id_path'); ?>
                                </label>
                                <input type="text" name="universal_form_id_path" id="universal_form_id_path" class="form-control" placeholder="e.g., payload.formId or X-Form-ID">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submission ID Configuration -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="universal_submission_id_source">
                                    <?php echo _l('form_sync_universal_submission_id_source'); ?>
                                </label>
                                <select name="universal_submission_id_source" id="universal_submission_id_source" class="form-control">
                                    <option value="auto" selected><?php echo _l('form_sync_universal_submission_id_auto'); ?></option>
                                    <option value="payload"><?php echo _l('form_sync_universal_submission_id_payload'); ?></option>
                                    <option value="header"><?php echo _l('form_sync_universal_submission_id_header'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="universal_submission_id_path_container" style="display: none;">
                            <div class="form-group">
                                <label for="universal_submission_id_path">
                                    <?php echo _l('form_sync_universal_submission_id_path'); ?>
                                </label>
                                <input type="text" name="universal_submission_id_path" id="universal_submission_id_path" class="form-control" placeholder="e.g., payload.id or X-Submission-ID">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Site ID Configuration -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="universal_site_id_source">
                                    <?php echo _l('form_sync_universal_site_id_source'); ?>
                                </label>
                                <select name="universal_site_id_source" id="universal_site_id_source" class="form-control">
                                    <option value="none" selected><?php echo _l('form_sync_universal_site_id_none'); ?></option>
                                    <option value="payload"><?php echo _l('form_sync_universal_site_id_payload'); ?></option>
                                    <option value="header"><?php echo _l('form_sync_universal_site_id_header'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="universal_site_id_path_container" style="display: none;">
                            <div class="form-group">
                                <label for="universal_site_id_path">
                                    <?php echo _l('form_sync_universal_site_id_path'); ?>
                                </label>
                                <input type="text" name="universal_site_id_path" id="universal_site_id_path" class="form-control" placeholder="e.g., payload.siteId or X-Site-ID">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Metadata Fields -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="universal_metadata_fields">
                                    <?php echo _l('form_sync_universal_metadata_fields'); ?>
                                    <i class="fa fa-info-circle text-info" data-toggle="tooltip" title="<?php echo html_escape(_l('form_sync_universal_metadata_fields_help')); ?>" style="cursor: help; margin-left: 5px;"></i>
                                </label>
                                <input type="text" name="universal_metadata_fields" id="universal_metadata_fields" class="form-control" value="form_id, submission_id, timestamp, site_id" placeholder="form_id, submission_id, timestamp">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Signature Verification -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="universal_signature_verification_enabled" id="universal_signature_verification_enabled" value="1">
                                    <label for="universal_signature_verification_enabled"><?php echo _l('form_sync_universal_signature_verification_enabled'); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="universal_signature_settings" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="universal_signature_method">
                                        <?php echo _l('form_sync_universal_signature_method'); ?>
                                    </label>
                                    <select name="universal_signature_method" id="universal_signature_method" class="form-control">
                                        <option value="header" selected><?php echo _l('form_sync_universal_signature_header'); ?></option>
                                        <option value="hmac"><?php echo _l('form_sync_universal_signature_hmac'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="universal_signature_header_name">
                                        <?php echo _l('form_sync_universal_signature_header_name'); ?>
                                    </label>
                                    <input type="text" name="universal_signature_header_name" id="universal_signature_header_name" class="form-control" value="X-Signature" placeholder="X-Signature">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!-- Field Mapping Modal -->
<div class="modal fade" id="fieldMappingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="fieldMappingModalTitle"><?php echo _l('form_sync_field_mapping'); ?></h4>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div id="fieldMappingLoading" class="text-center" style="padding: 50px;">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p><?php echo _l('loading'); ?>...</p>
                </div>
                <div id="fieldMappingContent" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo _l('form_sync_form_name'); ?></label>
                                <input type="text" id="mapping_form_name_display" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo _l('form_sync_target_type'); ?></label>
                                <input type="text" id="mapping_target_type_display" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <hr />
                    <form id="fieldMappingForm">
                        <input type="hidden" name="form_id" id="mapping_form_id">
                        <input type="hidden" name="target_type" id="mapping_target_type">
                        <input type="hidden" name="provider" id="mapping_provider">
                        <div id="fieldMappingTableContainer"></div>
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> <?php echo _l('save'); ?>
                                </button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">
                                    <?php echo _l('close'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="fieldMappingError" style="display: none;" class="alert alert-danger"></div>
            </div>
        </div>
    </div>
</div>

<!-- Webflow Secret Key Modal -->
<div class="modal fade" id="webflowSecretModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('form_sync_webflow_secret_modal_title'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <?php echo _l('form_sync_webflow_secret_modal_description'); ?>
                    <br><br>
                    <small><?php echo _l('form_sync_webflow_secret_modal_help'); ?></small>
                </div>
                <form id="webflowSecretForm">
                    <input type="hidden" name="config_id" id="webflow_secret_config_id" value="">
                    <div class="form-group">
                        <label for="webflow_secret_key">
                            <?php echo _l('form_sync_webflow_secret_modal_title'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="webflow_secret" 
                               id="webflow_secret_key" 
                               class="form-control" 
                               placeholder="<?php echo _l('form_sync_webflow_secret_placeholder'); ?>"
                               required>
                        <small class="form-text text-muted">
                            This secret key is used to verify that webhook requests are coming from Webflow.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" id="saveWebflowSecretBtn">
                    <i class="fa fa-save"></i> <?php echo _l('save'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
// Simplified approach: Script is placed after init_tail() which loads jQuery
// Use jQuery directly (it's available after init_tail)

// Define copyToClipboard in global scope for inline onclick handlers
function copyToClipboard(text, type) {
    if (!text) {
        if (typeof alert_float !== 'undefined') {
            alert_float('warning', 'Nothing to copy');
        } else {
            alert('Nothing to copy');
        }
        return;
    }
    
    // Create a temporary textarea element
    var tempTextarea = document.createElement('textarea');
    tempTextarea.value = text;
    tempTextarea.style.position = 'fixed';
    tempTextarea.style.opacity = '0';
    document.body.appendChild(tempTextarea);
    tempTextarea.select();
    tempTextarea.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        var message = type === 'webhook_secret' 
            ? '<?php echo _l('form_sync_webhook_secret_copied'); ?>' 
            : '<?php echo _l('form_sync_webhook_url_copied'); ?>';
        if (typeof alert_float !== 'undefined') {
            alert_float('success', message);
        } else {
            alert(message);
        }
    } catch (err) {
        if (typeof alert_float !== 'undefined') {
            alert_float('danger', 'Failed to copy');
        } else {
            alert('Failed to copy');
        }
    } finally {
        document.body.removeChild(tempTextarea);
    }
}

(function($) {
    'use strict';
    
    // Provider-specific Form ID help text
    var formIdHelpTexts = {
        'framer': <?php echo json_encode(_l('form_sync_form_id_help_framer')); ?>,
        'webflow': <?php echo json_encode(_l('form_sync_form_id_help_webflow')); ?>,
        'google_forms': <?php echo json_encode(_l('form_sync_form_id_help_google_forms')); ?>,
        'elementor': <?php echo json_encode(_l('form_sync_form_id_help_elementor')); ?>,
        'universal': <?php echo json_encode(_l('form_sync_form_id_help_universal')); ?>,
        'default': <?php echo json_encode(_l('form_sync_form_id_help')); ?>
    };
    
    // Function to update Form ID tooltip based on selected provider
    function updateFormIdHelpText() {
        var provider = $('#modal_provider').val();
        var helpText = formIdHelpTexts[provider] || formIdHelpTexts['default'];
        var $infoIcon = $('#modal_form_id_info');
        // Update tooltip title
        $infoIcon.attr('data-original-title', helpText);
        // Destroy and reinitialize tooltip to update content
        $infoIcon.tooltip('destroy').tooltip();
    }
    
    $(document).ready(function() {
        // Initialize Bootstrap tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Update tooltip when provider changes
        $('#modal_provider').on('change', function() {
            updateFormIdHelpText();
            toggleUniversalProviderSettings();
        });
        
        // Universal Provider Settings Toggle
        function toggleUniversalProviderSettings() {
            var provider = $('#modal_provider').val();
            if (provider === 'universal') {
                $('#universal_provider_settings').show();
            } else {
                $('#universal_provider_settings').hide();
            }
        }
        
        // Universal Provider Conditional Fields
        $('#universal_payload_structure').on('change', function() {
            var structure = $(this).val();
            if (structure === 'nested' || structure === 'array' || structure === 'custom') {
                $('#universal_data_path_container').show();
            } else {
                $('#universal_data_path_container').hide();
            }
        });
        
        $('#universal_form_id_source').on('change', function() {
            var source = $(this).val();
            if (source === 'payload' || source === 'header') {
                $('#universal_form_id_path_container').show();
            } else {
                $('#universal_form_id_path_container').hide();
            }
        });
        
        $('#universal_submission_id_source').on('change', function() {
            var source = $(this).val();
            if (source === 'payload' || source === 'header') {
                $('#universal_submission_id_path_container').show();
            } else {
                $('#universal_submission_id_path_container').hide();
            }
        });
        
        $('#universal_site_id_source').on('change', function() {
            var source = $(this).val();
            if (source === 'payload' || source === 'header') {
                $('#universal_site_id_path_container').show();
            } else {
                $('#universal_site_id_path_container').hide();
            }
        });
        
        $('#universal_signature_verification_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('#universal_signature_settings').show();
            } else {
                $('#universal_signature_settings').hide();
            }
        });
        
        // Initialize Universal provider settings visibility
        toggleUniversalProviderSettings();
    // Handle edit button click (use document.on for dynamically loaded content)
    $(document).on('click', '.edit-config', function(e) {
        e.preventDefault();
        var config = $(this).data('config');
        
        $('#config_id').val(config.id);
        $('#modal_provider').val(config.provider);
        $('#modal_site_name').val(config.site_name || '');
        $('#modal_form_id').val(config.form_id);
        $('#modal_form_name').val(config.form_name);
        $('#modal_target_type').val(config.target_type);
        $('#modal_customer_group_id').val(config.customer_group_id || '');
        $('#modal_lead_source_id').val(config.lead_source_id || '');
        $('#modal_estimate_request_status_id').val(config.estimate_request_status_id || '');
        $('#modal_estimate_request_assigned_id').val(config.estimate_request_assigned_id || '');
        $('#modal_ticket_department_id').val(config.ticket_department_id || '');
        $('#modal_ticket_priority').val(config.ticket_priority || '');
        $('#modal_perfex_form_id').val(config.perfex_form_id || '');
        $('#modal_enabled').prop('checked', config.enabled == 1);
        
        // Update modal title
        $('#modalTitle').text('<?php echo _l('edit'); ?> Form Setup');
        
        // Show/hide conditional fields based on target type
        toggleTargetTypeFields(config.target_type);
        
        // Load Universal provider settings if provider is universal
        if (config.provider === 'universal' && config.custom_provider_settings) {
            try {
                var settings = JSON.parse(config.custom_provider_settings);
                $('#universal_payload_structure').val(settings.payload_structure || 'flat');
                $('#universal_data_path').val(settings.data_path || '');
                $('#universal_form_id_source').val(settings.form_id_source || 'url');
                $('#universal_form_id_path').val(settings.form_id_path || '');
                $('#universal_submission_id_source').val(settings.submission_id_source || 'auto');
                $('#universal_submission_id_path').val(settings.submission_id_path || '');
                $('#universal_site_id_source').val(settings.site_id_source || 'none');
                $('#universal_site_id_path').val(settings.site_id_path || '');
                $('#universal_metadata_fields').val(settings.metadata_fields ? settings.metadata_fields.join(', ') : 'form_id, submission_id, timestamp, site_id');
                
                if (settings.signature_verification && settings.signature_verification.enabled) {
                    $('#universal_signature_verification_enabled').prop('checked', true);
                    $('#universal_signature_method').val(settings.signature_verification.method || 'header');
                    $('#universal_signature_header_name').val(settings.signature_verification.header_name || 'X-Signature');
                } else {
                    $('#universal_signature_verification_enabled').prop('checked', false);
                }
                
                // Trigger change events to show/hide conditional fields
                $('#universal_payload_structure').trigger('change');
                $('#universal_form_id_source').trigger('change');
                $('#universal_submission_id_source').trigger('change');
                $('#universal_site_id_source').trigger('change');
                $('#universal_signature_verification_enabled').trigger('change');
            } catch (e) {
                console.error('Error parsing Universal provider settings:', e);
            }
        }
        
        // Refresh all selectpickers after setting values
        $('.selectpicker').selectpicker('refresh');
        
        // Update Form ID tooltip based on provider
        updateFormIdHelpText();
        
        // Toggle Universal provider settings visibility
        toggleUniversalProviderSettings();
        
        // Initialize tooltips after a short delay to ensure DOM is ready
        setTimeout(function() {
            $('[data-toggle="tooltip"]').tooltip();
        }, 100);
        
        // Show modal
        $('#addFormConfigModal').modal('show');
    });
    
    // Handle add button click
    $('button[data-target="#addFormConfigModal"]').on('click', function() {
        // Reset form
        $('#addFormConfigModal form')[0].reset();
        $('#config_id').val('');
        $('#modalTitle').text('<?php echo _l('add'); ?> Form Setup');
        $('#modal_enabled').prop('checked', true);
        $('#modal_target_type').val('customer');
        // Set initial state - show customer group, hide lead source
        toggleTargetTypeFields('customer');
        // Update Form ID help text to default
        updateFormIdHelpText();
    });
    
    // Handle target type change
    $('#modal_target_type').on('change', function() {
        toggleTargetTypeFields($(this).val());
    });
    
    // Initialize fields when modal is shown
    $('#addFormConfigModal').on('shown.bs.modal', function() {
        var targetType = $('#modal_target_type').val() || 'customer';
        toggleTargetTypeFields(targetType);
        // Update Form ID tooltip when modal is shown
        updateFormIdHelpText();
        // Reinitialize tooltips in case they were destroyed
        $('[data-toggle="tooltip"]').tooltip();
    });
    
    
    // Handle delete button
    $('._delete').on('click', function(e) {
        e.preventDefault();
        var confirmMsg = $(this).data('confirm');
        var configId = $(this).data('config-id');
        
        if (confirm(confirmMsg)) {
            // Get CSRF token
            var csrf = <?php echo json_encode(get_csrf_for_ajax()); ?>;
            
            var form = $('<form>', {
                'method': 'POST',
                'action': '<?php echo admin_url('form_sync/form_configurations'); ?>'
            });
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'action',
                'value': 'delete'
            }));
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'config_id',
                'value': configId
            }));
            // Add CSRF token
            form.append($('<input>', {
                'type': 'hidden',
                'name': csrf.token_name,
                'value': csrf.hash
            }));
            form.appendTo('body').submit();
        }
    });
    
    // Map Fields button handler
    $(document).on('click', '.map-fields-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        
        // Check if button is disabled using data attribute (more reliable)
        var hasSubmissions = $btn.data('has-submissions');
        if (!hasSubmissions || hasSubmissions == '0' || hasSubmissions == 0) {
            alert_float('warning', 'Please submit at least one form first to enable field mapping.');
            return false;
        }
        
        // Validate required data
        var formId = $btn.data('form-id');
        var targetType = $btn.data('target-type');
        var formName = $btn.data('form-name');
        var provider = $btn.data('provider');
        
        if (!formId || !targetType) {
            alert_float('danger', 'Missing form information. Please refresh the page.');
            return false;
        }
        
        // Open modal
        if (typeof openFieldMappingModal === 'function') {
            openFieldMappingModal(formId, targetType, formName, provider);
        } else {
            alert_float('danger', 'Field mapping function not available. Please refresh the page.');
        }
    });
    
    // Function to open field mapping modal
    function openFieldMappingModal(formId, targetType, formName, provider) {
        // Validate modal exists in DOM
        if (!$('#fieldMappingModal').length) {
            alert_float('danger', 'Field mapping modal not found. Please refresh the page.');
            console.error('Field mapping modal #fieldMappingModal not found in DOM');
            return;
        }
        
        // Reset modal state
        $('#fieldMappingLoading').show();
        $('#fieldMappingContent').hide();
        $('#fieldMappingError').hide();
        
        // Set modal title
        var normalizedTargetType = targetType ? String(targetType).toLowerCase().trim() : 'customer';
        var targetTypeLabel = '<?php echo _l('customer'); ?>';
        if (normalizedTargetType === 'lead') {
            targetTypeLabel = '<?php echo _l('lead'); ?>';
        } else if (normalizedTargetType === 'estimate_request') {
            targetTypeLabel = '<?php echo _l('form_sync_target_type_estimate_request'); ?>';
        } else if (normalizedTargetType === 'ticket') {
            targetTypeLabel = '<?php echo _l('form_sync_target_type_ticket'); ?>';
        }
        $('#fieldMappingModalTitle').text('<?php echo _l('form_sync_field_mapping'); ?> - ' + formName + ' (' + targetTypeLabel + ')');
        
        // Show modal first (before AJAX call)
        try {
            $('#fieldMappingModal').modal('show');
        } catch (error) {
            console.error('Error opening modal:', error);
            alert_float('danger', 'Failed to open modal. Please refresh the page.');
            return;
        }
        
        // Load field mapping data via AJAX
        var ajaxUrl = '<?php echo admin_url('form_sync/get_field_mapping_data'); ?>';
        var ajaxData = {
            form_id: formId,
            target_type: targetType,
            provider: provider || ''
        };
        
        // Debug logging (optional - can be removed in production)
        // console.log('[FormSync] Loading field mapping data:', ajaxData);
        
        $.ajax({
            url: ajaxUrl,
            type: 'GET',
            data: ajaxData,
            dataType: 'json',
            timeout: 30000, // 30 second timeout
            success: function(response) {
                // Debug logging (optional - can be removed in production)
                // console.log('[FormSync] Field mapping response received:', response);
                if (response && response.success) {
                    try {
                        populateFieldMappingModal(response, provider);
                    } catch (e) {
                        console.error('[FormSync] Error in populateFieldMappingModal:', e);
                        $('#fieldMappingLoading').hide();
                        $('#fieldMappingError').text('Error displaying field mapping: ' + e.message).show();
                    }
                } else {
                    $('#fieldMappingLoading').hide();
                    var errorMsg = response && response.message ? response.message : '<?php echo _l('form_sync_field_mapping_error'); ?>';
                    $('#fieldMappingError').text(errorMsg).show();
                }
            },
            error: function(xhr, status, error) {
                console.error('[FormSync] AJAX error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    status_param: status,
                    responseText: xhr.responseText,
                    url: ajaxUrl
                });
                
                $('#fieldMappingLoading').hide();
                var errorMsg = '<?php echo _l('form_sync_field_mapping_error'); ?>: ' + error;
                
                // Handle timeout specifically
                if (status === 'timeout') {
                    errorMsg = 'Request timed out. The server may be taking too long to respond.';
                }
                
                // Try to get more detailed error from response
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        if (jsonResponse.message) {
                            errorMsg = jsonResponse.message;
                        }
                    } catch (e) {
                        // Not JSON, use default error
                        if (xhr.responseText.length > 0) {
                            errorMsg += ' (Response: ' + xhr.responseText.substring(0, 200) + ')';
                        }
                    }
                }
                
                $('#fieldMappingError').text(errorMsg).show();
            }
        });
    }
    
    // Function to populate field mapping modal
    function populateFieldMappingModal(data, provider) {
        // Set form values
        $('#mapping_form_id').val(data.form_id);
        $('#mapping_target_type').val(data.target_type);
        $('#mapping_provider').val(provider || '');
        $('#mapping_form_name_display').val(data.form_name);
        var targetType = data.target_type ? String(data.target_type).toLowerCase().trim() : 'customer';
        var targetTypeLabel = '<?php echo _l('customer'); ?>';
        if (targetType === 'lead') {
            targetTypeLabel = '<?php echo _l('lead'); ?>';
        } else if (targetType === 'estimate_request') {
            targetTypeLabel = '<?php echo _l('form_sync_target_type_estimate_request'); ?>';
        } else if (targetType === 'ticket') {
            targetTypeLabel = '<?php echo _l('form_sync_target_type_ticket'); ?>';
        }
        $('#mapping_target_type_display').val(targetTypeLabel);
        
        // Build side-by-side mapping table (similar to Tally)
        var tableHtml = '<div class="table-responsive">';
        tableHtml += '<table class="table table-striped table-bordered">';
        tableHtml += '<thead><tr>';
        tableHtml += '<th style="width: 45%; background-color: #f5f5f5;"><?php echo _l('form_sync_form_field'); ?></th>';
        tableHtml += '<th style="width: 10%; text-align: center; background-color: #f5f5f5;"><i class="fa fa-arrow-right"></i></th>';
        tableHtml += '<th style="width: 45%; background-color: #f5f5f5;"><?php echo _l('form_sync_perfex_field'); ?></th>';
        tableHtml += '</tr></thead><tbody>';
        
        if (data.form_fields && data.form_fields.length > 0) {
            $.each(data.form_fields, function(index, field) {
                // Ensure all values are strings to prevent .replace() errors
                var fieldId = String(field.key || field.id || '');
                var fieldLabel = String(field.label || field.title || fieldId || '');
                var currentMapping = data.mappings && data.mappings[fieldId] ? String(data.mappings[fieldId]) : 'none';
                
                tableHtml += '<tr>';
                // Form Field Column
                tableHtml += '<td>';
                tableHtml += '<strong>' + escapeHtml(fieldLabel) + '</strong>';
                tableHtml += '<br><small class="text-muted"><code>' + escapeHtml(fieldId) + '</code></small>';
                tableHtml += '</td>';
                // Arrow Column
                tableHtml += '<td style="text-align: center; vertical-align: middle;">';
                tableHtml += '<i class="fa fa-arrow-right text-muted"></i>';
                tableHtml += '</td>';
                // Perfex Field Column
                tableHtml += '<td>';
                tableHtml += '<select name="mappings[' + escapeHtml(fieldId) + ']" class="form-control">';
                tableHtml += '<option value="none"' + (currentMapping === 'none' ? ' selected' : '') + '><?php echo _l('form_sync_perfex_none'); ?></option>';
                
                $.each(data.perfex_fields, function(key, label) {
                    // Ensure key and label are strings
                    var safeKey = String(key || '');
                    var safeLabel = String(label || '');
                    var isSelected = String(currentMapping) === safeKey ? ' selected' : '';
                    tableHtml += '<option value="' + escapeHtml(safeKey) + '"' + isSelected + '>' + escapeHtml(safeLabel) + '</option>';
                });
                
                tableHtml += '</select>';
                tableHtml += '</td>';
                tableHtml += '</tr>';
            });
        } else {
            tableHtml += '<tr><td colspan="3" class="text-center"><p><?php echo _l('form_sync_no_fields_found'); ?></p><p class="text-muted"><small>Submit at least one test form to detect the form fields.</small></p></td></tr>';
        }
        
        tableHtml += '</tbody></table></div>';
        
        // Update table container
        $('#fieldMappingTableContainer').html(tableHtml);
        
        // Show content
        $('#fieldMappingLoading').hide();
        $('#fieldMappingContent').show();
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        // Handle null, undefined, or non-string values
        if (text == null) {
            return '';
        }
        // Convert to string if not already
        text = String(text);
        
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Field mapping form submission
    $('#fieldMappingForm').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('saving'); ?>...');
        
        // Get CSRF token
        var csrf = <?php echo json_encode(get_csrf_for_ajax()); ?>;
        
        // Collect mappings
        var mappings = {};
        $form.find('select[name^="mappings["]').each(function() {
            var fieldId = $(this).attr('name').match(/\[([^\]]+)\]/)[1];
            mappings[fieldId] = $(this).val();
        });
        
        // Build data object with CSRF token
        // Use traditional serialization for CodeIgniter array handling
        var ajaxData = {
            form_id: $('#mapping_form_id').val(),
            target_type: $('#mapping_target_type').val(),
            provider: $('#mapping_provider').val() || ''
        };
        
        // Add mappings with proper array notation for CodeIgniter
        $.each(mappings, function(key, value) {
            ajaxData['mappings[' + key + ']'] = value;
        });
        
        ajaxData[csrf.token_name] = csrf.hash;
        
        // Validate form_id before sending
        var formIdToSend = $('#mapping_form_id').val();
        if (!formIdToSend || formIdToSend.trim() === '') {
            console.error('Form Sync: Form ID is empty!');
            alert_float('danger', 'Form ID is missing. Please refresh the page and try again.');
            $submitBtn.prop('disabled', false).html(originalText);
            return;
        }
        
        // Submit via AJAX
        // Send mappings as JSON string to preserve field IDs with special characters
        var ajaxData = {
            form_id: $('#mapping_form_id').val(),
            target_type: $('#mapping_target_type').val(),
            provider: $('#mapping_provider').val() || '',
            mappings_json: JSON.stringify(mappings) // Send as JSON string
        };
        
        // Add CSRF token
        ajaxData[csrf.token_name] = csrf.hash;
        
        $.ajax({
            url: '<?php echo admin_url('form_sync/save_field_mappings'); ?>',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            timeout: 30000, // 30 second timeout
            success: function(response) {
                // Always re-enable button
                $submitBtn.prop('disabled', false).html(originalText);
                
                if (response && response.success) {
                    alert_float('success', response.message || '<?php echo _l('form_sync_field_mappings_saved'); ?>');
                    
                    // Reload the mapping data to show updated mappings
                    var formId = $('#mapping_form_id').val();
                    var targetType = $('#mapping_target_type').val();
                    var provider = $('#mapping_provider').val() || '';
                    
                    // Show loading state
                    $('#fieldMappingLoading').show();
                    $('#fieldMappingContent').hide();
                    
                    // Reload the field mapping data
                    $.ajax({
                        url: '<?php echo admin_url('form_sync/get_field_mapping_data'); ?>',
                        type: 'GET',
                        data: {
                            form_id: formId,
                            target_type: targetType,
                            provider: provider || ''
                        },
                        dataType: 'json',
                        success: function(reloadResponse) {
                            if (reloadResponse && reloadResponse.success) {
                                populateFieldMappingModal(reloadResponse, provider);
                            } else {
                                console.error('Form Sync: Failed to reload mapping data:', reloadResponse);
                                $('#fieldMappingLoading').hide();
                                $('#fieldMappingError').show().html('<p>' + (reloadResponse.message || 'Failed to reload mapping data') + '</p>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Form Sync: Error reloading mapping data:', error);
                            $('#fieldMappingLoading').hide();
                            $('#fieldMappingError').show().html('<p>Error reloading mapping data. Please close and reopen the modal.</p>');
                        }
                    });
                } else {
                    var errorMsg = (response && response.message) ? response.message : '<?php echo _l('form_sync_field_mapping_error'); ?>';
                    console.error('Form Sync: Save failed:', errorMsg);
                    alert_float('danger', errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Form Sync: AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    status_param: status,
                    responseText: xhr.responseText
                });
                
                // Always re-enable button
                $submitBtn.prop('disabled', false).html(originalText);
                
                var errorMsg = '<?php echo _l('form_sync_field_mapping_error'); ?>';
                
                // Handle timeout
                if (status === 'timeout') {
                    errorMsg = 'Request timed out. Please try again.';
                } else if (status === 'abort') {
                    errorMsg = 'Request was cancelled.';
                } else {
                    // Try to get more detailed error from response
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            var jsonResponse = JSON.parse(xhr.responseText);
                            if (jsonResponse.message) {
                                errorMsg = jsonResponse.message;
                            }
                        } catch (e) {
                            // Not JSON, use default error
                            if (xhr.status === 419) {
                                errorMsg = 'Page expired. Please refresh the page and try again.';
                            } else if (xhr.status === 0) {
                                errorMsg = 'Network error. Please check your connection.';
                            } else {
                                errorMsg += ': ' + error;
                            }
                        }
                    } else {
                        errorMsg += ': ' + error;
                    }
                }
                
                alert_float('danger', errorMsg);
            },
            complete: function() {
                // Ensure button is always re-enabled, even if something goes wrong
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Webflow Secret Management
    $(document).on('click', '.webflow-secret-add, .webflow-secret-edit', function(e) {
        e.preventDefault();
        var configId = $(this).data('config-id');
        var existingSecret = $(this).data('secret') || '';
        var formName = $(this).data('form-name') || '';
        
        $('#webflow_secret_config_id').val(configId);
        $('#webflow_secret_key').val(existingSecret);
        
        // Update modal title if editing
        if (existingSecret) {
            $('#webflowSecretModal .modal-title').text('<?php echo _l('form_sync_webflow_secret_update'); ?>');
        } else {
            $('#webflowSecretModal .modal-title').text('<?php echo _l('form_sync_webflow_secret_modal_title'); ?>');
        }
        
        $('#webflowSecretModal').modal('show');
    });
    
    // Save Webflow Secret
    $('#saveWebflowSecretBtn').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        var configId = $('#webflow_secret_config_id').val();
        var secret = $('#webflow_secret_key').val().trim();
        
        if (!secret) {
            alert_float('warning', '<?php echo _l('form_sync_webflow_secret_required'); ?>');
            return;
        }
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('saving'); ?>...');
        
        // Get CSRF token
        var csrf = <?php echo json_encode(get_csrf_for_ajax()); ?>;
        
        $.ajax({
            url: '<?php echo admin_url('form_sync/save_webflow_secret'); ?>',
            type: 'POST',
            data: {
                config_id: configId,
                webflow_secret: secret,
                [csrf.token_name]: csrf.hash
            },
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                
                if (response && response.success) {
                    alert_float('success', response.message || '<?php echo _l('form_sync_webflow_secret_saved'); ?>');
                    $('#webflowSecretModal').modal('hide');
                    
                    // Update the UI without page reload
                    var $secretCell = $('.webhook-secret-cell[data-config-id="' + configId + '"]');
                    if ($secretCell.length) {
                        // Escape secret for use in HTML attributes
                        var escapedSecret = $('<div>').text(secret).html();
                        
                        // Create copy button
                        var $copyBtn = $('<button>', {
                            type: 'button',
                            class: 'btn btn-sm btn-default',
                            html: '<i class="fa fa-copy"></i> <?php echo _l('form_sync_webhook_secret'); ?>',
                            'data-toggle': 'tooltip',
                            title: '<?php echo _l('form_sync_webhook_secret'); ?>'
                        }).on('click', function() {
                            copyToClipboard(secret, 'webhook_secret');
                        });
                        
                        // Replace content with just the copy button
                        $secretCell.empty().append($copyBtn);
                        
                        // Reinitialize tooltips
                        $('[data-toggle="tooltip"]').tooltip();
                    }
                } else {
                    var errorMsg = (response && response.message) ? response.message : 'Failed to save secret.';
                    alert_float('danger', errorMsg);
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html(originalText);
                
                var errorMsg = 'Failed to save secret.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                alert_float('danger', errorMsg);
            }
        });
    });
    
    // Functions that need jQuery but are called from outside document.ready
    function toggleTargetTypeFields(targetType) {
        // Normalize targetType for robust comparison
        targetType = String(targetType).toLowerCase().trim();
        
        // Hide all conditional fields first
        $('#customer_group_col').hide();
        $('#lead_source_col').hide();
        $('#estimate_request_status_col').hide();
        $('#estimate_request_assigned_col').hide();
        $('#perfex_form_selection_col').hide();
        $('#ticket_department_col').hide();
        $('#ticket_priority_col').hide();
        
        // Remove required attributes from all conditional fields
        $('#modal_customer_group_id').prop('required', false);
        $('#modal_lead_source_id').prop('required', false);
        $('#modal_estimate_request_status_id').prop('required', false);
        $('#modal_perfex_form_id').prop('required', false);
        $('#modal_ticket_department_id').prop('required', false);
        $('#modal_ticket_priority').prop('required', false);
        
        if (targetType === 'customer') {
            $('#customer_group_col').show();
        } else if (targetType === 'lead') {
            $('#lead_source_col').show();
            $('#modal_lead_source_id').prop('required', true);
        } else if (targetType === 'estimate_request') {
            $('#estimate_request_status_col').show();
            $('#estimate_request_assigned_col').show();
            $('#perfex_form_selection_col').show();
            $('#modal_estimate_request_status_id').prop('required', true);
            $('#modal_perfex_form_id').prop('required', true);
        } else if (targetType === 'ticket') {
            $('#ticket_department_col').show();
            $('#ticket_priority_col').show();
            $('#modal_ticket_department_id').prop('required', true);
            $('#modal_ticket_priority').prop('required', true);
        }
        
        // Refresh all selectpickers
        $('.selectpicker').selectpicker('refresh');
        
        // Reinitialize tooltips after selectpicker refresh
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    
    }); // End of $(document).ready
})(jQuery); // End of IIFE - jQuery is available after init_tail()
</script>
