<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FormSync Language File
 * 
 * Contains all language strings used by the FormSync module.
 */

$lang['form_sync'] = 'FormSync';
$lang['form_sync_settings'] = 'Settings';
$lang['form_sync_form_configurations'] = 'Form Configurations';
$lang['form_sync_pending_review'] = 'Pending Review';
$lang['form_sync_logs'] = 'Logs';

// Providers
$lang['form_sync_provider'] = 'Provider';
$lang['form_sync_provider_framer'] = 'Framer';
$lang['form_sync_provider_webflow'] = 'Webflow';
$lang['form_sync_provider_google_forms'] = 'Google Forms';
$lang['form_sync_provider_elementor'] = 'Elementor';
$lang['form_sync_provider_universal'] = 'Universal';
$lang['form_sync_provider_select'] = 'Choose where your form is hosted (e.g., Framer, Webflow, Google Forms, or Universal for any provider)';
$lang['form_sync_framer_enabled'] = 'Enable Framer Integration';
$lang['form_sync_webflow_enabled'] = 'Enable Webflow Integration';
$lang['form_sync_google_forms_enabled'] = 'Enable Google Forms Integration';
$lang['form_sync_elementor_enabled'] = 'Enable Elementor Integration';
$lang['form_sync_universal_enabled'] = 'Enable Universal Integration';
$lang['form_sync_framer_enabled_help'] = 'Enable webhook integration with Framer forms.';
$lang['form_sync_webflow_enabled_help'] = 'Enable webhook integration with Webflow forms.';
$lang['form_sync_google_forms_enabled_help'] = 'Enable webhook integration with Google Forms via Google Apps Script.';
$lang['form_sync_elementor_enabled_help'] = 'Enable webhook integration with Elementor forms.';
$lang['form_sync_universal_enabled_help'] = 'Enable Universal webhook integration for any form provider with configurable payload structure.';

// Webhook Settings
$lang['form_sync_webhook_url'] = 'Webhook URL';
$lang['form_sync_webhook_url_help'] = 'Copy this URL and paste it into your form provider\'s webhook configuration.';
$lang['form_sync_webhook_secret'] = 'Webhook Secret';
$lang['form_sync_webhook_secret_help'] = 'Optional security secret for signature verification. Minimum 32 characters.';
$lang['form_sync_webhook_secret_generate'] = 'Generate Secure Secret';
$lang['form_sync_webhook_url_copy'] = 'Copy URL';
$lang['form_sync_webhook_url_copied'] = 'Webhook URL copied to clipboard!';
$lang['form_sync_webhook_secret_copied'] = 'Webhook secret copied to clipboard!';
$lang['form_sync_webhook_base_url'] = 'Webhook Base URL';
$lang['form_sync_webhook_base_url_help'] = 'This is your webhook base URL. Use this when setting up forms.';

// Site Settings
$lang['form_sync_site_name'] = 'Site Name';
$lang['form_sync_site_name_help'] = 'Give this site a name to help you remember it (e.g., "Main Website", "Landing Page")';
$lang['form_sync_site_id'] = 'Site ID';
$lang['form_sync_site_id_help'] = 'Site identifier (auto-populated for Webflow from webhook data).';

// Form Configuration
$lang['form_sync_form_name'] = 'Form Name';
$lang['form_sync_form_name_help'] = 'What should we call this form? (e.g., "Contact Form", "Newsletter Signup")';
$lang['form_sync_form_id'] = 'Form ID';
$lang['form_sync_form_id_help'] = 'The ID number from your form. You can find this in your form settings.';
$lang['form_sync_form_id_help_framer'] = 'The unique identifier for your Framer form. You can find this in your Framer form settings under the form component properties.';
$lang['form_sync_form_id_help_webflow'] = 'The form ID from your Webflow form. This is automatically detected from webhook submissions, but you can also find it in your Webflow form settings.';
$lang['form_sync_form_id_help_google_forms'] = 'A unique identifier for your Google Form (e.g., "contact-form", "newsletter-signup"). This will be used in the webhook URL and should match what you use in your Google Apps Script code.';
$lang['form_sync_form_id_help_elementor'] = 'The unique identifier for your Elementor form. You can find this in your Elementor form settings. This will be auto-detected from webhook submissions.';
$lang['form_sync_form_id_help_universal'] = 'A unique identifier for your form. This will be used in the webhook URL. You can extract it from the URL path (default), payload field, or HTTP header based on your configuration.';
$lang['form_sync_target_type'] = 'Create as';
$lang['form_sync_target_type_lead'] = 'Lead';
$lang['form_sync_target_type_customer'] = 'Customer';
$lang['form_sync_target_type_estimate_request'] = 'Estimate Request';
$lang['form_sync_target_type_ticket'] = 'Support Ticket';
$lang['form_sync_target_type_help'] = 'What should happen when someone fills out this form? Create a new lead, customer, estimate request, or support ticket?';

