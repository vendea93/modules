<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="tw-max-w-4xl tw-mx-auto">
            <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700"><?=_l('hosting_manager_add')?></h4>
            <?php echo form_open(admin_url('hosting_manager/save_hosting_manager'),['id'=>'save_form']); ?>

                <div class="panel_s">
                <div class="panel-body">
                        <div class="container-fluid">
                            <?php echo render_input('title', _l('hosting_manager_title'), '', 'text', ['required' => 'required', 'id' => 'hosting_manager_title', 'placeholder' => _l('hosting_manager_title')]); ?>

                          
                            <div class="row">  
                            <div class="col-md-6">
                                    <?php echo render_input('provider', _l('hosting_manager_provider'), '', 'text', ['required' => 'required','id' => 'hosting_manager_provider', 'placeholder' =>  _l('hosting_manager_provider'), 'autocomplete' => 'off']); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('provider_url', _l('hosting_manager_provider_url'), '', 'text', ['id' => 'hosting_manager_provider_url', 'placeholder' =>  _l('hosting_manager_provider_url'), 'autocomplete' => 'off']); ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <?php echo render_input('username', _l('hosting_manager_username'), '', 'text', ['id' => 'hosting_manager_username', 'placeholder' =>  _l('hosting_manager_username'), 'autocomplete' => 'off']); ?>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="control-label">
                                        <?= _l('hosting_manager_password'); ?>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control password" name="password"
                                                autocomplete="false">
                                            <span class="input-group-addon tw-border-l-0">
                                            <a href="#password" class="show_password"
                                                onclick="showPassword('password'); return false;"><i class="fa fa-eye"></i></a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('plan', _l('hosting_manager_plan'), '', 'text', ['id' => 'hosting_manager_plan', 'placeholder' =>  _l('hosting_manager_plan'), 'autocomplete' => 'off']); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_input('price', _l('hosting_manager_price'), '', 'text', ['id' => 'hosting_manager_price', 'placeholder' =>  _l('hosting_manager_price'), 'autocomplete' => 'off']); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_date_input('start_date', _l('hosting_manager_start_date'), '', ['id' => 'hosting_manager_start_date', 'autocomplete' => 'off','class'=>'col-ms-6']); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo render_date_input('expiry_date', _l('hosting_manager_expiry_date'), '', ['id' => 'hosting_manager_expiry_date', 'autocomplete' => 'off']); ?>
                                </div>
                               
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="project_id"><?php echo _l('Project'); ?></label>
                                        <select name="project_id" id="project_id" class="form-control selectpicker"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                            <option value="">- Project -</option>
                                            <?php foreach ($projects as $project) { ?>
                                            <option value="<?= $project['id']; ?>"><?= $project['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_id"><?php echo _l('Client'); ?></label>
                                        <select name="client_id" id="client_id" class="form-control selectpicker"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                            <option value="">- Client -</option>
                                            <?php foreach ($clients as $client) { ?>
                                            <option value="<?= $client['userid']; ?>"><?= $client['company']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                
                              
                            </div>
                            <div class="form-group">
                                <label for="status"><?php echo _l('Status'); ?></label>
                                <select name="status" class="selectpicker" data-width="100%" id="status">

                                    <?php $status = [
                'active'=>  _l('hosting_manager_active'),
                'hosting_manager_expiring_soon'=>  _l('hosting_manager_expiring_soon'),
                'expired'=>  _l('hosting_manager_expired'),
                'pending'=>  _l('hosting_manager_pending'),
            ]; foreach ($status as $key => $s) { ?>
                                    <option value="<?=$key?>"><?=$s?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php echo render_textarea('description', 'Description', '', ['placeholder' => 'Description']); ?>
                        </div>


                    </div>
                    <div class="panel-footer text-right">
                        <button class="btn btn-primary" type="submit"><?=_l('Save')?></button>
                    </div>
                </div>
                <?php echo form_close(); ?>
        </div>
    </div>
</div>


<?php init_tail(); ?>
<script>
    $(".menu-item-hosting_manager").addClass('active');
    $(".sub-menu-item-hosting_manager").addClass('active');
    
</script>
</body>
</html>