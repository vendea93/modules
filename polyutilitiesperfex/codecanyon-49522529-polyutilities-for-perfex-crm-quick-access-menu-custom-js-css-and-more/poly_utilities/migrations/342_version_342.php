<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_342 extends App_module_migration
{
    public function up()
    {
        // Tables are now created in install.php
        // This migration is kept for version tracking only
    }
    
    public function down()
    {
        // Tables are managed in install.php
    }
    
    public function logChanged()
    {
        /*
        -------- Version 3.4.2 (November 26, 2025) --------  
        P/S: In case you encounter any conflicts during usage, please leave feedback or contact me at polyxgo@gmail.com. I will support you right away! Thanks.

        NEW
        - Integrate fixed bottom menu (sticky bottom menu with 3-level menu items). Support unlimited menu items slider. Toggle on/off either or both on desktop, mobile.
        - Support managing, categorizing, creating task template list for new project creation. This removes need to recreate generic tasks for most projects like gathering client requirements, feature lists, design, feature integration, handover, etc.

        CHANGED
        - Add cache for Custom Menu processing + language keys.
        - Persistent tab state in settings.

        FIXED
        - Fix query bug causing Custom Menu slow load and slowing system. Sincerely apologize to everyone with system affected by this bug!
        - Fix issue where new menu items not added into database when module updates routes.
        - Optimize menu sync performance with batch operations.
        */
    }
}