// Setup Instructions
$lang['form_sync_framer_setup_title'] = 'Framer Setup Instructions';
$lang['form_sync_webflow_setup_title'] = 'Webflow Setup Instructions';
$lang['form_sync_google_forms_setup_title'] = 'Google Forms Setup Instructions';
$lang['form_sync_elementor_setup_title'] = 'Elementor Setup Instructions';
$lang['form_sync_universal_setup_title'] = 'Universal Setup Instructions';
$lang['form_sync_setup_step_1'] = 'Step 1: Create Form Configuration';
$lang['form_sync_setup_step_2'] = 'Step 2: Copy Webhook URL';
$lang['form_sync_setup_step_3'] = 'Step 3: Configure in Provider';
$lang['form_sync_setup_step_4'] = 'Step 4: Map Fields';
$lang['form_sync_setup_step_5'] = 'Step 5: Test Submission';

// Status
$lang['form_sync_status_active'] = 'Active - Receiving submissions';
$lang['form_sync_status_waiting'] = 'Waiting - No submissions yet';
$lang['form_sync_status_error'] = 'Error - Check configuration';
$lang['form_sync_status_disabled'] = 'Disabled';

// Messages
$lang['form_sync_settings_saved'] = 'Settings saved successfully.';
$lang['form_sync_form_config_saved'] = 'Form configuration saved successfully.';
$lang['form_sync_form_config_deleted'] = 'Form configuration deleted successfully.';
$lang['form_sync_webhook_test_success'] = 'Test webhook received successfully!';
$lang['form_sync_webhook_test_failed'] = 'Test webhook failed.';

// Field Mapping
$lang['form_sync_field_mapping'] = 'Field Mapping';
$lang['form_sync_form_field'] = 'Form Field';
$lang['form_sync_perfex_field'] = 'Perfex CRM Field';
$lang['form_sync_perfex_none'] = 'None (Do not map)';
$lang['form_sync_field_mappings_saved'] = 'Field mappings saved successfully.';
$lang['form_sync_field_mapping_error'] = 'Error loading field mapping data.';
$lang['form_sync_no_fields_found'] = 'No form fields found. Please submit a test form first to detect fields.';

// Errors
$lang['form_sync_error_form_config_not_found'] = 'Form configuration not found.';
$lang['form_sync_error_no_field_mappings'] = 'No field mappings configured.';
$lang['form_sync_error_invalid_signature'] = 'Invalid webhook signature.';
$lang['form_sync_error_missing_form_id'] = 'Form ID is required.';

// Target types (for consistency)
$lang['form_sync_target_type_lead'] = 'Lead';
$lang['form_sync_target_type_customer'] = 'Customer';
$lang['form_sync_target_type_estimate_request'] = 'Estimate Request';
$lang['form_sync_target_type_ticket'] = 'Support Ticket';

// Bulk Actions
$lang['form_sync_bulk_actions'] = 'Bulk Actions';
$lang['form_sync_bulk_approve'] = 'Approve Selected';
$lang['form_sync_bulk_ignore'] = 'Ignore Selected';
$lang['form_sync_select_all'] = 'Select All';
$lang['form_sync_selected'] = 'selected';
$lang['form_sync_no_selection'] = 'Please select at least one submission.';
$lang['form_sync_bulk_approve_confirm'] = 'Are you sure you want to approve the selected submissions? This will create the corresponding entities in the CRM.';
$lang['form_sync_bulk_ignore_confirm'] = 'Are you sure you want to ignore the selected submissions? These submissions will be removed from pending review.';
$lang['form_sync_bulk_approve_success'] = 'Submissions approved successfully.';
$lang['form_sync_bulk_approve_partial'] = 'Some submissions were approved, but some failed.';
$lang['form_sync_bulk_approve_failed'] = 'Failed to approve submissions.';
$lang['form_sync_bulk_ignore_success'] = 'Submissions ignored successfully.';
$lang['form_sync_bulk_ignore_partial'] = 'Some submissions were ignored, but some failed.';
$lang['form_sync_bulk_ignore_failed'] = 'Failed to ignore submissions.';
$lang['form_sync_processing'] = 'Processing...';

