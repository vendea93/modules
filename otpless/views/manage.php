<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-6 col-md-offset-3">
            <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
               <?php echo _l('otpless_manage') ?>
            </h4>
            <?php echo form_open($this->uri->uri_string()); ?>
            <div class="panel_s">
               <div class="panel-body">
                  <div class="form-group">
                     <label for="time_format" class="control-label"><?php echo _l('otpless_functionality'); ?></label>
                     <select name="settings[otpless_functionality]" id="time_format" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value="0" <?php if('0' == get_option('otpless_functionality')){echo 'selected';} ?>><?php echo _l('de-active'); ?></option>
                        <option value="1" <?php if('1' == get_option('otpless_functionality')){echo 'selected';} ?>><?php echo _l('active'); ?></option>
                     </select>
                  </div>
                  <?php echo render_input('settings[otpless_appid]', 'otpless_appid', get_option('otpless_appid'), 'text',['required'=>'required']); ?>
                 
                  <?php echo render_input('settings[otpless_clientid]', 'otpless_clientid', get_option('otpless_clientid'), 'text',['required'=>'required']); ?>

                  <?php echo render_input('settings[otpless_client_secret]', 'otpless_client_secret', get_option('otpless_client_secret'), 'text',['required'=>'required']); ?>
                   <?php echo render_input('settings[otpless_purchase_code]', 'purchase_code', get_option('otpless_purchase_code'), 'text',['required'=>'required']); ?>
                  <label for="show_subscriptions_in_customers_area" class="control-label clearfix">
        <i class="fa-regular fa-circle-question" data-toggle="tooltip" data-title="How to find your purchase code" data-original-title="" title=""></i> <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code" target="_blank">Where Is My Purchase Code?</a></label>
        
               </div>
               <div class="panel-footer text-right">
                  <button class="btn btn-primary"
                     type="submit"><?php echo _l('Save'); ?></button>
               </div>
            </div>
            <?php echo form_close(); ?>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
</html>