<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo _flexacademy_lang('categories'); ?>
                    </h4>
                    <div>
                        <!--order categories modal button-->
                        <a href="#" id="flexacademy-order-category"
                            data-toggle="modal"
                            data-target="#flexacademy-order-category-modal"
                            class="btn btn-secondary mright5">
                            <i class="fa-solid fa-arrow-down-wide-short tw-mr-1"></i>
                            <?php echo _flexacademy_lang('order-category'); ?>
                        </a>
                        <a href="#" id="flexacademy-create-category"
                            data-title="<?php echo _flexacademy_lang('create-category'); ?>"
                            data-button-text="<?php echo _flexacademy_lang('create-category'); ?>"
                            data-id="0"
                            data-name=""
                            data-description=""
                            data-image="<?php echo "" ?>"
                            data-status="1"
                            data-parent-id="0"
                            class="btn btn-primary mright5 flexacademy-create-edit-category">
                            <i class="fa-solid fa-plus tw-mr-1"></i>
                            <?php echo _flexacademy_lang('create-category'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <th>#</th>
                                <th><?php echo _flexacademy_lang('category-name') ?></th>
                                <th><?php echo _flexacademy_lang('description'); ?></th>
                                <th><?php echo _flexacademy_lang('parent-category') ?></th>
                                <th><?php echo _flexacademy_lang('status') ?></th>
                                <th><?php echo _flexacademy_lang('actions') ?></th>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($categories as $category): 
                                    $parent_category = $category['parent_id'] == 0 ? "" : flexacademy_get_category_name($category['parent_id']);
                                    $status = $category['status'] == 'active' ? _flexacademy_lang('active') : _flexacademy_lang('inactive');
                                    ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            echo $i;
                                            $i++;
                                            ?>
                                        </td>
                                        <td>
                                            <div class="tw-flex tw-items-center">
                                                <?php echo $category['title']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo $category['description']; ?>
                                        </td>
                                        <td>
                                            <?php echo $parent_category; ?>
                                        </td>
                                        <td>
                                            <?php echo $status; ?>
                                        </td>
                                        <td data-order="<?php echo $category['id'] ?>">
                                            <a
                                                href="<?php echo "" ?>"
                                                data-title="<?php echo _flexacademy_lang('edit-category'); ?>"
                                                data-id="<?php echo $category['id']; ?>"
                                                data-name="<?php echo $category['title']; ?>"
                                                data-description="<?php echo $category['description']; ?>"
                                                data-status="<?php echo $category['status']; ?>"
                                                data-parent-id="<?php echo (int)$category['parent_id']; ?>"
                                                data-button-text="<?php echo _flexacademy_lang('update-category'); ?>"
                                                class="btn text-secondary btn-sm flexacademy-create-edit-category">
                                                <i class="fa-solid fa-pencil tw-mr-1"></i>
                                            </a>

                                            <a href="<?php echo admin_url('flexacademy/delete_category/' . $category['id']); ?>"
                                                class="btn text-danger btn-sm _delete">
                                                <i class="fa-solid fa-trash-can tw-mr-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="flexacademy_ajax_url" value="<?php echo admin_url('flexacademy/ajax'); ?>">
<div class="modal fade" id="flexacademy-create-edit-category-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('flexacademy/create_edit_category')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _flexacademy_lang('create-category'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_input('title', 'flexacademy_category-name'); ?>
                <?php echo render_textarea('description', 'flexacademy_description'); ?>
                <!-- visibility -->
                <div class="form-group tw-mb-4">
                    <div class="form-group">
                        <label for="status" class="control-label clearfix"><?php echo _flexacademy_lang('status') ?> </label>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_1_status" name="status" class="ffb-is-required"
                                value="1">
                            <label for="y_opt_1_status">
                                <?php echo _flexacademy_lang('active') ?>
                            </label>
                        </div><br/>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_2_status" name="status" class="ffb-is-required"
                                value="0">
                            <label for="y_opt_2_status">
                                <?php echo _flexacademy_lang('inactive') ?>
                            </label>
                        </div>
                    </div>
                </div>
                <!-- parent category -->
                <div class="form-group">
                    <label for="parent_id" class="control-label clearfix"><?php echo _flexacademy_lang('parent-category') ?> </label>
                    <select name="parent_id" id="parent_id" class="form-control">
                        <option value="0"><?php echo _flexacademy_lang('no-parent') ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['title']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="id" id="category_id" value="0">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _flexacademy_lang('create-category'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!--list all categories in a modal with drag and drop for order-->
<div class="modal fade" id="flexacademy-order-category-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _flexacademy_lang('order-category'); ?></h4>
            </div>
            <div class="modal-body">
                <ul id="flexacademy-order-list" class="flexacademy-order-list" data-type="category" data-success="<?php echo _flexacademy_lang('order-success'); ?>">
                    <?php foreach (flexacademy_get_parent_categories() as $category): ?>
                        <li class="flexacademy-order-item" data-id="<?php echo $category['id']; ?>"><span><i class="fa-solid fa-grip-vertical tw-mr-1"></i></span> <?php echo $category['title']; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _flexacademy_lang('close'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>