// Hold Reasons
$lang['form_sync_hold_reason_duplicate'] = 'Duplicate';
$lang['form_sync_hold_reason_no_mappings'] = 'No Mappings';
$lang['form_sync_hold_reason_manual_review'] = 'Manual Review';
$lang['form_sync_hold_reason_none'] = 'None';

// Approval Messages
$lang['form_sync_approve_success'] = 'Submission approved and entity created successfully.';
$lang['form_sync_approve_failed'] = 'Failed to approve submission.';
$lang['form_sync_ignore_success'] = 'Submission ignored.';
$lang['form_sync_ignore_failed'] = 'Failed to ignore submission.';

// Webflow Secret Management
$lang['form_sync_webflow_secret_add'] = 'Add Webflow Secret';
$lang['form_sync_webflow_secret_add_here'] = 'Add here';
$lang['form_sync_webflow_secret_modal_title'] = 'Enter Webflow Secret Key';
$lang['form_sync_webflow_secret_modal_description'] = 'Enter the secret key provided by Webflow. This is required for webhook signature verification.';
$lang['form_sync_webflow_secret_modal_help'] = 'You can find this secret key in your Webflow webhook settings. It may be an OAuth client secret or a secret key from a Site API key.';
$lang['form_sync_webflow_secret_placeholder'] = 'Paste your Webflow secret key here...';
$lang['form_sync_webflow_secret_saved'] = 'Webflow secret key saved successfully.';
$lang['form_sync_webflow_secret_required'] = 'Webflow secret key is required for signature verification.';
$lang['form_sync_webflow_secret_update'] = 'Update Webflow Secret';

// License Validation
$lang['form_sync_license_activation'] = 'License Activation';
$lang['form_sync_license_status_valid'] = 'Activated';
$lang['form_sync_license_status_invalid'] = 'Not Activated';
$lang['form_sync_license_activated'] = 'License Activated!';
$lang['form_sync_license_activated_desc'] = 'Your FormSync module is activated and ready to use.';
$lang['form_sync_license_required_title'] = 'License Required';
$lang['form_sync_license_required_desc'] = 'Please enter your Envato purchase code to activate the FormSync module.';
$lang['form_sync_license_required'] = 'Please activate your license to use FormSync features.';
$lang['form_sync_license_code'] = 'Purchase Code';
$lang['form_sync_license_code_help'] = 'Enter the purchase code from your Envato/CodeCanyon order. You can find this in your Envato downloads page.';
$lang['form_sync_license_code_required'] = 'Please enter your purchase code.';
$lang['form_sync_license_invalid_format'] = 'Invalid purchase code format. Please enter a valid Envato purchase code (e.g., xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).';
$lang['form_sync_license_validate'] = 'Validate License';
$lang['form_sync_license_valid'] = 'License validated successfully! Your FormSync module is now activated.';
$lang['form_sync_license_not_found'] = 'Purchase code not found. Please check your code and try again.';
$lang['form_sync_license_wrong_item'] = 'This purchase code is for a different item. Please use the purchase code for FormSync.';
$lang['form_sync_license_invalid_response'] = 'Invalid response from license server. Please try again later.';
$lang['form_sync_license_connection_error'] = 'Could not connect to the license server. Please check your internet connection and try again.';
$lang['form_sync_license_api_error'] = 'License verification failed. Please try again later or contact support.';
$lang['form_sync_license_rate_limited'] = 'Too many validation attempts. Please wait a few minutes and try again.';
$lang['form_sync_license_buyer'] = 'Registered To';
$lang['form_sync_license_type'] = 'License Type';
$lang['form_sync_license_purchase_date'] = 'Purchase Date';
$lang['form_sync_license_support_until'] = 'Support Until';
$lang['form_sync_license_support_active'] = 'Active';
$lang['form_sync_license_support_expired'] = 'Expired';
$lang['form_sync_license_validated_at'] = 'Validated On';
$lang['form_sync_license_deactivate'] = 'Deactivate License';
$lang['form_sync_license_deactivate_confirm'] = 'Are you sure you want to deactivate this license? You will need to re-enter your purchase code to use the module.';
$lang['form_sync_license_deactivated'] = 'License deactivated successfully.';
$lang['form_sync_license_revalidate'] = 'Re-validate License';
$lang['form_sync_license_stored_code_found'] = 'Stored Purchase Code Found';
$lang['form_sync_license_stored_code_desc'] = 'We found a stored purchase code. You can re-validate it automatically or enter a new code below.';
$lang['form_sync_license_no_stored_code'] = 'No stored purchase code found. Please enter your purchase code.';
$lang['form_sync_license_or'] = 'or enter a new code';
$lang['form_sync_providers_locked'] = 'Provider Settings Locked';
$lang['form_sync_providers_locked_desc'] = 'Please activate your license above to unlock provider settings and start using FormSync.';
$lang['form_sync_processing'] = 'Processing...';

