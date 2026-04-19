<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/** @var object $event */
/** @var object $event_menu */
/** @var array $menus */
/** @var array $packages */
/** @var array $menu_items */
/** @var array $menu_sections */
/** @var array $event_staff */
/** @var array $event_menu_summary */
/** @var array $staff_summary */
/** @var array $financials_summary */
/** @var array $notes_stats */

?>
<div class="row">

    <!-- Left Column: Event Details -->
    <div class="col-md-8">

        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-info-circle tw-mr-2"></i>
					<?php echo _l('event_details'); ?>
                </h4>

                <table class="table table-borderless">
                    <tbody>
                    <tr>
                        <td class="tw-font-medium" width="30%"><?php echo _l('event_name'); ?>:</td>
                        <td><?php echo $event->event_name; ?></td>
                    </tr>

					<?php if ($event->client_company): ?>
                        <tr>
                            <td class="tw-font-medium"><?php echo _l('client'); ?>:</td>
                            <td>
                                <a href="<?php echo admin_url('clients/client/'.$event->client_id); ?>">
									<?php echo $event->client_company; ?>
                                </a>
                            </td>
                        </tr>
					<?php endif; ?>

					<?php if ($event->event_type_name): ?>
                        <tr>
                            <td class="tw-font-medium"><?php echo _l('event_type'); ?>:</td>
                            <td><?php echo $event->event_type_name; ?></td>
                        </tr>
					<?php endif; ?>

                    <tr>
                        <td class="tw-font-medium"><?php echo _l('event_start'); ?>:</td>
                        <td><?php echo _dt($event->event_start); ?></td>
                    </tr>

					<?php if ($event->event_end): ?>
                        <tr>
                            <td class="tw-font-medium"><?php echo _l('event_end'); ?>:</td>
                            <td><?php echo _dt($event->event_end); ?></td>
                        </tr>
					<?php endif; ?>

                    <tr>
                        <td class="tw-font-medium"><?php echo _l('venue'); ?>:</td>
                        <td>
							<?php if ($event->venue_name): ?>
                                <strong><?php echo $event->venue_name; ?></strong><br>
								<?php if ($event->venue_address): ?>
                                    <small class="text-muted"><?php echo nl2br($event->venue_address); ?></small>
								<?php endif; ?>
							<?php else: ?>
                                -
							<?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tw-font-medium"><?php echo _l('guest_count'); ?>:</td>
                        <td>
                            <strong><?php echo $event->guest_count_expected; ?></strong> expected
							<?php if ($event->guest_count_final): ?>
                                <i class="fa fa-arrow-right"></i>
                                <strong class="text-success"><?php echo $event->guest_count_final; ?></strong> final
							<?php endif; ?>
                        </td>
                    </tr>

					<?php if ($event->dietary_notes): ?>
                        <tr>
                            <td class="tw-font-medium"><?php echo _l('dietary_notes'); ?>:</td>
                            <td><?php echo nl2br($event->dietary_notes); ?></td>
                        </tr>
					<?php endif; ?>

					<?php if ($event->project_name): ?>
                        <tr>
                            <td class="tw-font-medium"><?php echo _l('linked_project'); ?>:</td>
                            <td>
                                <a href="<?php echo admin_url('projects/view/'.$event->project_id); ?>">
									<?php echo $event->project_name; ?>
                                </a>
                            </td>
                        </tr>
					<?php endif; ?>

                    <tr>
                        <td class="tw-font-medium"><?php echo _l('created_by'); ?>:</td>
                        <td>
							<?php echo $event->created_by_name; ?>
                            <small class="text-muted">on <?php echo _dt($event->created_at); ?></small>
                        </td>
                    </tr>

					<?php if ($event->updated_at): ?>
                        <tr>
                            <td class="tw-font-medium"><?php echo _l('last_updated'); ?>:</td>
                            <td><?php echo _dt($event->updated_at); ?></td>
                        </tr>
					<?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Internal Notes -->
		<?php if ($event->internal_notes): ?>
            <div class="panel_s">
                <div class="panel-body">
                    <h4 class="tw-font-semibold tw-mb-4">
                        <i class="fa fa-sticky-note-o tw-mr-2"></i>
						<?php echo _l('internal_notes'); ?>
                    </h4>
                    <div class="text-muted">
						<?php echo nl2br($event->internal_notes); ?>
                    </div>
                </div>
            </div>
		<?php endif; ?>

    </div>

    <!-- Right Column: Quick Actions & Timeline -->
    <div class="col-md-4">

        <!-- Status Change -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold"><?php echo _l('change_status'); ?></h5>
                <select id="quick-status-change" class="selectpicker" data-width="100%">
					<?php foreach ($event_statuses as $status): ?>
                        <option value="<?php echo $status; ?>"
							<?php if ($event->status == $status)
							{
								echo 'selected';
							} ?>>
							<?php echo _l('event_status_'.$status); ?>
                        </option>
					<?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Estimate Actions -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3"><?php echo _l('estimate'); ?></h5>

				<?php if ($event->estimate_id): ?>
                    <div class="alert alert-success tw-mb-3">
                        <i class="fa fa-check-circle"></i>
						<?php echo _l('estimate_generated'); ?>
                    </div>
                    <a href="<?php echo admin_url('estimates/list_estimates/'.$event->estimate_id); ?>"
                       class="btn btn-info btn-block tw-mb-2">
                        <i class="fa fa-file-text-o"></i>
						<?php echo _l('view_estimate'); ?>
                    </a>

                    <div class="row">
                        <div class="col-xs-6">
                            <a href="<?php echo admin_url('catering_management_module/events/regenerate_estimate/'.$event->eventid); ?>"
                               class="btn btn-warning btn-block btn-sm"
                               onclick="return confirm('<?php echo _l('confirm_regenerate_estimate'); ?>');">
                                <i class="fa fa-refresh"></i>
								<?php echo _l('regenerate'); ?>
                            </a>
                        </div>
                        <div class="col-xs-6">
                            <a href="<?php echo admin_url('catering_management_module/events/unlink_estimate/'.$event->eventid); ?>"
                               class="btn btn-danger btn-block btn-sm"
                               onclick="return confirm('<?php echo _l('confirm_unlink_estimate'); ?>');">
                                <i class="fa fa-unlink"></i>
								<?php echo _l('unlink'); ?>
                            </a>
                        </div>
                    </div>
				<?php else: ?>
					<?php if ($event->client_id): ?>
                        <p class="text-muted tw-mb-3">
							<?php echo _l('generate_estimate_description'); ?>
                        </p>
                        <a href="<?php echo admin_url('catering_management_module/events/generate_estimate/'.$event->eventid); ?>"
                           class="btn btn-success btn-block"
                           onclick="return confirm('<?php echo _l('confirm_generate_estimate'); ?>');">
                            <i class="fa fa-plus-circle"></i>
							<?php echo _l('generate_estimate'); ?>
                        </a>
					<?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
							<?php echo _l('event_must_have_client_to_generate_estimate'); ?>
                        </div>
					<?php endif; ?>
				<?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3"><?php echo _l('quick_stats'); ?></h5>

                <div class="tw-space-y-3">
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('staff_assigned'); ?>:</span>
                        <strong><?php echo number_format($staff_summary['total_staff']) ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('menu_items'); ?>:</span>
                        <strong><?php echo number_format($event_menu_summary['total_items']) ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('financial'); ?>:</span>
                        <strong><?php echo app_format_money($financials_summary['total_revenue'] ?? 0, get_base_currency()) ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('attachments'); ?>:</span>
                        <strong><?php echo count($event->attachments); ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('notes'); ?>:</span>
                        <strong><?php echo number_format($notes_stats['total']) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Public Link -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3"><?php echo _l('public_link'); ?></h5>
                <div class="input-group">
                    <input type="text" class="form-control"
                           value="<?php echo site_url('catering/public/'.$event->hash); ?>"
                           id="public-link" readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="copyToClipboard('#public-link')">
                            <i class="fa fa-copy"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        $(function () {
            $('#quick-status-change').on('change', function () {
                var newStatus = $(this).val();

                if (confirm('<?php echo _l('confirm_status_change'); ?>')) {
                    $.post(admin_url + 'catering_management_module/events/change_event_status', {
                        id: <?php echo $event->eventid; ?>,
                        status: newStatus
                    }).done(function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert_float('success', response.message);
                            setTimeout(function () {
                                location.reload();
                            }, 1500)
                        } else {
                            alert_float('danger', response.message);
                        }
                    });
                } else {
                    $(this).selectpicker('val', '<?php echo $event->status; ?>');
                }
            });
        });
    });

    function copyToClipboard(element) {
        $(element).select();
        document.execCommand('copy');
        alert_float('success', '<?php echo _l('link_copied'); ?>');
    }
</script>