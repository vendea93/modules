# FormSync User Guide

<p align="center">
  <strong>Seamlessly sync form submissions with Perfex CRM</strong>
</p>

<p align="center">
  Version 1.0.0 | Created by <a href="https://liquidapps.studio">LiquidApps Studio</a>
</p>

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Requirements](#2-system-requirements)
3. [Installation](#3-installation)
4. [Providers](#4-providers)
5. [Framer Integration & FAQ](#5-framer-integration--faq)
6. [Webflow Integration & FAQ](#6-webflow-integration--faq)
7. [Updates (Changelog)](#7-updates-changelog)

---

## 1. Introduction

### 1.1 What is FormSync?

FormSync is a powerful Perfex CRM module that automatically connects your website forms to Perfex CRM. When visitors submit forms on your Framer or Webflow website, FormSync instantly captures that data and creates **Leads** or **Customers** in your CRM system — no manual data entry required.

### 1.2 How It Works

FormSync uses **webhook technology** to receive form submissions in real-time:

```
┌─────────────────────┐
│   Your Website      │
│   (Framer/Webflow)  │
│   Form Submission   │
└──────────┬──────────┘
           │
           │ Webhook (HTTP POST)
           │ Instant Delivery
           ▼
┌─────────────────────┐
│   FormSync          │
│   - Receives Data   │
│   - Maps Fields     │
│   - Checks Dupes    │
└──────────┬──────────┘
           │
           │ Create Record
           ▼
┌─────────────────────┐
│   Perfex CRM        │
│   Lead or Customer  │
│   Created!          │
└─────────────────────┘
```

### 1.3 Key Features

| Feature | Description |
|---------|-------------|
| **Real-Time Sync** | Form submissions are processed instantly via webhooks |
| **Multi-Provider Support** | Works with Framer, Webflow, and more providers coming soon |
| **Automatic Lead/Customer Creation** | Submissions automatically become Leads or Customers |
| **Flexible Field Mapping** | Map any form field to any CRM field with an intuitive interface |
| **Duplicate Detection** | Automatically identifies duplicates by email/phone |
| **Pending Review System** | Review and approve duplicate submissions manually |
| **Comprehensive Logging** | Track every submission with detailed status and error info |
| **Webhook Security** | Optional signature verification for enhanced security |
| **Multi-Form Support** | Connect unlimited forms from multiple projects |

### 1.4 Use Cases

- **Contact Forms** → Automatically create leads from website inquiries
- **Newsletter Signups** → Capture subscriber information as customers
- **Quote Request Forms** → Generate leads with project details
- **Event Registration** → Create customer records with event preferences
- **Support Requests** → Log inquiries as leads for follow-up

---

## 2. System Requirements

### 2.1 Server Requirements

| Requirement | Minimum Version |
|-------------|-----------------|
| **Perfex CRM** | 2.3.* or higher |
| **PHP** | 7.4 or higher |
| **MySQL** | 5.7+ or MariaDB 10.2+ |
| **Web Server** | Apache 2.4+ or Nginx 1.18+ |
| **mod_rewrite** | Enabled (for clean URLs) |
| **SSL Certificate** | Recommended for webhook security |

### 2.2 Required PHP Extensions

| Extension | Purpose |
|-----------|---------|
| **cURL** | Required for webhook communication |
| **JSON** | Required for processing webhook payloads |
| **OpenSSL** | Required for webhook signature verification |
| **mbstring** | Required for string processing |

> **Note:** In most hosting environments, these PHP extensions are enabled by default. Contact your hosting provider if you're unsure.

### 2.3 Browser Requirements

FormSync's admin interface works with all modern browsers:

- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

### 2.4 Form Provider Requirements

**For Framer:**
- Active Framer account
- Published Framer project with forms
- Access to form component settings

**For Webflow:**
- Active Webflow account
- Published Webflow site with forms
- Access to Site Settings → Webhooks

---

## 3. Installation

### 3.1 Prerequisites

Before installing FormSync, ensure you have:

- ✅ Perfex CRM installed and running
- ✅ Admin access to your Perfex CRM installation
- ✅ The FormSync module ZIP file downloaded

### 3.2 Step-by-Step Installation

#### Step 1: Upload the Module

1. Log in to your **Perfex CRM admin panel**
2. Navigate to **Setup → Modules → Module Manager**
3. Click **"Upload Module"** or **"Install from ZIP"**
4. Select the FormSync module ZIP file
5. Wait for the upload to complete

#### Step 2: Activate the Module

1. In the Module Manager, find **FormSync** in the list
2. Click the **"Activate"** button
3. Wait for activation to complete
4. You should see a success message

#### Step 3: Verify Installation

After activation, verify the installation:

1. Navigate to **Setup** in the admin sidebar
2. Look for **FormSync** menu item
3. You should see the following sub-menu items:
   - Settings
   - Form Configurations
   - Pending Review
   - Logs

If you see these menu items, installation was successful! 🎉

### 3.3 Post-Installation Setup

After installation, complete these initial setup steps:

1. **Enable your provider(s):**
   - Go to **Setup → FormSync → Settings**
   - Enable Framer and/or Webflow integration
   - Click **Save**

2. **Create your first form configuration:**
   - Go to **Setup → FormSync → Form Configurations**
   - Click **Add** to create a new configuration
   - Follow the provider-specific guide below

### 3.4 Troubleshooting Installation

| Issue | Solution |
|-------|----------|
| Module not appearing | Check file permissions (755 for folders, 644 for files) |
| Activation fails | Verify PHP version meets requirements |
| Database errors | Ensure database user has CREATE TABLE permission |
| Menu items missing | Clear browser cache and refresh the page |

---

## 4. Providers

### 4.1 What is a Provider?

A **provider** is the platform where your forms are hosted (e.g., Framer, Webflow). FormSync supports multiple providers, each with its own configuration and webhook handling.

### 4.2 Supported Providers

| Provider | Status | Description |
|----------|--------|-------------|
| **Framer** | ✅ Fully Supported | Sync forms from Framer websites |
| **Webflow** | ✅ Fully Supported | Sync forms from Webflow sites |
| *More coming soon* | 🔜 Planned | Additional providers in development |

### 4.3 Provider Settings

Each provider can be enabled or disabled independently:

1. Go to **Setup → FormSync → Settings**
2. Find the provider section (Framer or Webflow)
3. Check/uncheck **"Enable [Provider] Integration"**
4. Click **Save**

> **Important:** A provider must be enabled before you can receive webhooks from that platform.

### 4.4 Multi-Provider Setup

FormSync supports using multiple providers simultaneously:

- Enable Framer for your marketing landing pages
- Enable Webflow for your main company website
- Each form gets its own configuration and webhook URL
- Manage all submissions from one central dashboard

### 4.5 Creating Form Configurations

For each form you want to connect, create a **Form Configuration**:

1. Go to **Setup → FormSync → Form Configurations**
2. Click **Add**
3. Fill in the required fields:
   - **Provider:** Select Framer or Webflow
   - **Form Name:** A descriptive name (e.g., "Contact Form")
   - **Form ID:** A unique identifier (e.g., "contact" or "CON1")
   - **Target Type:** Lead or Customer
4. Click **Save**

After saving, you'll see:
- **Webhook URL** - Copy this to configure in your form provider
- **Webhook Secret** - Optional security key (copy if configured)

---

## 5. Framer Integration & FAQ

### 5.1 About Framer Integration

Framer Integration connects your Framer website forms directly to Perfex CRM. When someone submits a form on your Framer site, the data is instantly sent to your CRM.

### 5.2 Quick Setup Guide

#### Step 1: Enable Framer Integration

1. Go to **Setup → FormSync → Settings**
2. Find the **Framer** section
3. Check **"Enable Framer Integration"**
4. Click **Save**

#### Step 2: Create a Form Configuration

1. Go to **Setup → FormSync → Form Configurations**
2. Click **Add**
3. Fill in:
   - **Provider:** Select "Framer"
   - **Form Name:** e.g., "Contact Form"
   - **Form ID:** e.g., "Contact" (keep it simple)
   - **Target Type:** "Lead" or "Customer"
   - **Webhook Secret:** Optional (recommended for security)
   - **Enabled:** ✓ Check this box
4. Click **Save**

#### Step 3: Copy the Webhook URL

1. Find your configuration in the list
2. Click the **copy button** (📋) next to the Webhook URL
3. The URL looks like: `https://yourdomain.com/form_sync/webhook/framer/Contact`

#### Step 4: Configure Webhook in Framer

1. Open your Framer project
2. Select the form component you want to connect
3. In form settings, find **"Send To"**
4. Click **"Add..."** next to "Send To"
5. Select **"Webhook"**
6. Paste the Webhook URL you copied
7. (Optional) Add the webhook secret if configured
8. Save your Framer project

#### Step 5: Map Your Fields

1. Go back to **Form Configurations** in Perfex CRM
2. Click the **chain icon** (🔗) next to your form
3. Wait for fields to load
4. Map each form field to a CRM field:
   - `email` → Email
   - `name` → First Name (for Customers) or Name (for Leads)
   - `phone` → Phone Number
   - `company` → Company
5. Click **Save**

> **Note:** If no fields appear, submit a test form first. Fields are discovered from actual submissions.

#### Step 6: Test Your Setup

1. Submit a test form on your Framer website
2. Go to **Setup → FormSync → Logs**
3. Verify your submission appears with **"Success"** status
4. Check that a Lead/Customer was created in your CRM

### 5.3 Framer Webhook URL Format

```
https://yourdomain.com/form_sync/webhook/framer/{form_id}
```

**Example:**
```
https://mycompany.com/form_sync/webhook/framer/Contact
```

### 5.4 Framer Data Format

Framer sends form data in a **flat structure**:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "123-456-7890",
  "message": "Hello, I'm interested in your services!"
}
```

### 5.5 Framer Webhook Headers

When Framer sends a webhook, it includes these headers:

| Header | Description |
|--------|-------------|
| `Framer-Signature` | HMAC signature for verification |
| `Framer-Webhook-Submission-Id` | Unique submission identifier |

### 5.6 Framer FAQ

<details>
<summary><strong>Q: Where do I find the Form ID in Framer?</strong></summary>

**A:** The Form ID is something you create yourself when setting up the form configuration in Perfex CRM. It's not from Framer — it's a unique identifier you choose (like "Contact" or "Newsletter"). This ID becomes part of your webhook URL.
</details>

<details>
<summary><strong>Q: Do I need to install anything on my Framer site?</strong></summary>

**A:** No installation is needed on Framer. You only need to configure the webhook URL in your form's settings. Framer handles sending the data automatically.
</details>

<details>
<summary><strong>Q: Is the webhook secret required?</strong></summary>

**A:** No, the webhook secret is optional but recommended for security. If you don't set one, the integration will still work. Adding a secret provides an extra layer of security to verify submissions are coming from Framer.
</details>

<details>
<summary><strong>Q: Why aren't my form fields appearing in the mapping interface?</strong></summary>

**A:** The system discovers fields from actual submissions. If no fields appear:
1. Submit a test form on your Framer website
2. Wait a few moments
3. Go back to Form Configurations
4. Click the chain icon again — fields should now appear
</details>

<details>
<summary><strong>Q: Can I connect multiple Framer forms?</strong></summary>

**A:** Yes! Create a separate form configuration for each form. Each form gets its own Form ID, webhook URL, and field mappings.
</details>

<details>
<summary><strong>Q: What happens if someone submits the same form twice?</strong></summary>

**A:** FormSync automatically detects duplicates by email address. The second submission will be held in **Pending Review** for you to approve or ignore.
</details>

<details>
<summary><strong>Q: My submissions are showing as "Failed" — what's wrong?</strong></summary>

**A:** Check these common causes:
1. **Field mappings not configured** — Set up field mappings in Form Configurations
2. **Provider not enabled** — Enable Framer in Settings
3. **Form configuration disabled** — Ensure "Enabled" is checked
4. View the error message in Logs for specific details
</details>

<details>
<summary><strong>Q: Can I send form data to both Framer Forms and Perfex CRM?</strong></summary>

**A:** Yes! Framer allows multiple destinations for form submissions. You can keep Framer's built-in form handling and add the FormSync webhook as an additional destination.
</details>

---

## 6. Webflow Integration & FAQ

### 6.1 About Webflow Integration

Webflow Integration connects your Webflow site forms to Perfex CRM. Form submissions are sent via Webflow's native webhook system to your CRM.

### 6.2 Quick Setup Guide

#### Step 1: Enable Webflow Integration

1. Go to **Setup → FormSync → Settings**
2. Find the **Webflow** section
3. Check **"Enable Webflow Integration"**
4. Click **Save**

#### Step 2: Create a Form Configuration

1. Go to **Setup → FormSync → Form Configurations**
2. Click **Add**
3. Fill in:
   - **Provider:** Select "Webflow"
   - **Form Name:** e.g., "Contact Form"
   - **Form ID:** e.g., "WF-Contact" (unique identifier)
   - **Site Name:** Optional, for organization
   - **Target Type:** "Lead" or "Customer"
   - **Webhook Secret:** Optional (for signature verification)
   - **Enabled:** ✓ Check this box
4. Click **Save**

#### Step 3: Copy the Webhook URL

1. Find your configuration in the list
2. Click the **copy button** (📋) next to the Webhook URL
3. The URL looks like: `https://yourdomain.com/form_sync/webhook/webflow/WF-Contact`

#### Step 4: Configure Webhook in Webflow

1. Log in to your Webflow dashboard
2. Go to your site's **Site Settings**
3. Navigate to the **Integrations** tab
4. Scroll down to **Webhooks** section
5. Click **"+ Add Webhook"**
6. Configure the webhook:
   - **Trigger Type:** Select "Form Submission"
   - **Webhook URL:** Paste the URL you copied
7. Click **Save**

#### Step 5: Map Your Fields

1. Go back to **Form Configurations** in Perfex CRM
2. Click the **chain icon** (🔗) next to your form
3. Wait for fields to load (submit a test form first if needed)
4. Map each form field to a CRM field
5. Click **Save**

#### Step 6: Test Your Setup

1. Submit a test form on your published Webflow site
2. Go to **Setup → FormSync → Logs**
3. Verify your submission appears with **"Success"** status
4. Check that a Lead/Customer was created in your CRM

### 6.3 Webflow Webhook URL Format

```
https://yourdomain.com/form_sync/webhook/webflow/{form_id}
```

**Example:**
```
https://mycompany.com/form_sync/webhook/webflow/WF-Contact
```

### 6.4 Webflow Data Format

Webflow sends form data in a **nested structure**:

```json
{
  "payload": {
    "formId": "abc123",
    "siteId": "xyz789",
    "id": "submission_001",
    "data": {
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "123-456-7890",
      "message": "Hello, I'm interested!"
    }
  }
}
```

FormSync automatically extracts the form data from the nested `payload.data` structure.

### 6.5 Webflow Webhook Headers

When Webflow sends a webhook, it includes these headers:

| Header | Description |
|--------|-------------|
| `x-webflow-signature` | HMAC signature for verification |
| `x-webflow-timestamp` | Timestamp for signature verification |

### 6.6 Webflow FAQ

<details>
<summary><strong>Q: Where do I find my Webflow Form ID?</strong></summary>

**A:** The Form ID is something you create yourself when setting up the form configuration in Perfex CRM. Alternatively, Webflow includes the form ID in webhook payloads automatically — you can find it in the Logs after your first submission.
</details>

<details>
<summary><strong>Q: Do I need a paid Webflow plan for webhooks?</strong></summary>

**A:** Webflow's webhook feature availability depends on your plan. Check Webflow's current pricing and features to confirm webhook support for your plan level.
</details>

<details>
<summary><strong>Q: How do I set up webhook signature verification?</strong></summary>

**A:** 
1. In Perfex CRM, add a webhook secret to your form configuration
2. In Webflow, when creating the webhook, you may need to configure the secret in your Webflow Site Settings
3. Both secrets must match exactly for verification to work
</details>

<details>
<summary><strong>Q: Can I connect multiple Webflow sites?</strong></summary>

**A:** Yes! Create separate form configurations for each site/form combination. Use the **Site Name** field to organize and identify which configuration belongs to which site.
</details>

<details>
<summary><strong>Q: What if I don't see the webhook option in Webflow?</strong></summary>

**A:** Make sure you're in your site's **Site Settings** (not project settings). Navigate to the **Integrations** tab and scroll down to find the Webhooks section. If it's not available, check your Webflow plan features.
</details>

<details>
<summary><strong>Q: My Webflow submissions aren't appearing — what should I check?</strong></summary>

**A:** 
1. Verify Webflow Integration is enabled in FormSync Settings
2. Check the webhook URL is correctly pasted in Webflow
3. Ensure the form configuration is enabled
4. Make sure you're testing on the published site (not the designer preview)
5. Check the Logs page for any error messages
</details>

<details>
<summary><strong>Q: Does FormSync work with Webflow's native form notifications?</strong></summary>

**A:** Yes! Webflow webhooks are independent of Webflow's built-in form notifications. You can keep both running — receive email notifications from Webflow AND create CRM records via FormSync.
</details>

<details>
<summary><strong>Q: What's the difference between Site ID and Form ID?</strong></summary>

**A:** 
- **Site ID:** Identifies your Webflow site (auto-detected from webhook payload)
- **Form ID:** Identifies a specific form on your site (you create this or use Webflow's form ID)
</details>

---

## 7. Updates (Changelog)

### 7.1 How to Update FormSync

**Before updating:**
1. ✅ Backup your Perfex CRM database
2. ✅ Backup the FormSync module files
3. ✅ Review the changelog for any breaking changes
4. ✅ Note any special update instructions

**Update process:**
1. Download the latest version of FormSync
2. Deactivate the current FormSync module in Perfex CRM
3. Upload new module files (overwrite existing)
4. Activate the module again
5. Clear your browser cache
6. Test with a sample form submission

> **Important:** Always backup your database before updating. Your existing form configurations, field mappings, and logs will be preserved during updates.

### 7.2 Version History

---

### Version 1.0.0 (December 2024)

**🚀 Initial Release**

#### New Features
- **Real-time webhook processing** for Framer and Webflow forms
- **Automatic Lead/Customer creation** based on form configuration
- **Field mapping system** with intuitive visual interface
- **Duplicate detection** by email and phone number
- **Pending Review system** for duplicate management
- **Comprehensive submission logging** with status tracking
- **Webhook signature verification** (SHA-256 HMAC) for security
- **Multi-form support** with independent configurations per form
- **Multi-provider architecture** — easily extensible for new providers

#### Admin Interface
- **Settings page** — Enable/disable providers, view status
- **Form Configurations** — Manage form integrations with copy buttons for URLs
- **Pending Review** — Review and approve/ignore held submissions
- **Logs page** — View all submissions with filtering and retry options

#### Technical Features
- Automatic field discovery from form submissions
- Provider-specific webhook handling
- Robust error handling and logging
- Database migration support for future updates
- Protection against webhook leads becoming customers prematurely

---

### 7.3 Planned Features

Future versions may include:

- 📌 Additional providers (Typeform, Jotform, Google Forms, etc.)
- 📌 Custom field support for leads and customers
- 📌 Advanced duplicate matching rules
- 📌 Automated actions (send emails, assign staff, etc.)
- 📌 API endpoint for programmatic access
- 📌 Submission analytics and reporting

---

### 7.4 Troubleshooting After Updates

If you encounter issues after updating:

| Issue | Solution |
|-------|----------|
| Settings not loading | Clear browser cache (Ctrl+F5 / Cmd+Shift+R) |
| Form configurations missing | Check database connectivity |
| Webhook URLs changed | Re-copy URLs and update in provider |
| Permissions errors | Re-save staff permissions in Setup |
| Module not activating | Check PHP version compatibility |

---

## Appendix A: Field Mapping Reference

### Lead Fields

| Perfex Field | Description |
|--------------|-------------|
| `name` | Lead Name |
| `company` | Company Name |
| `email` | Email Address |
| `phonenumber` | Phone Number |
| `address` | Street Address |
| `city` | City |
| `state` | State/Province |
| `zip` | ZIP/Postal Code |
| `country` | Country |
| `website` | Website URL |
| `description` | Description/Notes |
| `title` | Job Title |

### Customer Fields

| Perfex Field | Description |
|--------------|-------------|
| `company` | Company Name |
| `firstname` | First Name |
| `lastname` | Last Name |
| `email` | Email Address |
| `phonenumber` | Phone Number |
| `address` | Street Address |
| `city` | City |
| `state` | State/Province |
| `zip` | ZIP/Postal Code |
| `country` | Country |
| `website` | Website URL |
| `vat` | VAT Number |

---

## Appendix B: Glossary

| Term | Definition |
|------|------------|
| **Webhook** | HTTP callback that delivers real-time data when an event occurs |
| **Provider** | Platform where forms are hosted (Framer, Webflow) |
| **Form Configuration** | Settings that link a specific form to FormSync |
| **Field Mapping** | Connection between form fields and CRM fields |
| **Payload** | Data sent in a webhook request |
| **Form ID** | Unique identifier for a form configuration |
| **Submission ID** | Unique identifier for a form submission |
| **Site ID** | Unique identifier for a website (Webflow) |
| **Target Type** | Whether to create a Lead or Customer |
| **Duplicate Detection** | Process of identifying existing records |
| **Hold Status** | Submission held for manual review |
| **HMAC** | Hash-based Message Authentication Code |

---

## Appendix C: Support

### Getting Help

1. **Check this documentation** — Most answers are here
2. **Review the Logs page** — Error messages often point to solutions
3. **Check Troubleshooting sections** — Common issues and fixes
4. **Contact support** — If issues persist

### When Contacting Support

Please include:
- Perfex CRM version
- FormSync module version
- Description of the issue
- Steps to reproduce
- Error messages from Logs
- Screenshots if applicable

---

<p align="center">
  <strong>FormSync</strong> by LiquidApps Studio<br>
  <a href="https://liquidapps.studio">liquidapps.studio</a>
</p>

---

*Last Updated: December 2024 | Version 1.0.0*










