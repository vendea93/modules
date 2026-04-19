<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * @var array $statuses_display
 */
?>
<div id="events-kan-ban">
	<?php
	foreach ($statuses_display as $status)
	{
		$kanBan = new Events_kanban($status['id']);
		$kanBan->search($this->input->get('search'))
			->sortBy($this->input->get('sort_by'), $this->input->get('sort'));

		if ($this->input->get('refresh'))
		{
			$kanBan->refresh($this->input->get('refresh')[$status['id']] ?? NULL);
		}

		$events = $kanBan->get();
		$total_events = count($events);
		$total_pages = $kanBan->totalPages();

		?>
        <ul class="kan-ban-col events-kanban"
            data-col-status-id="<?= e($status['id']); ?>"
            data-total-pages="<?= e($total_pages); ?>"
            data-total="<?= e($total_events); ?>">
            <li class="kan-ban-col-wrapper">
                <div class="border-right panel_s">
                    <div class="panel-heading tw-font-medium"
                         style="background:<?= e($status['background-color']); ?>;border-color:<?= e($status['background-color']); ?>;color: <?= e($status['text-color']) ?>"
                         data-status-id="<?= e($status['id']); ?>">
						<?= $status['label'] ?>
                        -
                        <span class="tw-text-sm">
                    <?= $kanBan->countAll().' '._l('events') ?>
                </span>
                    </div>
                    <div class="kan-ban-content-wrapper">
                        <div class="kan-ban-content">
                            <ul class="status events-status sortable relative"
                                data-event-status-id="<?= e($status['id']); ?>">
								<?php
								foreach ($events as $event)
								{
									if ($event['status'] == $status['id'])
									{
										$this->load->view(CATERING_MANAGEMENT_MODULE_NAME.'/admin/events/_kan_ban_card', ['event' => $event, 'status' => $status]);
									}
								} ?>
								<?php if ($total_events > 0) { ?>
                                    <li class="text-center not-sortable kanban-load-more"
                                        data-load-status="<?= e($status['id']); ?>">
                                        <a href="#" class="btn btn-default btn-block<?php if ($total_pages <= 1 || $kanBan->getPage() == $total_pages)
										{
											echo ' disabled';
										} ?>"
                                           data-page="<?= $kanBan->getPage(); ?>"
                                           onclick="kanban_load_more('<?= e($status['id']); ?>',this,'catering_management_module/events/events_kanban_load_more',265,360); return false;"
                                           ;><?= _l('load_more'); ?></a>
                                    </li>
								<?php } ?>
                                <li class="text-center not-sortable mtop30 kanban-empty<?php if ($total_events > 0)
								{
									echo ' hide';
								} ?>">
                                    <h4>
                                        <i class="fa-solid fa-circle-notch" aria-hidden="true"></i><br/><br/>
										<?= _l('no_events_found'); ?>
                                    </h4>
                                </li>
                            </ul>
                        </div>
                    </div>
            </li>
        </ul>
	<?php } ?>
</div>