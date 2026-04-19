<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(is_admin() || has_permission('workflow_automation_setting', '', 'edit')){ ?>
<a href="#" onclick="permissions_update(0,0,' hide'); return false;" class="btn btn-info mbot10"><?php echo _l('wa_add'); ?></a>
<?php } ?>
<table class="table table-permission">
  <thead>
    <th><?php echo _l('wa_staff_name'); ?></th>
    <th><?php echo _l('role'); ?></th>
    <th><?php echo _l('staff_dt_email'); ?></th>
    <th><?php echo _l('wa_phone'); ?></th>
    <th><?php echo _l('options'); ?></th>
  </thead>
  <tbody>
  </tbody>
</table>
<div id="modal_wrapper"></div>