// Estimate Request field labels (used in mapping screen)
$lang['estimate_request_email'] = 'Email';
$lang['estimate_request_name'] = 'Name';
$lang['estimate_request_company'] = 'Company';
$lang['estimate_request_phonenumber'] = 'Phone';
$lang['estimate_request_address'] = 'Address';
$lang['estimate_request_city'] = 'City';
$lang['estimate_request_state'] = 'State';
$lang['estimate_request_zip'] = 'Zip Code';
$lang['estimate_request_country'] = 'Country';
$lang['estimate_request_website'] = 'Website';
$lang['estimate_request_description'] = 'Description';

// Ticket field labels (used in mapping screen)
$lang['ticket_phonenumber'] = 'Phone Number';
$lang['ticket_message'] = 'Message';

// Form configuration labels for new target types
$lang['form_sync_estimate_request_status'] = 'Estimate Request Status';
$lang['form_sync_estimate_request_assigned'] = 'Assign To Staff';
$lang['form_sync_ticket_department'] = 'Support Department';
$lang['form_sync_ticket_priority'] = 'Ticket Priority';
$lang['form_sync_perfex_form'] = 'Perfex CRM Form';
$lang['form_sync_perfex_form_help'] = 'Select an existing Perfex CRM estimate request form to link with this FormSync configuration.';

