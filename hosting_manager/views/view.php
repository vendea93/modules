<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class=" tw-mx-auto">
           

            <div class="sm:tw-flex sm:tw-justify-between sm:tw-items-center tw-mb-3 -tw-mt-px">
                <h4 class="tw-my-0 tw-font-bold tw-text-lg tw-text-neutral-700 tw-max-w-xl tw-truncate tw-space-x-1.5"
                    title="<?= isset($hosting) ? e($hosting->title) : ''; ?>">
                    <span>
                        <?= isset($hosting) ? e($hosting->title) : _l('hosting_information') ?>
                    </span>
                </h4>

            </div>
            <?php include('tabs.php') ?>
            <div class="panel_s">
                <div class="panel-body">
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="tab_info">
                        <div class="col-md-12">
                                <h4 class="tw-font-semibold tw-text-base tw-mb-4"><?=_l('hosting_manager_hosting_overview')?></h4>
                                <dl class="tw-grid tw-grid-cols-1 tw-gap-x-4 tw-gap-y-3 sm:tw-grid-cols-2">
                                    <div class="sm:tw-col-span-1 project-overview-id">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600"><?=_l('hosting_manager_hosting_id')?> # </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500"><?=$hosting->id?></dd>
                                    </div>

                                    <div class="sm:tw-col-span-1 project-overview-customer">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600"><?= _l('hosting_manager_title')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500">
                                                <?=$hosting->title?>
                                        </dd>
                                    </div>

                                    <div class="sm:tw-col-span-1 project-overview-billing">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600"><?=_l('hosting_manager_provider')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500"><?=$hosting->provider?></dd>
                                    </div>
                                    
                                    <div class="sm:tw-col-span-1 project-overview-customer">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600"><?=_l('hosting_manager_provider_url')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500">
                                        <a href="<?= (isset($hosting->provider_url) && !empty($hosting->provider_url) && strpos($hosting->provider_url, 'https://') === 0 ? $hosting->provider_url : 'https://default-url.com') ?>">
    <?=$hosting->provider_url ?: ''?>
</a>
                                        </dd>
                                    </div>
                                  
                                    <div class="sm:tw-col-span-1 project-overview-status">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600">
                                            <?=_l('hosting_manager_username')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500">
                                        <?=$hosting->provider_password ?> </dd>
                                    </div>

                                    <div class="sm:tw-col-span-1 project-overview-date-created">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600">
                                            <?= _l('hosting_manager_password')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500"><?=$hosting->provider_password?>  </dd>
                                    </div>
                                   
                                    <div class="sm:tw-col-span-1 project-overview-billing">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600">
                                        <?= _l('hosting_manager_client')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500">
                                        <a href="<?= admin_url('clients/client/'.$hosting->client_id)?>">
                                                <?=$hosting->client_id?>
                                            </a></dd>
                                    </div>
                                    <div class="sm:tw-col-span-1 project-overview-customer">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600"><?=_l('hosting_manager_project')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500">
                                            <a href="<?= admin_url('projects/view/'.$hosting->project_id)?>">
                                                <?=$hosting->project_id?>
                                            </a>
                                        </dd>
                                    </div>
                                    <div class="sm:tw-col-span-1 project-overview-start-date">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600">
                                            <?=_l('hosting_manager_start_date')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500">
                                        <?= _d($hosting->start_date)?> </dd>
                                    </div>
                                    <div class="sm:tw-col-span-1 project-overview-deadline">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600">
                                            <?= _l('hosting_manager_expiry_date')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm tw-text-neutral-500">
                                        <?= _d($hosting->expiry_date)?> </dd>
                                    </div>
                                   


                                    <div class="sm:tw-col-span-1 project-overview-estimated-hours">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600">
                                            <?=_l('hosting_manager_status')?> </dt>
                                        <dd class="tw-mt-1 tw-text-sm text-neutral-900">
                                        <?= _l($hosting->status)?></dd>
                                    </div>

                                   


                                    <div class="clearfix"></div>
                                    <div class="sm:tw-col-span-2 project-overview-description tc-content">
                                        <dt class="tw-text-sm tw-font-medium tw-text-neutral-600">
                                        <?=_l('description')?> </dt>
                                        <dd class="tw-mt-1 tw-space-y-5 tw-text-sm tw-text-neutral-500">
                                            <p class="text-muted tw-mb-0">
                                            <?= $hosting->description?> </p>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                        
                      
                        <div role="tabpanel" class="tab-pane<?= $this->input->get('tab') == 'tab_domain' ? ' active' : ''; ?>" id="tab_domain">
                            <div id="domain_list">

                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal-wrapper"></div>
<?php init_tail(); ?>
<script>
    var hosting_id = '<?=$hosting->id?>';
         
</script>
</body>

</html>