<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
init_head();
// Load VueJS
echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/vuejs/3.4.27/vue.global.prod.js') . '"></script>';
// Load CSS
echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/css/admin/task_templates.css') . '">';

// Pass data to JavaScript - MUST be in head section before any JS files load
$template_items = [];
if (isset($template) && isset($template['items']) && is_array($template['items'])) {
    $template_items = $template['items'];
}
?>
<script>
window.taskTemplateData = {
    items: <?php echo json_encode($template_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
};
</script>
<?php 
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                        <i class="fa fa-tasks tw-mr-2"></i><?php echo $title; ?>
                    </h4>
                    <a href="<?php echo admin_url('poly_utilities/task_templates'); ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left tw-mr-1"></i><?php echo _l('back'); ?>
                    </a>
                </div>

                <div class="alert alert-info tw-mb-4">
                    <i class="fa fa-info-circle tw-mr-2"></i>
                    <strong><?php echo _l('poly_utilities_task_template_info_title'); ?></strong>
                    <p class="tw-mb-0 tw-mt-2"><?php echo _l('poly_utilities_task_template_info_description'); ?></p>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open(admin_url('poly_utilities/task_templates/template' . (!empty($template['id']) ? '/' . $template['id'] : '')), ['id' => 'template-form']); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?php 
                                $name_value = isset($template['name']) ? $template['name'] : '';
                                echo render_input('name', 'name', $name_value, 'text', ['required' => true]); 
                                ?>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id" class="control-label"><?php echo _l('poly_utilities_task_template_category_label'); ?></label>
                                    <select name="category_id" id="category_id" class="form-control selectpicker" data-live-search="true">
                                        <option value=""><?php echo _l('poly_utilities_no_category'); ?></option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($template['category_id']) && $template['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php 
                            $description_value = isset($template['description']) ? $template['description'] : '';
                            echo render_textarea('description', 'description', $description_value, ['rows' => 3]); 
                            ?>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo _l('poly_utilities_active'); ?></label>
                            <div class="onoffswitch">
                                <input type="checkbox" 
                                       name="active" 
                                       id="active" 
                                       class="onoffswitch-checkbox" 
                                       value="1"
                                       <?php echo (!isset($template) || (isset($template['active']) && $template['active'] == 1)) ? 'checked' : ''; ?>>
                                <label class="onoffswitch-label" for="active"></label>
                            </div>
                        </div>

                        <hr>

                        <h5 class="tw-font-semibold tw-mb-3"><?php echo _l('poly_utilities_template_items'); ?></h5>
                        
                        <div id="task-template-app" v-cloak>
                            <div id="template-items">
                                <div v-for="(item, index) in items" :key="item.index" class="template-item-row tw-mb-3 tw-p-3 tw-border tw-rounded">
                                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                                        <strong><?php echo _l('item'); ?> #{{ index + 1 }}</strong>
                                        <button type="button" class="btn btn-danger btn-sm" @click="removeItem(index)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" 
                                                   :name="'items[' + index + '][name]'" 
                                                   class="form-control" 
                                                   placeholder="<?php echo _l('poly_utilities_task_name'); ?>" 
                                                   v-model="item.name"
                                                   required>
                                        </div>
                                        <div class="col-md-3">
                                            <select :name="'items[' + index + '][priority]'" class="form-control" v-model="item.priority">
                                                <option value="1"><?php echo _l('task_priority_low'); ?></option>
                                                <option value="2"><?php echo _l('task_priority_medium'); ?></option>
                                                <option value="3"><?php echo _l('task_priority_high'); ?></option>
                                                <option value="4"><?php echo _l('task_priority_urgent'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" 
                                                   :name="'items[' + index + '][estimated_hours]'" 
                                                   class="form-control" 
                                                   placeholder="<?php echo _l('estimated_hours'); ?>" 
                                                   step="0.5"
                                                   v-model="item.estimated_hours">
                                        </div>
                                    </div>
                                    <div class="row tw-mt-2">
                                        <div class="col-md-12">
                                            <textarea :name="'items[' + index + '][description]'" 
                                                      class="form-control" 
                                                      rows="2" 
                                                      placeholder="<?php echo _l('poly_utilities_description'); ?>"
                                                      v-model="item.description"></textarea>
                                        </div>
                                    </div>
                                    
                                    <!-- Checklist Items -->
                                    <div class="tw-mt-3">
                                        <label class="control-label tw-mb-2">
                                            <i class="fa fa-list-check tw-mr-1"></i><?php echo _l('poly_utilities_checklist_items'); ?>
                                        </label>
                                        <div class="checklist-items-container" :data-item-index="index">
                                            <div v-for="(chkItem, chkIndex) in item.checklist_items" :key="chkItem.index" class="checklist-item-row tw-flex tw-items-center tw-mb-2">
                                                <div class="drag-handle tw-cursor-move tw-mr-2 tw-text-gray-500 hover:tw-text-gray-700" title="<?php echo _l('drag_to_reorder'); ?>">
                                                    <i class="fa fa-bars"></i>
                                                </div>
                                                <input type="text" 
                                                       :name="'items[' + index + '][checklist_items][' + chkIndex + '][description]'" 
                                                       class="form-control tw-flex-1" 
                                                       placeholder="<?php echo _l('poly_utilities_checklist_item_description'); ?>" 
                                                       v-model="chkItem.description">
                                                <button type="button" class="btn btn-danger btn-sm tw-ml-2" @click="removeChecklistItem(index, chkIndex)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-info btn-sm tw-mt-2" @click="addChecklistItem(index)">
                                            <i class="fa fa-plus tw-mr-1"></i><?php echo _l('poly_utilities_add_checklist_item'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-info" @click="addItem">
                                <i class="fa fa-plus tw-mr-1"></i><?php echo _l('poly_utilities_add_item'); ?>
                            </button>
                        </div>

                        <hr>

                        <div class="tw-mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save tw-mr-1"></i><?php echo _l('submit'); ?>
                            </button>
                            <a href="<?php echo admin_url('poly_utilities/task_templates'); ?>" class="btn btn-default">
                                <?php echo _l('cancel'); ?>
                            </a>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
init_tail();
// Load JS file - data is already set in head section
echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/task_templates.js') . '"></script>';
?>
