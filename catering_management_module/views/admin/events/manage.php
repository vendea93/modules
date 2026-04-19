<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/** @var array $statuses */

?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                                    <i class="fa fa-calendar-alt tw-mr-2"></i>
									<?php echo _l('catering_events'); ?>
                                </h4>
                            </div>
                        </div>

                        <div class="row mb-4">
							<?php
							$status_colors = [
								'enquiry' => 'info',
								'quoted' => 'primary',
								'confirmed' => 'success',
								'in_progress' => 'warning',
								'completed' => 'default',
								'cancelled' => 'danger',
								'lost' => 'muted',
							];

							foreach ($statuses as $status):
								$count = isset($statistics[$status]) ? $statistics[$status] : 0;
								$color = $status_colors[$status] ?? 'default';
								?>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="panel_s bg-<?php echo $color; ?>-light">
                                        <div class="panel-body text-center">
                                            <h3 class="tw-font-bold"><?php echo $count; ?></h3>
                                            <p class="text-<?php echo $color; ?> tw-uppercase tw-text-xs">
												<?php echo _l('event_status_'.$status); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
							<?php endforeach; ?>
                        </div>

                        <!-- View Mode Switcher & Actions -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="btn-group">
                                    <a href="<?php echo admin_url('catering_management_module/events/index?view=table'); ?>"
                                       class="btn btn-default <?php echo $view_mode == 'table' ? 'active' : ''; ?>">
                                        <i class="fa fa-table"></i> <?php echo _l('table_view'); ?>
                                    </a>
                                    <a href="<?php echo admin_url('catering_management_module/events/index?view=kanban'); ?>"
                                       class="btn btn-default <?php echo $view_mode == 'kanban' ? 'active' : ''; ?>">
                                        <i class="fa fa-columns"></i> <?php echo _l('kanban_view'); ?>
                                    </a>
                                    <a href="<?php echo admin_url('catering_management_module/events/index?view=timeline'); ?>"
                                       class="btn btn-default <?php echo $view_mode == 'timeline' ? 'active' : ''; ?>">
                                        <i class="fa fa-calendar"></i> <?php echo _l('timeline_view'); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
								<?php if (staff_can('create', 'catering')): ?>
                                    <a href="<?php echo admin_url('catering_management_module/events/event'); ?>"
                                       class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('new_event'); ?>
                                    </a>
								<?php endif; ?>
                            </div>
                        </div>

                        <hr class="hr-panel-separator">

                        <!-- View Content -->
						<?php
						switch ($view_mode)
						{
							case 'kanban':
								$this->load->view(CATERING_MANAGEMENT_MODULE_NAME.'/admin/events/kanban_view');
								break;
							case 'timeline':
								$this->load->view(CATERING_MANAGEMENT_MODULE_NAME.'/admin/events/timeline_view');
								break;
							default:
								$this->load->view(CATERING_MANAGEMENT_MODULE_NAME.'/admin/events/table_view');
								break;
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

    $(function () {
        const viewMode = '<?php echo $view_mode; ?>';
        if (viewMode === 'kanban') {
            initKanban();
        } else if (viewMode === 'timeline') {
            initTimeline();
        } else {
            initTable();
        }
    });

    function initTable() {
        initDataTable(
            ".table-events",
            admin_url + "catering_management_module/events/table",
            [],
            [],
            {},
            [0, "desc"],
        );
    }

    function initKanban() {
        init_kanban(
            'catering_management_module/events/events_kanban', // URL for refresh
            events_kanban_update, // Update callback
            '.events-status', // Connect with selector
            300, // Column pixel width
            400, // Container pixel height
            function () {
                // After load callback
                $('[data-toggle="tooltip"]').tooltip();
            }
        );
        // init_kanban("catering_management_module/events/events_kanban", events_kanban_update, ".kan-ban-col", 240, 360);
    }

    // Updates task when action performed form kan ban area eq status changed.
    function events_kanban_update(ui, object) {
        if (object === ui.item.parent()[0]) {
            const status = $(ui.item.parent()[0]).attr("data-event-status-id");

            const data = {
                order: [],
                status: status,
            };

            $.each($(ui.item.parent()[0]).find("[data-event-id]"), function (idx, el) {
                var id = $(el).attr("data-event-id");
                if (id) {
                    data.order.push([id, idx + 1]);
                }
            });

            event_mark_as(status, $(ui.item).attr("data-event-id"), $(ui.item).attr("data-event-status"));
            check_kanban_empty_col("[data-event-id]");

            setTimeout(function () {
                $.post(admin_url + "catering_management_module/events/update_order", data)
                    .done(function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert_float('success', response.message);
                        } else {
                            alert_float('danger', response.message);
                            // Revert on failure
                            $(object).sortable('cancel');
                        }
                        update_kan_ban_total_when_moving(ui, data.status);
                        initKanban();
                    })
                    .fail(function () {
                        alert_float('danger', '<?= _l('something_went_wrong'); ?>');
                        $(status_container).sortable('cancel');
                    });
            }, 200);
        }
    }


    // Mark event status
    function event_mark_as(status, event_id, old_status) {
        if (status === old_status) {
            return false;
        }
        const data = {
            event_id,
            status
        }
        $.post(admin_url + "catering_management_module/events/update_event_status", data)
            .done(function (response) {
                if (response.success === true || response.success === "true") {
                    if ($(".events-kanban").length === 0) {
                        alert_float("success", response.message);
                    }
                }
            });
    }

    function initTimeline() {
        const calendar_selector = document.getElementById('events_calendar');
        const calendar_settings = {
            customButtons: {},
            locale: app.locale,
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            },
            editable: false,
            dayMaxEventRows: parseInt(app.options.calendar_events_limit) + 1,

            views: {
                day: {
                    dayMaxEventRows: false,
                },
            },

            direction: isRTL == "true" ? "rtl" : "ltr",
            eventStartEditable: false,
            firstDay: parseInt(app.options.calendar_first_day),
            initialView: app.options.default_view_calendar,
            timeZone: app.options.timezone,

            loading: function (isLoading, view) {
                !isLoading
                    ? $(".dt-loader").addClass("hide")
                    : $(".dt-loader").removeClass("hide");
            },

            eventSources: [
                function (info, successCallback, failureCallback) {
                    return $.getJSON(
                        admin_url + "catering_management_module/events/get_calendar_events",
                        $.extend({}, [], {
                            start: info.startStr,
                            end: info.endStr,
                        })
                    ).then(function (data) {
                        successCallback(
                            data.map(function (e) {
                                return $.extend({}, e, {
                                    start: e.start || e.date,
                                    end: e.end || e.date,
                                });
                            })
                        );
                    });
                },
            ],

            moreLinkClick: function (info) {
                calendar.gotoDate(info.date);
                calendar.changeView("dayGridDay");

                setTimeout(function () {
                    $(".fc-popover-close").click();
                }, 250);
            },

            eventDidMount: function (data) {
                const $el = $(data.el);
                $el.attr("title", data.event.extendedProps._tooltip);
                $el.attr("onclick", data.event.extendedProps.onclick);
                $el.attr("data-toggle", "tooltip");
            },

            dateClick: function (info) {
            },
        };
        const calendar = new FullCalendar.Calendar(
            calendar_selector,
            calendar_settings
        );
        calendar.render();
    }
</script>