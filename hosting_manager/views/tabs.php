<div class="horizontal-scrollable-tabs">
                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                    <ul class="nav nav-tabs nav-tabs-horizontal nav-tabs-segmented tw-mb-3" role="tablist">
                        <li role="presentation"
                            class="<?= $active_tab=='overview' ? 'active' : ''; ?>">
                            <a href="<?= admin_url('hosting_manager/view/'.$hosting->id)?>">
                                <?= _l('hosting_information'); ?>
                            </a>
                        </li>
                        <li role="presentation" class="<?= $active_tab == 'domain' ? 'active' : ''; ?>">
                            <a href="<?= admin_url('hosting_manager/domains?hosting_id='.$hosting->id)?>" >
                                <?= _l('hosting_manager_domains'); ?>
                                <?php $totalDomain = total_rows(db_prefix() . 'hm_domains', 'hosting_id=' . $hosting->id); ?>
                                <span class="badge domain-indicator<?= $totalDomain == 0 ? ' hide' : ''; ?>">
                                    <?= e($totalDomain); ?>
                                </span>
                            </a>
                        </li>
                        <li role="presentation" class="<?= $active_tab == 'database' ? 'active' : ''; ?>">
                            <a href="<?= admin_url('hosting_manager/database?hosting_id='.$hosting->id)?>" >
                                <?= _l('hosting_manager_database'); ?>
                                <?php $totalDomain = total_rows(db_prefix() . 'hm_database', 'hosting_id=' . $hosting->id); ?>
                                <span class="badge database-indicator<?= $totalDomain == 0 ? ' hide' : ''; ?>">
                                    <?= e($totalDomain); ?>
                                </span>
                            </a>
                        </li>
                        <li role="presentation" class="<?= $active_tab == 'ftp' ? 'active' : ''; ?>">
                            <a href="<?= admin_url('hosting_manager/ftp?hosting_id='.$hosting->id)?>" >
                                <?= _l('hosting_manager_ftp'); ?>
                                <?php $totalDomain = total_rows(db_prefix() . 'ftp_accounts', 'hosting_id=' . $hosting->id); ?>
                                <span class="badge ftp-indicator<?= $totalDomain == 0 ? ' hide' : ''; ?>">
                                    <?= e($totalDomain); ?>
                                </span>
                            </a>
                        </li>
                        
                       
                    </ul>
                </div>
            </div>