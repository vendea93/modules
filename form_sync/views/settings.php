<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
// #region agent log
file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'settings.php:2','message'=>'View file execution started','data'=>['has_data'=>isset($data),'data_keys'=>isset($data)?array_keys($data):[]],'timestamp'=>time()*1000])."\n", FILE_APPEND);
// #endregion
init_head(); 
// #region agent log
file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'settings.php:6','message'=>'init_head() called','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
// #endregion
?>
<style>
    #menu { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    .provider-accordion .panel-heading {
        background-color: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }
    .provider-accordion .panel-heading a {
        display: block;
        text-decoration: none;
        color: #333;
        font-weight: 600;
    }
    .provider-accordion .panel-heading a:hover {
        color: #0066cc;
    }
    .provider-accordion .panel-heading a i {
        margin-right: 8px;
        transition: transform 0.3s ease;
    }
    .provider-accordion .panel-heading a[aria-expanded="true"] i {
        transform: rotate(90deg);
    }
    .provider-badge-accordion {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 10px;
    }
    .provider-badge-framer {
        background: #0066ff;
        color: white;
    }
    .provider-badge-webflow {
        background: #4353ff;
        color: white;
    }
    .provider-badge-elementor {
        background: #92003b;
        color: white;
    }
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 8px;
    }
    .status-badge-active {
        background: #28a745;
        color: white;
    }
    .status-badge-disabled {
        background: #6c757d;
        color: white;
    }
    .webhook-url-container {
        position: relative;
    }
    .webhook-url-copy-btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #666;
        cursor: pointer;
        padding: 5px 10px;
    }
    .setup-instructions-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
    }
    .setup-instructions-table td {
        padding: 12px 15px;
        vertical-align: top;
        border: none;
    }
    .setup-instructions-table td:first-child {
        width: 50px;
        font-weight: 600;
        color: #0066cc;
        padding-right: 0px;
        padding-left: 0;
        white-space: nowrap;
    }
    .setup-instructions-table tr:not(:last-child) td {
        padding-bottom: 20px;
    }
    .setup-instructions-table tr {
        margin-bottom: 20px;
    }
    .form-group .alert-info {
        border-left: 4px solid #0066cc;
        padding-left: 20px;
    }
    
    /* License Validation Styles */
    .license-panel {
        border: 2px solid #ddd;
        border-radius: 8px;
        margin-bottom: 25px;
    }
    .license-panel.license-valid {
        border-color: #28a745;
    }
    .license-panel.license-invalid {
        border-color: #dc3545;
    }
    .license-panel .panel-heading {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 6px 6px 0 0;
        padding: 15px 20px;
    }
    .license-panel.license-valid .panel-heading {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .license-panel .panel-heading h4 {
        margin: 0;
        font-weight: 600;
    }
    .license-panel .panel-body {
        padding: 25px;
    }
    .license-status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .license-status-valid {
        background: #d4edda;
        color: #155724;
    }
    .license-status-invalid {
        background: #f8d7da;
        color: #721c24;
    }
    .license-details-table {
        width: 100%;
        margin-top: 15px;
    }
    .license-details-table td {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .license-details-table td:first-child {
        font-weight: 600;
        color: #666;
        width: 140px;
    }
    .purchase-code-input {
        font-family: 'Courier New', monospace;
        font-size: 14px;
        letter-spacing: 1px;
    }
    #validateLicenseBtn, #revalidateLicenseBtn {
        min-width: 140px;
    }
    #validateLicenseBtn .spinner, #revalidateLicenseBtn .spinner {
        display: none;
    }
    #validateLicenseBtn.loading .spinner, #revalidateLicenseBtn.loading .spinner {
        display: inline-block;
    }
    #validateLicenseBtn.loading .btn-text, #revalidateLicenseBtn.loading .btn-text {
        display: none;
    }
    .disabled-overlay {
        position: relative;
    }
    .disabled-overlay::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.7);
        z-index: 10;
        pointer-events: all;
    }
    .disabled-overlay .panel-body {
        pointer-events: none;
    }
