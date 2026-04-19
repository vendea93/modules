<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_341 extends App_module_migration
{
    public function up()
    {
       // No changes required
    }
    
    public function down()
    {
        // No rollback required
    }
    
    public function logChanged()
    {
        /*
        -------- Version 3.4.1 (November 14, 2025) --------  
        P/S: In case you encounter any conflicts during usage, please leave feedback or contact me at polyxgo@gmail.com. I will support you right away! Thanks.

        NEW
        - Adjust checkbox style to a unified toggle button.
        - Add settings to enable/disable Custom Menu, Multiple Companies, Multiple Addresses.

        CHANGED
        - Disable Gravatar support to avoid external avatar requests that slow down access to multiple companies and multiple addresses features.
        - Integrate settings that allow enabling or disabling features: Custom Menu, Multiple Companies, Multiple Addresses.
        - Projects Name patterns: Add drag & drop support to reorder project name patterns. Remove pagination because the actual data is usually not large.

        FIXED
        - Issue where all menu items became active when the route removed the trailing slash after /admin.
        - Fix conflict causing incorrect active state for menu items when adding custom styles css and scripts js.
        - Fix some issues related to the Project name pattern feedback.

        TASKS IN PROGRESS
        - Support for managing, categorizing, and creating a list of task templates for new project creation. This eliminates the need to recreate generic tasks for most projects, such as gathering client requirements, feature lists, design, feature integration, handover, etc. (September 1, 2025)
        - Fixed bottom menu on mobile devices. (September 1, 2025)
        */
    }
}
