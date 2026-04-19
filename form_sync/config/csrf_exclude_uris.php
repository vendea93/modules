<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CSRF Exclusion for FormSync Webhook Endpoints
 * 
 * Webhook endpoints are public and receive POST requests from external providers.
 * They must be excluded from CSRF protection.
 * 
 * @package    FormSync
 * @subpackage Config
 * @category   Module
 */

return [
    // Unified webhook endpoint (supports all providers with form IDs)
    // Matches: form_sync/webhook/framer/{form_id} or form_sync/webhook/webflow
    'form_sync\/webhook\/.+',
    // Backward compatibility endpoints
    'form_sync\/webhook_framer\/.+',
    'form_sync\/webhook_webflow',
];

