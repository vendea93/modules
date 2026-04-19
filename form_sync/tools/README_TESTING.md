# FormSync Testing Guide

This directory contains testing tools for the FormSync module.

## Tools

### 1. `validate_installation.php`
Validates that the module is properly installed and all database tables/columns exist.

**Usage:**
```bash
php modules/form_sync/tools/validate_installation.php
```

**What it checks:**
- Module is installed and active
- All required database tables exist
- All required columns exist
- Module options are set

### 2. `simulate_framer_webhook.php`
Sends a single test webhook request to test the webhook endpoint.

**Usage:**
```bash
php modules/form_sync/tools/simulate_framer_webhook.php --form-id=CON1 --url=http://localhost:8080 --secret=YOUR_SECRET
```

**Options:**
- `--form-id=ID` (required) - Form ID from your form configuration
- `--url=URL` (optional) - Base URL (default: http://localhost:8080)
- `--secret=SECRET` (optional) - Webhook secret for signature
- `--data=JSON` (optional) - JSON string with form data
- `--submission-id=ID` (optional) - Submission ID (default: auto-generated)

**Examples:**
```bash
# Basic test
php modules/form_sync/tools/simulate_framer_webhook.php --form-id=CON1 --secret=abc123

# Test with custom data
php modules/form_sync/tools/simulate_framer_webhook.php --form-id=CON1 --secret=abc123 --data='{"name":"John Doe","email":"john@example.com"}'
```

### 3. `send_multiple_test_webhooks.php`
Sends multiple test webhook requests with different data to test the entire flow.

**Usage:**
```bash
php modules/form_sync/tools/send_multiple_test_webhooks.php --form-id=CON1 --secret=YOUR_SECRET --count=10
```

**Options:**
- `--form-id=ID` (required) - Form ID from your form configuration
- `--secret=SECRET` (required) - Webhook secret for signature
- `--url=URL` (optional) - Base URL (default: http://localhost:8080)
- `--count=N` (optional) - Number of test submissions (default: 10)

**Examples:**
```bash
# Send 10 test submissions
php modules/form_sync/tools/send_multiple_test_webhooks.php --form-id=CON1 --secret=abc123

# Send 20 test submissions
php modules/form_sync/tools/send_multiple_test_webhooks.php --form-id=CON1 --secret=abc123 --count=20

# Test against different URL
php modules/form_sync/tools/send_multiple_test_webhooks.php --form-id=CON1 --secret=abc123 --url=http://localhost:3000
```

## Testing Workflow

### 1. Validate Installation
First, ensure the module is properly installed:

```bash
php modules/form_sync/tools/validate_installation.php
```

If there are errors, deactivate and reactivate the module in Perfex CRM admin panel.

### 2. Configure Form
1. Go to **FormSync > Form Configurations** in Perfex CRM
2. Create or edit a form configuration
3. Set the form ID (e.g., `CON1`)
4. Set the webhook secret
5. Enable the form
6. Configure field mappings (optional for initial testing)

### 3. Send Test Webhooks

**Single test:**
```bash
php modules/form_sync/tools/simulate_framer_webhook.php --form-id=CON1 --secret=YOUR_SECRET
```

**Multiple tests:**
```bash
php modules/form_sync/tools/send_multiple_test_webhooks.php --form-id=CON1 --secret=YOUR_SECRET --count=10
```

### 4. Verify Results

1. **Check Pending Review:**
   - Go to **FormSync > Pending Review**
   - You should see submissions waiting for approval

2. **Check Logs:**
   - Go to **FormSync > Logs**
   - Verify submissions are logged with correct status

3. **Approve Submissions:**
   - In **Pending Review**, click "Approve" on submissions
   - Verify success messages

4. **Check Leads/Customers:**
   - Go to **Leads** or **Customers** in Perfex CRM
   - Verify that approved submissions created leads/customers
   - Check that leads are visible (not filtered out)

## Troubleshooting

### Webhook returns 403 Forbidden
- Check CSRF exclusions are configured
- Verify webhook URL is correct
- Check server logs for CSRF errors

### Webhook returns 302 Redirect
- Ensure webhook requests bypass authentication
- Check controller constructor logic

### Submissions not appearing in Pending Review
- Check form configuration is enabled
- Verify webhook secret matches
- Check FormSync logs for errors

### Leads not visible after approval
- Check lead visibility settings (`is_public`, `addedfrom`)
- Verify lead was actually created (check logs)
- Run the visibility fix script if needed

### Module installation errors
- Check database permissions
- Verify all required tables can be created
- Check PHP error logs
- Run `validate_installation.php` to identify issues

## Test Data

The `send_multiple_test_webhooks.php` script includes various test data templates:
- Business leads with company information
- Personal leads without company
- International leads with different formats
- Edge cases (special characters, timestamps)

Each submission gets a unique email address to avoid duplicate detection.

## Notes

- Test scripts require PHP CLI with curl support
- Make sure your local server is running before testing
- Webhook secret must match the one configured in form settings
- Form ID must match the one configured in form settings
- All test submissions will be held for manual review by default











