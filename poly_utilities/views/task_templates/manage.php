<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head();
// Load VueJS
echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/vuejs/3.4.27/vue.global.prod.js') . '"></script>';

// Prepare data for VueJS
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab_templates';

// Get saved tab from localStorage if available (will be overridden by JS)
?>
<script>
    window.taskTemplatesManageData = {
        templates: <?php echo json_encode($templates ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
        categories: <?php echo json_encode($categories ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
        activeTab: <?php echo json_encode($active_tab); ?>
    };
</script>
<div id="task-templates-manage-app" v-cloak>
    <div id="wrapper">
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                            <i class="fa fa-tasks tw-mr-2"></i><?php echo $title; ?>
                        </h4>
                        <div class="tw-space-x-2">
                            <a href="<?php echo admin_url('poly_utilities/task_templates/category'); ?>" class="btn btn-info">
                                <i class="fa fa-folder-plus tw-mr-1"></i><?php echo _l('add_new', _l('poly_utilities_category')); ?>
                            </a>
                            <a href="<?php echo admin_url('poly_utilities/task_templates/template'); ?>" class="btn btn-primary">
                                <i class="fa fa-plus tw-mr-1"></i><?php echo _l('add_new', _l('poly_utilities_task_template')); ?>
                            </a>
                        </div>
                    </div>

                    <div class="panel_s">
                        <div class="panel-body">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" :class="{'active': activeTab === 'tab_templates'}">
                                    <a href="#tab_templates" aria-controls="tab_templates" role="tab" data-toggle="tab">
                                        <i class="fa fa-list tw-mr-1"></i><?php echo _l('poly_utilities_task_templates'); ?>
                                    </a>
                                </li>
                                <li role="presentation" :class="{'active': activeTab === 'tab_categories'}">
                                    <a href="#tab_categories" aria-controls="tab_categories" role="tab" data-toggle="tab">
                                        <i class="fa fa-folder tw-mr-1"></i><?php echo _l('poly_utilities_task_template_categories'); ?>
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content tw-mt-4 panel-table-full">
                                <!-- Templates Tab -->
                                <div role="tabpanel" class="tab-pane" :class="{'active': activeTab === 'tab_templates'}" id="tab_templates">
                                    <div class="table-responsive">
                                        <table class="table" id="templates-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;"><?php echo _l('poly_utilities_order'); ?></th>
                                                    <th><?php echo _l('name'); ?></th>
                                                    <th><?php echo _l('poly_utilities_category'); ?></th>
                                                    <th><?php echo _l('poly_utilities_description'); ?></th>
                                                    <th class="text-center"><?php echo _l('poly_utilities_items_count'); ?></th>
                                                    <th class="text-center"><?php echo _l('status'); ?></th>
                                                    <th class="text-center"><?php echo _l('options'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="template in templates" :key="template.id" :data-id="template.id">
                                                    <td>{{ template.order }}</td>
                                                    <td>{{ template.name }}</td>
                                                    <td>
                                                        <span v-if="template.category_name" class="label" :style="'background-color: ' + (template.category_color || '#3498db')">
                                                            {{ template.category_name }}
                                                        </span>
                                                        <span v-else class="text-muted"><?php echo _l('poly_utilities_no_category'); ?></span>
                                                    </td>
                                                    <td>{{ template.description || '' }}</td>
                                                    <td class="text-center">{{ template.items_count || 0 }}</td>
                                                    <td class="text-center">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" class="onoffswitch-checkbox" :id="'template-' + template.id" :checked="template.active == 1" @change="toggleTemplateStatus(template.id, $event.target.checked)">
                                                            <label class="onoffswitch-label" :for="'template-' + template.id"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                    <div class="flex-center">
                                                        <a :href="'<?php echo admin_url('poly_utilities/task_templates/template/'); ?>' + template.id">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <a href="#" @click.prevent="deleteTemplate(template.id)">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Categories Tab -->
                                <div role="tabpanel" class="tab-pane" :class="{'active': activeTab === 'tab_categories'}" id="tab_categories">
                                    <div class="table-responsive">
                                        <table class="table" id="categories-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;"><?php echo _l('poly_utilities_order'); ?></th>
                                                    <th><?php echo _l('name'); ?></th>
                                                    <th><?php echo _l('poly_utilities_description'); ?></th>
                                                    <th class="text-center"><?php echo _l('poly_utilities_color'); ?></th>
                                                    <th class="text-center"><?php echo _l('poly_utilities_templates_count'); ?></th>
                                                    <th class="text-center"><?php echo _l('status'); ?></th>
                                                    <th class="text-center"><?php echo _l('options'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="category in categories" :key="category.id" :data-id="category.id">
                                                    <td>{{ category.order }}</td>
                                                    <td>{{ category.name }}</td>
                                                    <td>{{ category.description || '' }}</td>
                                                    <td class="text-center">
                                                        <span v-if="category.color" class="label" :style="'background-color: ' + category.color">
                                                            {{ category.color }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">{{ category.templates_count || 0 }}</td>
                                                    <td class="text-center">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" class="onoffswitch-checkbox" :id="'category-' + category.id" :checked="category.active == 1" @change="toggleCategoryStatus(category.id, $event.target.checked)">
                                                            <label class="onoffswitch-label" :for="'category-' + category.id"></label>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="flex-center">
                                                        <a :href="'<?php echo admin_url('poly_utilities/task_templates/category/'); ?>' + category.id">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <a href="#" @click.prevent="deleteCategory(category.id)">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<?php
echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/task_templates_manage.js') . '"></script>';
?>