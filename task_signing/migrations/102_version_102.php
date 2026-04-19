<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Migration_Version_102 extends App_module_migration
{

    public function up()
    {

        add_option('ts_complete_task_without_sign',1,0);
        add_option('ts_followers_will_sign',1,0);

    }

}
