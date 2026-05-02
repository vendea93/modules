# Raporty modułów Perfex + FQ SaaS

Data: 2026-04-28

## 1) Źródłowe pliki raportów

Poniżej komplet plików raportowych wygenerowanych podczas instalacji, aktywacji, testów i rollbacków:

- `/tmp/fq_stageA_report_final.tsv`
- `/tmp/fq_stageA_install_actions.tsv`
- `/tmp/fq_stageB_report.tsv`
- `/tmp/fq_restore_endpoints.tsv`
- `/tmp/fq_fail_cats.tsv`
- `/tmp/fq_fix_report.tsv`
- `/tmp/fq_fix_final_endpoints.tsv`
- `/tmp/fq_fix_pass.txt`
- `/tmp/fq_fix_fail.txt`

## 2) Podsumowanie etapów

### Etap A (instalacja)

- `ok=121`

### Etap B (pełny przebieg 1-121)

- `PASS=36`
- `FAIL=85`

### Etap „łatwe -> średnie”

- `PASS=36`
- `FAIL=48`

## 3) Status endpointów (po restore)

Źródło: `/tmp/fq_restore_endpoints.tsv`

```tsv
endpoint	code
master	200
go	200
demo	200
hotel	200
logistyka	200
warsztat	200
nieruchomosci	200
kursy	200
ecommerce	200
serwiswww	200
oze	200
agencja	200
rekrutacja	200
medycyna	200
eventy	200
gastronomia	200
beauty	200
```

## 4) Status endpointów (po turze „łatwe -> średnie”)

Źródło: `/tmp/fq_fix_final_endpoints.tsv`

```tsv
endpoint	code
master	200
go	200
demo	200
hotel	500
logistyka	500
warsztat	500
nieruchomosci	200
kursy	200
ecommerce	500
serwiswww	200
oze	200
agencja	200
rekrutacja	500
medycyna	200
eventy	500
gastronomia	500
beauty	200
```

## 5) Moduły PASS/FAIL (tura „łatwe -> średnie”)

Źródła:
- PASS: `/tmp/fq_fix_pass.txt`
- FAIL: `/tmp/fq_fix_fail.txt`

### PASS (36)

- file_sharing
- mailflow
- purchase
- einvoice
- exports
- extended_email
- flexform
- flexibleleadfinder
- flexibleleadscore
- flexstage
- gocardless_gateway
- google_analytic
- hosting_manager
- idea_hub
- ideal
- invoices_builder
- lead_manager
- mercadopago_gateway
- otpless
- perfex_dashboard
- perfex_email_builder
- perfex_office_theme
- perfex_popup
- prchat
- projectroadmap
- purchase_orders
- si_custom_status
- support_contact
- surveys
- taskbookmarks
- task_signing
- telegram_chat
- website_maintenance_management
- wiki
- xml_exports
- zillapage

### FAIL (48)

- webhooks
- whatsbot
- hotel_management_system
- catering_management_module
- recruitment
- whatsapp_chat
- mailbox
- api
- mfa
- ai_project_analyzer
- approvify
- automation_manager
- commission
- custom_links
- facebook_leads_integration
- fixed_equipment
- flat_admin_theme
- goals
- google_workspace
- inject_javascript
- ma
- mention
- mmb
- multi_page_wtl
- okr
- perfex_dark_theme
- poly_utilities
- products
- project_roadmap
- publishx
- qrcode
- shopier
- si_lead_followup
- si_task_filters
- si_timesheet
- si_todo
- spreadsheet_online
- styleflow
- supportboard
- table_manage
- task_bookmarks
- team_password
- telegram_notification
- timesheets
- woocommerce
- workflow_automation
- zoom
- zoom_meetings

## 6) Uwaga operacyjna

Aktualny aktywny zestaw modułów i finalny stan środowiska należy zawsze potwierdzić bezpośrednio:

- `SELECT module_name, active FROM tblmodules WHERE active=1;`
- test HTTP `crm.flowquest.pl` + tenanty z `/tmp/fq_active_tenants.txt`

