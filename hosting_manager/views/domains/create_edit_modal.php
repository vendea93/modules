<div class="modal fade domain-modal" id="domain_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md tw-max-w-[48rem]" role="document">
        <?php echo form_open(admin_url('hosting_manager/domains/create'),['id'=>'save_form']); ?>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-label="Close"  data-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title"><?=_l('Add')?></h4>
            </div>
            <div class="modal-body">
                    <input type="hidden" name="hosting_id" value="<?=$hosting_id?>">
                <?php echo render_input('name', _l('hosting_manager_domain_name'), '', 'text', ['required' => 'required', 'id' => 'hosting_manager_domain_name', 'placeholder' => _l('hosting_manager_domain_name')]); ?>
                <?php echo render_input('price', _l('hosting_manager_price'), '', 'text', ['id' => 'hosting_manager_price', 'placeholder' => _l('hosting_manager_price')]); ?>
                <div class="form-group">
                                <label for="ssl_status"><?php echo _l('SSL Status'); ?></label>
                                <select name="ssl_status" class="selectpicker" data-width="100%" id="ssl_status">

                                    <?php $status = [
                'enable'=>  _l('hosting_manager_enable'),
                'disable'=>  _l('hosting_manager_disable'),
            ]; foreach ($status as $key => $s) { ?>
                                    <option value="<?=$key?>"><?=$s?></option>
                                    <?php } ?>
                                </select>
                            </div>
                <div class="form-group">
                                <label for="status"><?php echo _l('Status'); ?></label>
                                <select name="status" class="selectpicker" data-width="100%" id="status">

                                    <?php $status = [
                'active'=>  _l('hosting_manager_active'),
                'expiring_soon'=>  _l('hosting_manager_expiring_soon'),
                'expired'=>  _l('hosting_manager_expired'),
                'pending'=>  _l('hosting_manager_pending'),
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

<div class="modal fade domain-modal" id="domain_modal_edit" tabindex="-1" role="dialog">
  
</div>
