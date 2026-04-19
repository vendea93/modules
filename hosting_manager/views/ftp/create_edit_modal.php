<div class="modal fade ftp-modal" id="ftp_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md tw-max-w-[48rem]" role="document">
        <?php echo form_open(admin_url('hosting_manager/ftp/create'),['id'=>'save_form']); ?>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close"  data-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title"><?=_l('hosting_manager_add_ftp')?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="hosting_id" value="<?=$hosting_id?>">
                <?php echo render_input('account_name', _l('hosting_manager_account_name'), '', 'text', ['required' => 'required', 'id' => 'hosting_manager_account_name', 'placeholder' => _l('hosting_manager_account_name')]); ?>
               
                <?php echo render_input('hostname', _l('hosting_manager_hostname'), '', 'text', ['id' => 'hosting_manager_hostname', 'placeholder' => _l('hosting_manager_hostname')]); ?>

                <?php echo render_input('username', _l('hosting_manager_username'), '', 'text', ['id' => 'hosting_manager_username', 'placeholder' => _l('hosting_manager_username')]); ?>
                
             
                <div class="form-group">
                    <label for="password" class="control-label">
                    <?= _l('hosting_manager_ftp_password'); ?>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control password" name="ftp_password"
                            autocomplete="false">
                        <span class="input-group-addon tw-border-l-0">
                        <a href="#password" class="show_password"
                            onclick="showPassword('ftp_password'); return false;"><i class="fa fa-eye"></i></a>
                        </span>
                    </div>
                </div>

                <?php echo render_input('port', _l('hosting_manager_port'), '', 'text', ['id' => 'hosting_manager_port', 'placeholder' => _l('hosting_manager_port')]); ?>
                <?php echo render_input('protocol', _l('hosting_manager_protocol'), '', 'text', ['id' => 'hosting_manager_protocol', 'placeholder' => _l('hosting_manager_protocol')]); ?>

                <?php echo render_input('root_directory', _l('hosting_manager_root_directory'), '', 'text', ['id' => 'hosting_manager_root_directory', 'placeholder' => _l('hosting_manager_root_directory')]); ?>
        
             
                <div class="form-group">
                                <label for="status"><?php echo _l('Status'); ?></label>
                                <select name="status" class="selectpicker" data-width="100%" id="status">

                                    <?php $status = [
                'enable'=>  _l('hosting_manager_enable'),
                'disable'=>  _l('hosting_manager_disable'),
            ]; foreach ($status as $key => $s) { ?>
                                    <option value="<?=$key?>"><?=$s?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php echo render_textarea('description', 'Description', '', ['placeholder' => 'Description']); ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<div class="modal fade ftp-modal" id="ftp_modal_edit" tabindex="-1" role="dialog">
  
</div>
