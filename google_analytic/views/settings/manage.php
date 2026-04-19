<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-3">
        <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked customer-tabs" role="tablist">
          <?php
          foreach($tab as $key => $gr){
            ?>
            <li class="<?php if($key == 0){echo 'active ';} ?>setting_tab_<?php echo e($key); ?>">
              <a data-group="<?php echo e($gr); ?>" href="<?php echo admin_url('google_analytic/setting?group='.$gr); ?>">
                 <?php echo _l($gr); ?>
              </a>
            </li>
          <?php } ?>
        </ul>
      </div>
      <div class="col-md-9">
        <div class="panel_s">
           <div class="panel-body">
              <div>
                 <div class="tab-content">
                    <?php $this->load->view($tabs['view']); ?>
                 </div>
              </div>
           </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>