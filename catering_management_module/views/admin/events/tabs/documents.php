<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-md-8">

        <!-- Upload Files -->
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-cloud-upload tw-mr-2"></i>
					<?php echo _l('upload_files'); ?>
                </h4>

                <div id="dropzone-wrapper">
                    <form action="<?php echo admin_url('catering_management_module/events/upload_document_attachment/'.$event->eventid); ?>"
                          class="dropzone" id="event-dropzone">
                        <div class="dz-message">
                            <i class="fa fa-cloud-upload fa-3x"></i>
                            <h4><?php echo _l('drop_files_here_to_upload'); ?></h4>
                            <span><?php echo _l('or_click_to_browse'); ?></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Attached Files -->
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-paperclip tw-mr-2"></i>
					<?php echo _l('attached_files'); ?>
                </h4>

				<?php if ( ! empty($event->attachments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th width="5%"></th>
                                <th><?php echo _l('file_name'); ?></th>
                                <th><?php echo _l('file_type'); ?></th>
                                <th><?php echo _l('uploaded_by'); ?></th>
                                <th><?php echo _l('date'); ?></th>
                                <th><?php echo _l('size'); ?></th>
                                <th><?php echo _l('options'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php foreach ($event->attachments as $file): ?>
								<?php
								$download_url = admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/events/download_attachment/'.$event->eventid.'?file_id='.$file['id']);

								?>
                                <tr>
                                    <td>
                                        <!--   <i class="--><?php //echo get_mime_icon($file['filetype']); ?><!-- fa-2x"></i>-->
                                    </td>
                                    <td>
                                        <a href="<?php echo $download_url ?>"
                                           class="tw-font-medium">
											<?php echo $file['file_name']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo strtoupper($file['filetype']); ?></td>
                                    <td><?php echo get_staff_full_name($file['staffid']); ?></td>
                                    <td><?php echo _dt($file['dateadded']); ?></td>
                                    <td><?php echo bytesToSize('', $file['file_name']); ?></td>
                                    <td>
                                        <a href="<?php echo $download_url ?>"
                                           class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo _l('download'); ?>">
                                            <i class="fa fa-download"></i>
                                        </a>
										<?php if (staff_can('delete', 'catering_events')): ?>
                                            <button class="btn btn-danger btn-xs delete-file"
                                                    data-id="<?php echo $file['id']; ?>"
                                                    data-toggle="tooltip"
                                                    title="<?php echo _l('delete'); ?>">
                                                <i class="fa fa-remove"></i>
                                            </button>
										<?php endif; ?>
                                    </td>
                                </tr>
							<?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
				<?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <?php echo _l('no_files_attached'); ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>

        <!-- Generated PDFs - TODO: Implement PDF generation -->
		<?php /*
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-file-pdf-o tw-mr-2"></i>
					<?php echo _l('generated_documents'); ?>
                </h4>

                <div class="list-group">
                    <a href="#" class="list-group-item">
                        <div class="media">
                            <div class="media-left">
                                <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                            </div>
                            <div class="media-body">
                                <h5 class="media-heading tw-font-medium">
									<?php echo _l('event_proposal'); ?>
                                    <span class="label label-success pull-right">Ready</span>
                                </h5>
                                <p class="text-muted">Complete proposal with menu, pricing, and terms</p>
                                <div class="btn-group btn-group-xs">
                                    <button class="btn btn-default">
                                        <i class="fa fa-eye"></i> <?php echo _l('preview'); ?>
                                    </button>
                                    <button class="btn btn-default">
                                        <i class="fa fa-download"></i> <?php echo _l('download'); ?>
                                    </button>
                                    <button class="btn btn-default">
                                        <i class="fa fa-envelope"></i> <?php echo _l('email'); ?>
                                    </button>
                                    <button class="btn btn-primary">
                                        <i class="fa fa-refresh"></i> <?php echo _l('regenerate'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="#" class="list-group-item">
                        <div class="media">
                            <div class="media-left">
                                <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                            </div>
                            <div class="media-body">
                                <h5 class="media-heading tw-font-medium">
									<?php echo _l('allergen_report'); ?>
                                    <span class="label label-success pull-right">Ready</span>
                                </h5>
                                <p class="text-muted">Detailed allergen and dietary information</p>
                                <div class="btn-group btn-group-xs">
                                    <button class="btn btn-default">
                                        <i class="fa fa-eye"></i> <?php echo _l('preview'); ?>
                                    </button>
                                    <button class="btn btn-default">
                                        <i class="fa fa-download"></i> <?php echo _l('download'); ?>
                                    </button>
                                    <button class="btn btn-default">
                                        <i class="fa fa-envelope"></i> <?php echo _l('email'); ?>
                                    </button>
                                    <button class="btn btn-primary">
                                        <i class="fa fa-refresh"></i> <?php echo _l('regenerate'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="#" class="list-group-item">
                        <div class="media">
                            <div class="media-left">
                                <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                            </div>
                            <div class="media-body">
                                <h5 class="media-heading tw-font-medium">
									<?php echo _l('packing_list'); ?>
                                    <span class="label label-warning pull-right">Outdated</span>
                                </h5>
                                <p class="text-muted">Equipment and supplies checklist</p>
                                <div class="btn-group btn-group-xs">
                                    <button class="btn btn-default">
                                        <i class="fa fa-eye"></i> <?php echo _l('preview'); ?>
                                    </button>
                                    <button class="btn btn-default">
                                        <i class="fa fa-download"></i> <?php echo _l('download'); ?>
                                    </button>
                                    <button class="btn btn-default">
                                        <i class="fa fa-envelope"></i> <?php echo _l('email'); ?>
                                    </button>
                                    <button class="btn btn-primary">
                                        <i class="fa fa-refresh"></i> <?php echo _l('regenerate'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="#" class="list-group-item">
                        <div class="media">
                            <div class="media-left">
                                <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                            </div>
                            <div class="media-body">
                                <h5 class="media-heading tw-font-medium">
									<?php echo _l('run_sheet'); ?>
                                    <span class="label label-default pull-right">Not Generated</span>
                                </h5>
                                <p class="text-muted">Event timeline and staff roster</p>
                                <div class="btn-group btn-group-xs">
                                    <button class="btn btn-primary">
                                        <i class="fa fa-cog"></i> <?php echo _l('generate'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
		*/ ?>

    </div>

    <!-- Right Sidebar -->
    <div class="col-md-4">

        <!-- Document Summary -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-bar-chart tw-mr-2"></i>
					<?php echo _l('document_summary'); ?>
                </h5>
                <div class="tw-space-y-3">
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('attached_files'); ?>:</span>
                        <strong><?php echo count($event->attachments); ?></strong>
                    </div>
					<?php
					// Calculate total file size
					$total_size = 0;
					foreach ($event->attachments as $file)
					{
						$file_path = get_upload_path_by_type('catering_event').$event->eventid.'/'.$file['file_name'];
						if (file_exists($file_path))
						{
							$total_size += filesize($file_path);
						}
					}
					?>
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('total_size'); ?>:</span>
                        <strong><?php echo bytesToSize('', $total_size); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Allowed File Types -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-info-circle tw-mr-2"></i>
					<?php echo _l('allowed_file_types'); ?>
                </h5>
                <div class="alert alert-info alert-sm">
                    <strong>Documents:</strong> PDF, DOC, DOCX, XLS, XLSX, TXT<br>
                    <strong>Images:</strong> JPG, PNG, GIF<br>
                    <strong>Max size:</strong> 10 MB
                </div>
            </div>
        </div>

        <!-- Quick Generate - TODO: Implement PDF generation -->
		<?php /*
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-magic tw-mr-2"></i>
					<?php echo _l('quick_generate'); ?>
                </h5>
                <button class="btn btn-primary btn-block btn-sm">
                    <i class="fa fa-file-pdf-o"></i> <?php echo _l('proposal'); ?>
                </button>
                <button class="btn btn-warning btn-block btn-sm">
                    <i class="fa fa-exclamation-triangle"></i> <?php echo _l('allergen_report'); ?>
                </button>
                <button class="btn btn-info btn-block btn-sm">
                    <i class="fa fa-list"></i> <?php echo _l('packing_list'); ?>
                </button>
                <button class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-clock-o"></i> <?php echo _l('run_sheet'); ?>
                </button>
            </div>
        </div>

        <!-- Email Documents -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-envelope tw-mr-2"></i>
					<?php echo _l('email_documents'); ?>
                </h5>
                <div class="form-group">
                    <label><?php echo _l('recipient'); ?></label>
                    <input type="email" class="form-control"
                           value="<?php echo $event->client_company ? 'get_primary_contact_email($event->client_id)' : ''; ?>"
                           placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label><?php echo _l('select_documents'); ?></label>
                    <div class="checkbox">
                        <input type="checkbox" id="email_proposal" checked>
                        <label for="email_proposal"><?php echo _l('proposal'); ?></label>
                    </div>
                    <div class="checkbox">
                        <input type="checkbox" id="email_allergen" checked>
                        <label for="email_allergen"><?php echo _l('allergen_report'); ?></label>
                    </div>
                </div>
                <button class="btn btn-primary btn-block">
                    <i class="fa fa-send"></i> <?php echo _l('send_email'); ?>
                </button>
            </div>
        </div>
		*/ ?>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $(function () {
            // Initialize Dropzone

            // Delete file
            $('.delete-file').on('click', function () {
                var fileId = $(this).data('id');
                var row = $(this).closest('tr');

                if (confirm('<?php echo _l('confirm_delete_file'); ?>')) {
                    $.post(admin_url + 'catering_management_module/events/delete_attachment/' + fileId)
                        .done(function (response) {
                            response = JSON.parse(response);
                            if (response.success) {
                                row.fadeOut(function () {
                                    $(this).remove();
                                });
                                alert_float('success', '<?php echo _l('file_deleted_successfully'); ?>');
                            } else {
                                alert_float('danger', '<?php echo _l('file_delete_failed'); ?>');
                            }
                        });
                }
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    })
</script>