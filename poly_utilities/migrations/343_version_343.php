<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_343 extends App_module_migration
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
        -------- Version 3.4.3 (November 30, 2025) --------  
        P/S: In case you encounter any conflicts during usage, please leave feedback or contact me at polyxgo@gmail.com. I will support you right away! Thanks.

        FIXED
        - Fixed issue where the page became blank due to migration Custom Menu.
        - Fixed issue where toggling active banner settings resulted in 400 error when setting default values for the first time.
        */
    }
}
