# FlowQuest CRM Modules Repository

This repository contains all the modules for the FlowQuest CRM system.

## Overview
FlowQuest CRM uses a modular architecture with over 119 different modules that extend its functionality. Each module is designed for specific business needs and industries.

## Repository Branches
- `main` - Original clean modules from backup (2026-04-21)
- `server-version` - Current server modifications with all updates

## Module Categories
The modules are organized by business functions:
1. **Project Management** - Project planning, task tracking, time management
2. **Sales & Marketing** - Lead management, customer relationship tools
3. **Finance** - Invoicing, accounting, payment processing
4. **Communication** - Email, SMS, chat systems
5. **Industry-Specific** - Modules tailored for specific sectors like Beauty, Hotel, Logistics, etc.
6. **Administration** - User management, settings, system tools
7. **Reporting** - Analytics, dashboards, export tools

## Key Modules
- `account_planning` - Financial planning tools
- `appointly` - Appointment scheduling system
- `assets` - Asset management
- `commission` - Commission tracking
- `hrm` - Human resources management
- `omni_sales` - Multi-channel sales platform
- `purchase` - Procurement management
- `warehouse` - Inventory management
- `workshop` - Workshop/service management
- `fq_saas` - Main SaaS platform integration

## Installation
Modules are automatically loaded by the CRM system. Each module follows the Perfex CRM module structure:
```
module_name/
├── module_name.php          # Main module file
├── assets/                  # CSS, JS, images
├── controllers/            # PHP controllers
├── helpers/                # Helper functions
├── language/               # Language files
├── libraries/              # Custom libraries
├── migrations/             # Database migrations
├── models/                 # Data models
├── views/                  # Template files
└── config/                 # Configuration files
```

## AI Agent Instructions
1. **Branch Management**:
   - `main` branch contains original backup code
   - `server-version` branch contains current server modifications
   - Create feature branches from `server-version` for development

2. **Module Development Guidelines**:
   - Follow Perfex CRM module structure conventions
   - Use consistent naming patterns
   - Maintain compatibility with core CRM functions
   - Test integrations with other modules
   - Document all custom functions

3. **Version Tracking**:
   - Check commit history for module update logs
   - Compare branches to see differences in modules
   - Use git diff to analyze specific module changes

4. **Deployment Notes**:
   - Modules path: `/var/www/crm.flowquest.pl/modules/`
   - Each module should be self-contained
   - Dependencies should be clearly documented
   - Test module activation/deactivation

## Module Update Reports
Key documentation files available in the repository:
- `FLOWQUEST_Q2_2026_ALIGNMENT.md` - Q2 2026 alignment report
- `FQ_ALL_REPORTS.md` - Comprehensive module reports
- `RAPORTY_FQ_SAAS.md` - SaaS module reports
- `FQ_GLOBAL_SYNC_LAST.md` - Last global sync report

Last updated: 2026-05-02