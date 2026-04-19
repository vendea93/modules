<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <?php echo form_hidden('project_id', $project->id) ?>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons">
                    <div class="row">
                        <div class="col-md-7 project-heading">
                            <div class="tw-flex tw-flex-wrap tw-items-center">
                                <h3 class="project-name"><?php echo e($project->project_name); ?></h3>
                                <div id="project_view_name" class="tw-mr-3 tw-max-w-[350px] hide">
                                    <div class="tw-w-full">
                                        <select class="selectpicker" id="project_top" data-width="100%"
                                            <?php if (count($other_projects) > 6) { ?> data-live-search="true"
                                            <?php } ?>>
                                            <option value="<?php echo e($project->id); ?>" selected
                                                data-content="<?php echo e($project->project_name); ?>">
                                                <?php echo e($project->project_name); ?>
                                            </option>
                                            <?php foreach ($other_projects as $op) { ?>
                                            <option value="<?php echo e($op['id']); ?>"
                                                >#<?php echo e($op['id']); ?> -
                                                <?php echo e($op['project_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="visible-xs">
                                    <div class="clearfix"></div>
                                </div>

                               
                            </div>
                        </div>
                        <div class="col-md-5 text-right tw-space-x-1">
                            <div class="btn-group">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="project-menu-panel tw-my-5">
                    <?php hooks()->do_action('before_render_project_view', $project->id); ?>
                    <?php $this->load->view('reputation/projects/project_tabs'); ?>
                </div>
               

                <?php $this->load->view(($tab ? 'reputation/projects/includes/'.$tab : 'reputation/projects/includes/keywords')); ?>

            </div>
        </div>
    </div>
</div>
</div>
</div>

<?php init_tail(); ?>
<?php require 'modules/reputation/assets/js/projects/project_js.php'; ?>
</body>
</html>