</style>
<?php 
// #region agent log
file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'settings.php:208','message'=>'Before main content div','data'=>['has_providers'=>isset($providers),'providers_count'=>isset($providers)?count($providers):0],'timestamp'=>time()*1000])."\n", FILE_APPEND);
// #endregion
?>
<div id="wrapper">
    <div class="content-wrapper" style="margin-left: 0;">
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <?php 
                            // #region agent log
                            file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'settings.php:217','message'=>'Inside panel-body, before title','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                            // #endregion
                            ?>
                            <h4 class="no-margin"><?php echo _l('form_sync'); ?> - <?php echo _l('settings'); ?></h4>
                            <hr class="hr-panel-heading" />
                            
                            <!-- License Validation Section -->
                            <div class="license-panel <?php echo (isset($license_valid) && $license_valid) ? 'license-valid' : 'license-invalid'; ?>">
                                <div class="panel-heading">
                                    <h4>
                                        <i class="fa fa-key"></i> 
                                        <?php echo _l('form_sync_license_activation'); ?>
                                        <?php if (isset($license_valid) && $license_valid): ?>
                                            <span class="license-status-badge license-status-valid pull-right">
                                                <i class="fa fa-check-circle"></i> <?php echo _l('form_sync_license_status_valid'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="license-status-badge license-status-invalid pull-right">
                                                <i class="fa fa-times-circle"></i> <?php echo _l('form_sync_license_status_invalid'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </h4>
                                </div>
                                <div class="panel-body">
                                    <?php if (isset($license_valid) && $license_valid): ?>
                                        <!-- License is Valid - Show Details -->
                                        <div class="alert alert-success">
                                            <i class="fa fa-check-circle"></i> 
                                            <strong><?php echo _l('form_sync_license_activated'); ?></strong>
                                            <?php echo _l('form_sync_license_activated_desc'); ?>
                                        </div>
                                        
                                        <?php if (isset($license_details) && $license_details): ?>
                                        <table class="license-details-table">
                                            <tr>
                                                <td><?php echo _l('form_sync_license_code'); ?>:</td>
                                                <td><code><?php echo isset($purchase_code_masked) ? $purchase_code_masked : '****'; ?></code></td>
                                            </tr>
                                            <?php if (!empty($license_details['buyer'])): ?>
                                            <tr>
                                                <td><?php echo _l('form_sync_license_buyer'); ?>:</td>
                                                <td><?php echo htmlspecialchars($license_details['buyer']); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($license_details['license_type'])): ?>
                                            <tr>
                                                <td><?php echo _l('form_sync_license_type'); ?>:</td>
                                                <td><?php echo htmlspecialchars($license_details['license_type']); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($license_details['sold_at'])): ?>
                                            <tr>
                                                <td><?php echo _l('form_sync_license_purchase_date'); ?>:</td>
                                                <td><?php echo date('F j, Y', strtotime($license_details['sold_at'])); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($license_details['supported_until'])): ?>
                                            <tr>
                                                <td><?php echo _l('form_sync_license_support_until'); ?>:</td>
                                                <td>
                                                    <?php echo date('F j, Y', strtotime($license_details['supported_until'])); ?>
                                                    <?php if (isset($support_active)): ?>
                                                        <?php if ($support_active): ?>
                                                            <span class="label label-success"><?php echo _l('form_sync_license_support_active'); ?></span>
                                                        <?php else: ?>
                                                            <span class="label label-warning"><?php echo _l('form_sync_license_support_expired'); ?></span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if (!empty($license_details['validated_at'])): ?>
                                            <tr>
                                                <td><?php echo _l('form_sync_license_validated_at'); ?>:</td>
                                                <td><?php echo date('F j, Y \a\t g:i A', strtotime($license_details['validated_at'])); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                        <?php endif; ?>
                                        
                                        <div class="mtop15">
                                            <button type="button" class="btn btn-danger btn-sm" id="deactivateLicenseBtn">
                                                <i class="fa fa-times"></i> <?php echo _l('form_sync_license_deactivate'); ?>
                                            </button>
                                        </div>
                                        
                                    <?php else: ?>
                                        <!-- License Not Valid - Show Input Form -->
                                        <div class="alert alert-warning">
                                            <i class="fa fa-exclamation-triangle"></i> 
                                            <strong><?php echo _l('form_sync_license_required_title'); ?></strong>
                                            <?php echo _l('form_sync_license_required_desc'); ?>
                                        </div>
                                        
                                        <?php if (!empty($stored_purchase_code)): ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> 
                                            <strong><?php echo _l('form_sync_license_stored_code_found'); ?></strong>
                                            <p><?php echo _l('form_sync_license_stored_code_desc'); ?></p>
                                            <p><strong><?php echo _l('form_sync_license_code'); ?>:</strong> <code><?php echo isset($purchase_code_masked) ? htmlspecialchars($purchase_code_masked) : '****'; ?></code></p>
                                        </div>
                                        <div class="mtop15 mbot15">
                                            <button type="button" class="btn btn-success" id="revalidateLicenseBtn">
                                                <span class="spinner"><i class="fa fa-spinner fa-spin"></i></span>
                                                <span class="btn-text"><i class="fa fa-refresh"></i> <?php echo _l('form_sync_license_revalidate'); ?></span>
                                            </button>
                                            <span class="text-muted" style="margin-left: 10px;"><?php echo _l('form_sync_license_or'); ?></span>
                                        </div>
                                        <hr />
                                        <?php endif; ?>
                                        
                                        <div class="form-group">
                                            <label for="purchase_code" class="control-label">
                                                <?php echo _l('form_sync_license_code'); ?> <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                                <input type="text" 
                                                       class="form-control purchase-code-input" 
                                                       id="purchase_code" 
                                                       name="purchase_code" 
                                                       value="<?php echo !empty($stored_purchase_code) ? htmlspecialchars($stored_purchase_code) : ''; ?>"
                                                       placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                                       maxlength="36"
                                                       autocomplete="off">
                                            </div>
                                            <small class="text-muted">
                                                <?php echo _l('form_sync_license_code_help'); ?>
                                            </small>
                                        </div>
                                        
                                        <div id="licenseValidationResult" style="display: none;" class="mtop10 mbot10"></div>
                                        
                                        <button type="button" class="btn btn-primary" id="validateLicenseBtn">
                                            <span class="spinner"><i class="fa fa-spinner fa-spin"></i></span>
                                            <span class="btn-text"><i class="fa fa-check"></i> <?php echo _l('form_sync_license_validate'); ?></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php echo form_open(admin_url('form_sync/settings')); ?>
                            
                            <!-- Provider Accordions -->
                            <?php $providers_disabled = !(isset($license_valid) && $license_valid); ?>
                            
                            <?php if ($providers_disabled): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-lock"></i> 
                                <strong><?php echo _l('form_sync_providers_locked'); ?></strong>
                                <?php echo _l('form_sync_providers_locked_desc'); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="panel-group provider-accordion <?php echo $providers_disabled ? 'disabled-overlay' : ''; ?>" id="providerAccordion" role="tablist" aria-multiselectable="true">
                                
                                <?php 
                                // Dynamically generate accordions for all providers
                                if (isset($providers) && !empty($providers)) {
                                    foreach ($providers as $provider_id => $provider) {
                                        $provider_enabled = get_option('form_sync_' . $provider_id . '_enabled');
                                        $provider_name = $provider->getName();
                                        $provider_instructions = $provider->getSetupInstructions();
                                ?>
                                
                                <!-- <?php echo $provider_name; ?> Accordion -->
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="<?php echo $provider_id; ?>Heading">
                                        <h4 class="panel-title">
                                            <a role="button" 
                                               data-toggle="collapse" 
                                               data-parent="#providerAccordion" 
                                               href="#<?php echo $provider_id; ?>Accordion" 
                                               aria-expanded="false" 
                                               aria-controls="<?php echo $provider_id; ?>Accordion">
                                                <i class="fa fa-chevron-right"></i> 
                                                <?php echo $provider_name; ?> Integration
                                                <span class="status-badge <?php echo ($provider_enabled == '1') ? 'status-badge-active' : 'status-badge-disabled'; ?>">
                                                    <?php echo ($provider_enabled == '1') ? 'Active' : 'Disabled'; ?>
                                                </span>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="<?php echo $provider_id; ?>Accordion" 
                                         class="panel-collapse collapse" 
                                         role="tabpanel" 
                                         aria-labelledby="<?php echo $provider_id; ?>Heading">
                                        <div class="panel-body">
                                            <!-- Enable <?php echo $provider_name; ?> Integration -->
                                            <div class="form-group">
                                                <div class="checkbox checkbox-primary">
                                                    <input type="checkbox" 
                                                           id="<?php echo $provider_id; ?>_enabled" 
                                                           name="<?php echo $provider_id; ?>_enabled" 
                                                           value="1"
                                                           <?php echo ($provider_enabled == '1') ? 'checked' : ''; ?>>
                                                    <label for="<?php echo $provider_id; ?>_enabled">
                                                        <strong>Enable <?php echo $provider_name; ?> Integration</strong>
                                                    </label>
                                                </div>
                                                <small class="form-text text-muted">
                                                    <?php echo htmlspecialchars($provider->getDescription()); ?>
                                                </small>
                                            </div>
                                            
                                            <hr />
                                            
                                            <!-- <?php echo $provider_name; ?> Setup Instructions -->
                                            <?php if (!empty($provider_instructions)) { ?>
                                            <div class="form-group" style="margin-bottom: 30px;">
                                                <h5>Setup Instructions</h5>
                                                <div class="alert alert-info">
                                                    <?php if (is_array($provider_instructions)) { ?>
                                                        <table class="setup-instructions-table">
                                                            <tbody>
                                                                <?php foreach ($provider_instructions as $index => $instruction) { ?>
                                                                    <tr>
                                                                        <td>Step <?php echo ($index + 1); ?></td>
                                                                        <td><?php echo $instruction; ?></td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    <?php } else { ?>
                                                        <?php echo $provider_instructions; ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php 
                                    }
                                } else {
                                    echo '<div class="alert alert-warning">No providers available.</div>';
                                }
                                ?>
                                
                            </div>
                            
                            <hr />
                            
                            <!-- Save Button -->
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> <?php echo _l('save'); ?>
                                    </button>
                                </div>
                            </div>
                            
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
// FormSync License Validation Script
(function($) {
    'use strict';
    
    // Handle chevron icon rotation on accordion toggle
    $('#providerAccordion').on('show.bs.collapse', function (e) {
        $(e.target).prev('.panel-heading').find('a i').css('transform', 'rotate(90deg)');
    });
    
    $('#providerAccordion').on('hide.bs.collapse', function (e) {
        $(e.target).prev('.panel-heading').find('a i').css('transform', 'rotate(0deg)');
    });
    
    // Update status badges when checkboxes are toggled (dynamic for all providers)
    $('#providerAccordion').on('change', 'input[type="checkbox"][id$="_enabled"]', function() {
        var providerId = $(this).attr('id').replace('_enabled', '');
        var statusBadge = $('#' + providerId + 'Heading').find('.status-badge');
        if ($(this).is(':checked')) {
            statusBadge.removeClass('status-badge-disabled').addClass('status-badge-active').text('Active');
        } else {
            statusBadge.removeClass('status-badge-active').addClass('status-badge-disabled').text('Disabled');
        }
    });
    
    // License Validation Button Click Handler
    $(document).on('click', '#validateLicenseBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var btn = $(this);
        var purchaseCode = $('#purchase_code').val().trim();
        var resultDiv = $('#licenseValidationResult');
        
        if (!purchaseCode) {
            resultDiv.html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo addslashes(_l("form_sync_license_code_required")); ?></div>').show();
            return false;
        }
        
        // Basic format validation
        var codePattern = /^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i;
        if (!codePattern.test(purchaseCode)) {
            resultDiv.html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo addslashes(_l("form_sync_license_invalid_format")); ?></div>').show();
            return false;
        }
        
        // Show loading state
        btn.addClass('loading').prop('disabled', true);
        resultDiv.hide();
        
        // Build data object with CSRF token
        var postData = {
            purchase_code: purchaseCode
        };
        postData['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
        
        // Use admin_url global variable
        var ajaxUrl = admin_url + 'form_sync/validate_purchase_code';
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                btn.removeClass('loading').prop('disabled', false);
                
                if (response.success) {
                    resultDiv.html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + response.message + '</div>').show();
                    // Reload page to show updated license status
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    resultDiv.html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + response.message + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                btn.removeClass('loading').prop('disabled', false);
                resultDiv.html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo addslashes(_l("form_sync_license_connection_error")); ?></div>').show();
            }
        });
        
        return false;
    });
    
    // Allow Enter key to trigger validation
    $(document).on('keypress', '#purchase_code', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#validateLicenseBtn').trigger('click');
            return false;
        }
    });
    
    // License Re-validation
    $(document).on('click', '#revalidateLicenseBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var btn = $(this);
        var resultDiv = $('#licenseValidationResult');
        
        btn.addClass('loading').prop('disabled', true);
        resultDiv.hide();
        
        var revalidateData = {};
        revalidateData['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
        
        $.ajax({
            url: admin_url + 'form_sync/revalidate_license',
            type: 'POST',
            data: revalidateData,
            dataType: 'json',
            success: function(response) {
                btn.removeClass('loading').prop('disabled', false);
                
                if (response.success) {
                    resultDiv.html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + response.message + '</div>').show();
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    resultDiv.html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + response.message + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                btn.removeClass('loading').prop('disabled', false);
                resultDiv.html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo addslashes(_l("form_sync_license_connection_error")); ?></div>').show();
            }
        });
        
        return false;
    });
    
    // License Deactivation
    $(document).on('click', '#deactivateLicenseBtn', function(e) {
        e.preventDefault();
        
        if (!confirm('<?php echo addslashes(_l("form_sync_license_deactivate_confirm")); ?>')) {
            return false;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo addslashes(_l("form_sync_processing")); ?>');
        
        var deactivateData = {};
        deactivateData['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
        
        $.ajax({
            url: admin_url + 'form_sync/deactivate_license',
            type: 'POST',
            data: deactivateData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message);
                    btn.prop('disabled', false).html('<i class="fa fa-times"></i> <?php echo addslashes(_l("form_sync_license_deactivate")); ?>');
                }
            },
            error: function() {
                alert_float('danger', '<?php echo addslashes(_l("form_sync_license_connection_error")); ?>');
                btn.prop('disabled', false).html('<i class="fa fa-times"></i> <?php echo addslashes(_l("form_sync_license_deactivate")); ?>');
            }
        });
        
        return false;
    });
    
})(jQuery);

