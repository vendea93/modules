<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/** @var array $event */
/** @var array $status */
?>
<li data-event-id="<?= e($event['eventid']); ?>"
    data-event-status="<?= e($event['status']); ?>"
    class="event">
    <div class="panel-body tw-px-4 tw-py-5 tw-mx-1 tw-my-2">
        <div class="row">
            <div class="col-md-12 event-name">
                <a href="<?php echo admin_url('catering_management_module/events/event/'.$event['eventid']); ?>"
                   class="tw-font-medium tw-text-neutral-800"
                   onclick="event.stopPropagation();">
					<?php echo '#'.$event['eventid'].' '.$event['event_name']; ?>
                </a>
            </div>
            <div class="col-md-12">
                <div class="tw-mt-2 tw-text-sm text-muted">
					<?php if ($event['client_company'] || $event['lead_name']): ?>
                        <div>
                            <i class="fa fa-building-o"></i>
							<?php echo $event['client_company'] ?: $event['lead_name']; ?>
                        </div>
					<?php endif; ?>

                    <div class="tw-mt-1">
                        <i class="fa fa-calendar"></i>
						<?php echo _dt($event['event_start']); ?>
                    </div>

					<?php if ($event['venue_name']): ?>
                        <div class="tw-mt-1">
                            <i class="fa fa-map-marker"></i>
							<?php echo $event['venue_name']; ?>
                        </div>
					<?php endif; ?>

                    <div class="tw-mt-1">
                        <i class="fa fa-users"></i>
						<?php echo $event['guest_count_expected']; ?> guests
                    </div>
                </div>
            </div>
        </div>

		<?php if (!empty($event['event_end'])): ?>
            <div class="row mtop10">
                <div class="tw-text-sm col-md-12 text-right">
                    <span class="label label-default"
                          data-toggle="tooltip"
                          title="<?= _l('event_end'); ?>">
                        <i class="fa fa-calendar-check-o"></i>
                        <?= e(_d($event['event_end'])); ?>
                    </span>
                </div>
            </div>
		<?php endif; ?>
    </div>
</li>