<div class="row">
  <div class="col-md-6">
    <?php if($check_config != 1){ 
     echo '<p class="text-danger">'._l('please_enter_the_application_code_first').': <a href="'.admin_url('reputation/settings?group='.$type).'">Link</a> </p>';
    } ?>
    </div>
    <div class="col-md-6">
        <a href="#" onclick="add_account(); return false;" class="btn btn-primary mbot10 pull-right"><?php echo _l('ma_add_new', _l('account')); ?></a>
    </div>
</div>

<hr class="hr-panel-heading" />
<table class="table table-accounts">
  <thead>
    <th><?php echo _l('name'); ?></th>
    <th><?php echo _l('description'); ?></th>
    <th><?php echo _l('status'); ?></th>
    <th><?php echo _l('active'); ?></th>
    <th><?php echo _l('action'); ?></th>
  </thead>
  <tbody>
  </tbody>
</table>