// Google Forms Apps Script Code Copy Function
function copyGoogleFormsCode(button) {
    var codeBlock = $(button).prev('pre').find('code');
    if (codeBlock.length === 0) {
        codeBlock = $(button).siblings('pre').find('code');
    }
    
    if (codeBlock.length === 0) {
        if (typeof alert_float !== 'undefined') {
            alert_float('warning', 'Code block not found');
        } else {
            alert('Code block not found');
        }
        return;
    }
    
    var codeText = codeBlock.text();
    
    // Create a temporary textarea element
    var tempTextarea = document.createElement('textarea');
    tempTextarea.value = codeText;
    tempTextarea.style.position = 'fixed';
    tempTextarea.style.opacity = '0';
    document.body.appendChild(tempTextarea);
    tempTextarea.select();
    tempTextarea.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        if (typeof alert_float !== 'undefined') {
            alert_float('success', 'Google Apps Script code copied to clipboard!');
        } else {
            alert('Google Apps Script code copied to clipboard!');
        }
        
        // Visual feedback on button
        var originalHtml = $(button).html();
        $(button).html('<i class="fa fa-check"></i> Copied!').prop('disabled', true);
        setTimeout(function() {
            $(button).html(originalHtml).prop('disabled', false);
        }, 2000);
    } catch (err) {
        if (typeof alert_float !== 'undefined') {
            alert_float('danger', 'Failed to copy code');
        } else {
            alert('Failed to copy code');
        }
    } finally {
        document.body.removeChild(tempTextarea);
    }
}
</script>
