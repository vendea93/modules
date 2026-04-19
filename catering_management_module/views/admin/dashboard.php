<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
    <div id="wrapper">
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin bold"><?php echo _l('catering_dashboard'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="widget-drilldown">
                                <div class="widget-drilldown-heading">
                                    <h3 class="bold"><?php echo $total_items; ?></h3>
                                    <span class="text-muted"><?php echo _l('menu_items'); ?></span>
                                </div>
                                <div class="widget-drilldown-icon">
                                    <i class="fa fa-list"></i>
                                </div>
                            </div>
                            <a href="<?php echo admin_url('catering/items'); ?>" class="btn btn-default btn-block mtop15">
								<?php echo _l('view_all'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="widget-drilldown">
                                <div class="widget-drilldown-heading">
                                    <h3 class="bold"><?php echo $total_menus; ?></h3>
                                    <span class="text-muted"><?php echo _l('menus'); ?></span>
                                </div>
                                <div class="widget-drilldown-icon">
                                    <i class="fa fa-book"></i>
                                </div>
                            </div>
                            <a href="<?php echo admin_url('catering/menus'); ?>" class="btn btn-default btn-block mtop15">
								<?php echo _l('view_all'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="widget-drilldown">
                                <div class="widget-drilldown-heading">
                                    <h3 class="bold"><?php echo $total_packages; ?></h3>
                                    <span class="text-muted"><?php echo _l('packages'); ?></span>
                                </div>
                                <div class="widget-drilldown-icon">
                                    <i class="fa fa-cube"></i>
                                </div>
                            </div>
                            <a href="<?php echo admin_url('catering/packages'); ?>" class="btn btn-default btn-block mtop15">
								<?php echo _l('view_all'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="widget-drilldown">
                                <div class="widget-drilldown-heading">
                                    <h3 class="bold"><i class="fa fa-cog"></i></h3>
                                    <span class="text-muted"><?php echo _l('settings'); ?></span>
                                </div>
                                <div class="widget-drilldown-icon">
                                    <i class="fa fa-wrench"></i>
                                </div>
                            </div>
                            <div class="mtop15">
                                <a href="<?php echo admin_url('catering/categories'); ?>" class="btn btn-default btn-icon btn-block">
									<?php echo _l('menu_categories'); ?>
                                </a>
                                <a href="<?php echo admin_url('catering/sections'); ?>" class="btn btn-default btn-icon btn-block mtop5">
									<?php echo _l('menu_sections'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="bold"><?php echo _l('quick_actions'); ?></h4>
                            <div class="row mtop15">
								<?php if (staff_can('create', 'catering_events')) { ?>
                                    <div class="col-md-3 col-sm-6">
                                        <a href="<?php echo admin_url('catering/item'); ?>" class="btn btn-primary btn-block">
                                            <i class="fa fa-plus"></i> <?php echo _l('new_menu_item'); ?>
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <a href="<?php echo admin_url('catering/menu'); ?>" class="btn btn-primary btn-block">
                                            <i class="fa fa-plus"></i> <?php echo _l('new_menu'); ?>
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <a href="<?php echo admin_url('catering/package'); ?>" class="btn btn-primary btn-block">
                                            <i class="fa fa-plus"></i> <?php echo _l('new_package'); ?>
                                        </a>
                                    </div>
								<?php } ?>
                                <div class="col-md-3 col-sm-6">
                                    <a href="<?php echo admin_url('catering/report_item_popularity'); ?>" class="btn btn-default btn-block">
                                        <i class="fa fa-bar-chart"></i> <?php echo _l('reports'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="bold"><?php echo _l('recently_added_items'); ?></h4>
							<?php
							$this->db->select('id, item_name, unit_price, created_at');
							$this->db->from(db_prefix() . 'catering_menu_items');
							$this->db->where('active', 1);
							$this->db->order_by('created_at', 'DESC');
							$this->db->limit(5);
							$recent_items = $this->db->get()->result_array();

							if (!empty($recent_items)) {
								echo '<ul class="list-unstyled">';
								foreach ($recent_items as $item) {
									echo '<li class="mtop10">';
									echo '<a href="' . admin_url('catering/item/' . $item['id']) . '">';
									echo '<strong>' . htmlspecialchars($item['item_name']) . '</strong>';
									echo '</a>';
									echo '<span class="pull-right">';
									echo '<span class="label label-primary">' . $item['usage_count'] . ' ' . _l('uses') . '</span>';
									echo '</span>';
									echo '</li>';
								}
								echo '</ul>';
							} else {
								echo '<p class="text-muted">' . _l('no_data_available') . '</p>';
							}
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php init_tail(); ?>
    <script>
        $(function() {
            // Dashboard initialization
        });
    </script>
    </body>
    </html>
    <a href="' . admin_url('catering/item/' . $item['id']) . '">';
        echo '<strong>' . htmlspecialchars($item['item_name']) . '</strong>';
        echo '</a>';
    echo '<span class="pull-right text-muted">' . catering_format_price($item['unit_price']) . '</span>';
    echo '<br><small class="text-muted">' . _dt($item['created_at']) . '</small>';
    echo '</li>';
    }
    echo '</ul>';
    } else {
    echo '<p class="text-muted">' . _l('no_items_found') . '</p>';
    }
    ?>
    </div>
    </div>
    </div>

<div class="col-md-6">
    <div class="panel_s">
    <div class="panel-body">
    <h4 class="bold"><?php echo _l('popular_items'); ?></h4>
<?php
$this->db->select('mi.id, mi.item_name, COUNT(DISTINCT mil.menu_id) + COUNT(DISTINCT pil.package_id) as usage_count');
$this->db->from(db_prefix() . 'catering_menu_items mi');
$this->db->join(db_prefix() . 'catering_menu_items_link mil', 'mil.item_id = mi.id', 'left');
$this->db->join(db_prefix() . 'catering_package_items_link pil', 'pil.item_id = mi.id', 'left');
$this->db->where('mi.active', 1);
$this->db->group_by('mi.id');
$this->db->having('usage_count >', 0);
$this->db->order_by('usage_count', 'DESC');
$this->db->limit(5);
$popular_items = $this->db->get()->result_array();

if (!empty($popular_items)) {
	echo '<ul class="list-unstyled">';
	foreach ($popular_items as $item) {
		echo '<li class="mtop10">';
		echo '