// Universal Provider Settings
$lang['form_sync_universal_provider_settings'] = 'Universal Provider Settings';
$lang['form_sync_universal_payload_structure'] = 'Payload Structure';
$lang['form_sync_universal_payload_structure_help'] = 'Select how your webhook sends form data. Flat: direct field mapping. Nested: data in payload.data. Array: array of field objects. Custom: specify your own JSON path.';
$lang['form_sync_universal_payload_flat'] = 'Flat (field names as keys)';
$lang['form_sync_universal_payload_nested'] = 'Nested (data in sub-object)';
$lang['form_sync_universal_payload_array'] = 'Array (array of field objects)';
$lang['form_sync_universal_payload_custom'] = 'Custom (specify JSON path)';
$lang['form_sync_universal_payload_auto_detect'] = 'Auto-detect (on first submission)';
$lang['form_sync_universal_payload_structure_flat'] = 'Flat (field names as keys)';
$lang['form_sync_universal_payload_structure_nested'] = 'Nested (data in sub-object)';
$lang['form_sync_universal_payload_structure_array'] = 'Array (array of field objects)';
$lang['form_sync_universal_payload_structure_custom'] = 'Custom (specify JSON path)';
$lang['form_sync_universal_payload_structure_auto'] = 'Auto-detect (on first submission)';
$lang['form_sync_universal_data_path'] = 'Data Path';
$lang['form_sync_universal_data_path_help'] = 'JSON path using dot notation (e.g., payload.data). Required for nested, array, and custom structures. Leave empty for flat structure.';
$lang['form_sync_universal_form_id_source'] = 'Form ID Source';
$lang['form_sync_universal_form_id_source_help'] = 'Where to extract the form ID from. URL: from webhook URL path. Payload: from JSON payload. Header: from HTTP header.';
$lang['form_sync_universal_form_id_url'] = 'URL Path (default)';
$lang['form_sync_universal_form_id_payload'] = 'Payload Field';
$lang['form_sync_universal_form_id_header'] = 'HTTP Header';
$lang['form_sync_universal_form_id_source_url'] = 'URL Path (default)';
$lang['form_sync_universal_form_id_source_payload'] = 'Payload Field';
$lang['form_sync_universal_form_id_source_header'] = 'HTTP Header';
$lang['form_sync_universal_form_id_path'] = 'Form ID Path/Header';
$lang['form_sync_universal_form_id_path_help'] = 'For payload: JSON path (e.g., form_id or payload.formId). For header: header name (e.g., X-Form-ID). Leave empty to use common patterns.';
$lang['form_sync_universal_submission_id_source'] = 'Submission ID Source';
$lang['form_sync_universal_submission_id_source_help'] = 'Where to extract the submission ID from. Auto: generate UUID if not found. Payload: from JSON payload. Header: from HTTP header.';
$lang['form_sync_universal_submission_id_auto'] = 'Auto-generate (default)';
$lang['form_sync_universal_submission_id_payload'] = 'Payload Field';
$lang['form_sync_universal_submission_id_header'] = 'HTTP Header';
$lang['form_sync_universal_submission_id_source_auto'] = 'Auto-generate (default)';
$lang['form_sync_universal_submission_id_source_payload'] = 'Payload Field';
$lang['form_sync_universal_submission_id_source_header'] = 'HTTP Header';
$lang['form_sync_universal_submission_id_path'] = 'Submission ID Path/Header';
$lang['form_sync_universal_submission_id_path_help'] = 'For payload: JSON path (e.g., submission_id or id). For header: header name (e.g., X-Submission-ID).';
$lang['form_sync_universal_site_id_source'] = 'Site ID Source';
$lang['form_sync_universal_site_id_source_help'] = 'Where to extract the site ID from (optional, for multi-site scenarios).';
$lang['form_sync_universal_site_id_none'] = 'None (default)';
$lang['form_sync_universal_site_id_payload'] = 'Payload Field';
$lang['form_sync_universal_site_id_header'] = 'HTTP Header';
$lang['form_sync_universal_site_id_source_none'] = 'None (default)';
$lang['form_sync_universal_site_id_source_payload'] = 'Payload Field';
$lang['form_sync_universal_site_id_source_header'] = 'HTTP Header';
$lang['form_sync_universal_site_id_path'] = 'Site ID Path/Header';
$lang['form_sync_universal_site_id_path_help'] = 'For payload: JSON path (e.g., site_id). For header: header name (e.g., X-Site-ID).';
$lang['form_sync_universal_metadata_fields'] = 'Metadata Fields';
$lang['form_sync_universal_metadata_fields_help'] = 'Comma-separated list of field names to exclude from form data (these are metadata, not form fields).';
$lang['form_sync_universal_signature_verification_enabled'] = 'Enable Signature Verification';
$lang['form_sync_universal_signature_verification_help'] = 'Enable webhook signature verification for security.';
$lang['form_sync_universal_signature_method'] = 'Signature Method';
$lang['form_sync_universal_signature_method_help'] = 'Header: compare secret with header value. HMAC: verify HMAC signature of payload.';
$lang['form_sync_universal_signature_header'] = 'Header Comparison';
$lang['form_sync_universal_signature_hmac'] = 'HMAC Signature';
$lang['form_sync_universal_signature_method_header'] = 'Header Comparison';
$lang['form_sync_universal_signature_method_hmac'] = 'HMAC Signature';
$lang['form_sync_universal_signature_header_name'] = 'Signature Header Name';
$lang['form_sync_universal_signature_header_name_help'] = 'Name of the HTTP header containing the signature.';
$lang['form_sync_universal_settings_saved'] = 'Universal provider settings saved successfully.';
$lang['form_sync_universal_settings_error'] = 'Error saving Universal provider settings.';
$lang['form_sync_universal_auto_detected'] = 'Payload structure auto-detected and saved.';

