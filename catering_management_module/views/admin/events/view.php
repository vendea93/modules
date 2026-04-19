<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <!-- Header with Actions -->
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-xl tw-flex tw-items-center">
                                    <i class="fa fa-calendar tw-mr-2"></i>
									<?php echo $event->event_name; ?>
                                    <small class="tw-ml-3 text-muted">#<?php echo $event->eventid; ?></small>
                                </h4>

								<?php
								$statusColors = [
									'enquiry' => 'info',
									'quoted' => 'primary',
									'confirmed' => 'success',
									'in_progress' => 'warning',
									'completed' => 'default',
									'cancelled' => 'danger',
									'lost' => 'muted',
								];
								$statusColor = $statusColors[$event->status] ?? 'default';
								?>

                                <span class="label label-<?php echo $statusColor; ?> label-lg">
                                    <?php echo _l('event_status_'.$event->status); ?>
                                </span>
                            </div>

                            <div class="col-md-4 text-right">
								<?php if (staff_can('edit', 'catering_events')): ?>
                                    <a href="<?php echo admin_url('catering_management_module/events/event/'.$event->eventid); ?>"
                                       class="btn btn-default">
                                        <i class="fa fa-pencil"></i> <?php echo _l('edit'); ?>
                                    </a>
								<?php endif; ?>

								<?php if (staff_can('delete', 'catering_events')): ?>
                                    <a href="<?php echo admin_url('catering_management_module/events/delete_event/'.$event->eventid); ?>"
                                       class="btn btn-danger _delete">
                                        <i class="fa fa-remove"></i> <?php echo _l('delete'); ?>
                                    </a>
								<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Tabs -->
                <div class="panel_s">
                    <div class="panel-body">

                        <div class="horizontal-scrollable-tabs">
                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>

                            <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#tab_overview" aria-controls="tab_overview" role="tab" data-toggle="tab">
										<?php echo _l('overview'); ?>
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#tab_menu" aria-controls="tab_menu" role="tab" data-toggle="tab">
										<?php echo _l('menu'); ?>
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#tab_staffing" aria-controls="tab_staffing" role="tab" data-toggle="tab">
										<?php echo _l('staffing'); ?>
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#tab_finance" aria-controls="tab_finance" role="tab" data-toggle="tab">
										<?php echo _l('finance'); ?>
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#tab_documents" aria-controls="tab_documents" role="tab" data-toggle="tab">
										<?php echo _l('documents'); ?>
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#tab_notes" aria-controls="tab_notes" role="tab" data-toggle="tab">
										<?php echo _l('notes'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content mtop30">

                            <!-- Overview Tab -->
                            <div role="tabpanel" class="tab-pane active" id="tab_overview">
								<?php $this->load->view('admin/events/tabs/overview'); ?>
                            </div>

                            <!-- Menu Tab -->
                            <div role="tabpanel" class="tab-pane" id="tab_menu">
								<?php $this->load->view('admin/events/tabs/menu'); ?>
                            </div>

                            <!-- Staffing Tab -->
                            <div role="tabpanel" class="tab-pane" id="tab_staffing">
								<?php $this->load->view('admin/events/tabs/staffing'); ?>
                            </div>


                            <!-- Finance Tab -->
                            <div role="tabpanel" class="tab-pane" id="tab_finance">
								<?php $this->load->view('admin/events/tabs/finance'); ?>
                            </div>

                            <!-- Documents Tab -->
                            <div role="tabpanel" class="tab-pane" id="tab_documents">
								<?php $this->load->view('admin/events/tabs/documents'); ?>
                            </div>

                            <!-- Notes Tab -->
                            <div role="tabpanel" class="tab-pane" id="tab_notes">
								<?php $this->load->view('admin/events/tabs/notes'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    Dropzone.autoDiscover = false;
    $(document).ready(function () {
        new Dropzone('#event-dropzone', {
            paramName: "file",
            maxFilesize: 10,
            acceptedFiles: "application/pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif",
            addRemoveLinks: true,
            success: function (file, response) {
                if (response.success) {
                    alert_float('success', response.message);
                    setTimeout(function () {
                        location.reload();
                    }, 1500);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function (file, response) {
                alert_float('danger', 'Upload failed: ' + response);
            }
        })
    });


</script>
