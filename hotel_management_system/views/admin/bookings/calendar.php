<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
								<?php echo _l('bookings_calendar'); ?>
                                <a href="<?php echo admin_url('hotel_management_system/bookings'); ?>"
                                   class="btn btn-default pull-right">
                                    <i class="fa fa-list"></i> <?php echo _l('list_view'); ?>
                                </a>
                                <a href="<?php echo admin_url('hotel_management_system/bookings/booking'); ?>"
                                   class="btn btn-primary pull-right mright5">
                                    <i class="fa fa-plus"></i> <?php echo _l('new_booking'); ?>
                                </a>
                                <hr/>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('filters'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label for="filter_property"><?php echo _l('property'); ?></label>
                                            <select id="filter_property" class="form-control selectpicker"
                                                    data-live-search="true">
                                                <option value=""><?php echo _l('all_properties'); ?></option>
												<?php foreach ($properties as $property) { ?>
                                                    <option value="<?php echo $property['id']; ?>"><?php echo $property['name']; ?></option>
												<?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="filter_room"><?php echo _l('room'); ?></label>
                                            <select id="filter_room" class="form-control selectpicker"
                                                    data-live-search="true">
                                                <option value=""><?php echo _l('all_rooms'); ?></option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="filter_status"><?php echo _l('booking_status'); ?></label>
                                            <select id="filter_status" class="form-control selectpicker">
                                                <option value=""><?php echo _l('all_statuses'); ?></option>
												<?php foreach (hms_get_booking_statuses() as $status => $label) { ?>
                                                    <option value="<?php echo $status; ?>"><?php echo $label; ?></option>
												<?php } ?>
                                            </select>
                                        </div>

                                        <button id="apply_filters"
                                                class="btn btn-info btn-block"><?php echo _l('apply_filters'); ?></button>
                                        <button id="reset_filters"
                                                class="btn btn-default btn-block mtop5"><?php echo _l('reset_filters'); ?></button>
                                    </div>
                                </div>

                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('calendar_options'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="calendar_view" id="view_month"
                                                       value="dayGridMonth" checked>
												<?php echo _l('month_view'); ?>
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="calendar_view" id="view_week"
                                                       value="timeGridWeek">
												<?php echo _l('week_view'); ?>
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="calendar_view" id="view_resource"
                                                       value="resourceTimelineMonth">
												<?php echo _l('resources_view'); ?> (<?php echo _l('by_room'); ?>)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('legend'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <ul class="calendar-legend">
                                            <li>
                                                <span class="legend-box" style="background-color: #2C3E50;"></span>
                                                <span class="legend-label"><?php echo _l('confirmed'); ?></span>
                                            </li>
                                            <li>
                                                <span class="legend-box" style="background-color: #27AE60;"></span>
                                                <span class="legend-label"><?php echo _l('checked_in'); ?></span>
                                            </li>
                                            <li>
                                                <span class="legend-box" style="background-color: #95A5A6;"></span>
                                                <span class="legend-label"><?php echo _l('checked_out'); ?></span>
                                            </li>
                                            <li>
                                                <span class="legend-box" style="background-color: #E74C3C;"></span>
                                                <span class="legend-label"><?php echo _l('no_show'); ?></span>
                                            </li>
                                            <li>
                                                <span class="legend-box" style="background-color: #F39C12;"></span>
                                                <span class="legend-label"><?php echo _l('pending'); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('booking_stats'); ?></h4>
                                    </div>
                                    <div class="panel-body" id="booking-stats-panel">
                                        <div class="text-center">
                                            <div class="spinner-border" role="status">
                                                <span class="sr-only"><?php echo _l('loading'); ?>...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="booking_details_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('booking_details'); ?></h4>
            </div>
            <div class="modal-body" id="booking_details_content">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only"><?php echo _l('loading'); ?>...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <a href="#" class="btn btn-info" id="view_booking_btn"><?php echo _l('view_details'); ?></a>
                <a href="#" class="btn btn-success" id="edit_booking_btn"><?php echo _l('edit'); ?></a>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize the calendar
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['interaction', 'dayGrid', 'timeGrid', 'resourceTimeline'],
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,resourceTimelineMonth'
            },
            initialView: 'dayGridMonth',
            editable: false,
            selectable: true,
            selectMirror: true,
            eventLimit: true,
            navLinks: true,
            resourceLabelText: '<?php echo _l('rooms'); ?>',
            resourceAreaWidth: '20%',
            resourceGroupField: 'property',
            resources: function (fetchInfo, successCallback, failureCallback) {
                // Load resources (rooms) for the calendar
                $.ajax({
                    url: admin_url + 'hotel_management_system/rooms/get_rooms_for_calendar',
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        successCallback(response);
                    },
                    error: function () {
                        failureCallback({message: '<?php echo _l('error_loading_resources'); ?>'});
                    }
                });
            },
            events: function (fetchInfo, successCallback, failureCallback) {
                // Load events (bookings) for the calendar
                var filters = {
                    start_date: moment(fetchInfo.start).format('YYYY-MM-DD'),
                    end_date: moment(fetchInfo.end).format('YYYY-MM-DD'),
                    property_id: $('#filter_property').val(),
                    room_id: $('#filter_room').val(),
                    status: $('#filter_status').val()
                };

                $.ajax({
                    url: admin_url + 'hotel_management_system/bookings/get_calendar_bookings',
                    method: 'GET',
                    data: filters,
                    dataType: 'json',
                    success: function (response) {
                        successCallback(response);
                        updateBookingStats(filters.start_date, filters.end_date);
                    },
                    error: function () {
                        failureCallback({message: '<?php echo _l('error_loading_events'); ?>'});
                    }
                });
            },
            eventRender: function (info) {
                // Add tooltip to events
                $(info.el).tooltip({
                    title: info.event.extendedProps.description || info.event.title,
                    placement: 'top',
                    container: 'body',
                    html: true
                });
            },
            eventClick: function (info) {
                // Show booking details modal when an event is clicked
                var bookingId = info.event.id;
                openBookingDetailsModal(bookingId);
            },
            select: function (info) {
                // When a date range is selected, open the new booking form with pre-filled dates
                var startDate = moment(info.start).format('YYYY-MM-DD');
                var endDate = moment(info.end).format('YYYY-MM-DD');

                if (info.resource) {
                    // If in resource view, also prefill the room
                    window.location.href = admin_url + 'hotel_management_system/bookings/booking' +
                        '?check_in=' + startDate +
                        '&check_out=' + endDate +
                        '&room_id=' + info.resource.id;
                } else {
                    window.location.href = admin_url + 'hotel_management_system/bookings/booking' +
                        '?check_in=' + startDate +
                        '&check_out=' + endDate;
                }
            },
            loading: function (isLoading) {
                // Show/hide loading indicator
                if (isLoading) {
                    $('#calendar').addClass('fc-loading');
                } else {
                    $('#calendar').removeClass('fc-loading');
                }
            }
        });

        // Render the calendar
        calendar.render();

        // Handle property filter change to load rooms
        $('#filter_property').on('change', function () {
            var propertyId = $(this).val();
            $('#filter_room').empty().append('<option value=""><?php echo _l('all_rooms'); ?></option>');

            if (propertyId) {
                $.ajax({
                    url: admin_url + 'hotel_management_system/rooms/get_property_rooms/' + propertyId,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        var selectOptions = '';

                        $.each(response, function (index, room) {
                            selectOptions += '<option value="' + room.id + '">' + room.name + '</option>';
                        });

                        $('#filter_room').append(selectOptions).selectpicker('refresh');
                    }
                });
            } else {
                $('#filter_room').selectpicker('refresh');
            }
        });

        // Apply filters button
        $('#apply_filters').on('click', function () {
            calendar.refetchEvents();
        });

        // Reset filters button
        $('#reset_filters').on('click', function () {
            $('#filter_property').val('').selectpicker('refresh');
            $('#filter_room').empty().append('<option value=""><?php echo _l('all_rooms'); ?></option>').selectpicker('refresh');
            $('#filter_status').val('').selectpicker('refresh');
            calendar.refetchEvents();
        });

        // Change calendar view
        $('input[name="calendar_view"]').on('change', function () {
            calendar.changeView($(this).val());
        });

        // Function to open booking details modal
        function openBookingDetailsModal(bookingId) {
            $('#booking_details_content').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only"><?php echo _l('loading'); ?>...</span></div></div>');
            $('#view_booking_btn').attr('href', admin_url + 'hotel_management_system/bookings/view/' + bookingId);
            $('#edit_booking_btn').attr('href', admin_url + 'hotel_management_system/bookings/booking/' + bookingId);
            $('#booking_details_modal').modal('show');

            $.ajax({
                url: admin_url + 'hotel_management_system/bookings/get_booking_details_ajax/' + bookingId,
                type: 'GET',
                dataType: 'html',
                success: function (response) {
                    $('#booking_details_content').html(response);
                },
                error: function () {
                    $('#booking_details_content').html('<div class="alert alert-danger"><?php echo _l('error_loading_booking_details'); ?></div>');
                }
            });
        }

        // Function to update booking statistics
        function updateBookingStats(startDate, endDate) {
            $.ajax({
                url: admin_url + 'hotel_management_system/bookings/get_statistics',
                type: 'POST',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    property_id: $('#filter_property').val(),
                    room_id: $('#filter_room').val()
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        var data = response.data;
                        var html = '<ul class="list-group">';
                        html += '<li class="list-group-item"><span class="badge">' + data.total_bookings + '</span><?php echo _l('total_bookings'); ?></li>';
                        html += '<li class="list-group-item"><span class="badge">' + data.confirmed_bookings + '</span><?php echo _l('confirmed_bookings'); ?></li>';
                        html += '<li class="list-group-item"><span class="badge">' + data.cancelled_bookings + '</span><?php echo _l('cancelled_bookings'); ?></li>';
                        html += '<li class="list-group-item"><span class="badge">' + data.room_nights + '</span><?php echo _l('room_nights'); ?></li>';
                        html += '<li class="list-group-item"><span class="badge">' + app_format_money(data.total_revenue) + '</span><?php echo _l('total_revenue'); ?></li>';

                        // Calculate occupancy rate
                        var occupancyRate = 0;
                        if (data.room_nights > 0 && data.total_available_room_nights > 0) {
                            occupancyRate = (data.room_nights / data.total_available_room_nights) * 100;
                        }
                        html += '<li class="list-group-item"><span class="badge">' + occupancyRate.toFixed(2) + '%</span><?php echo _l('occupancy_rate'); ?></li>';

                        html += '</ul>';

                        $('#booking-stats-panel').html(html);
                    }
                }
            });
        }

        // Initialize with current month's data
        var currentDate = new Date();
        var firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        var lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);

        updateBookingStats(
            moment(firstDay).format('YYYY-MM-DD'),
            moment(lastDay).format('YYYY-MM-DD')
        );
    });
</script>

<style>
    .fc-toolbar h2 {
        font-size: 18px;
        font-weight: 600;
    }

    .fc-toolbar button {
        height: auto !important;
        padding: 6px 12px !important;
    }

    .fc-day-header {
        padding: 8px 0 !important;
        font-weight: 600;
        background-color: #f5f5f5;
    }

    .fc-day-top {
        padding: 8px !important;
    }

    .fc-day-number {
        font-weight: 600;
    }

    .fc-today {
        background-color: rgba(252, 248, 227, 0.3) !important;
    }

    .fc-event {
        border-radius: 2px;
        border: none;
        padding: 3px 5px;
        font-size: 12px;
        cursor: pointer;
        margin: 1px 0;
    }

    .fc-time {
        font-weight: 600;
    }

    .fc-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .calendar-legend {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .calendar-legend li {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }

    .legend-box {
        display: inline-block;
        width: 18px;
        height: 18px;
        margin-right: 8px;
        border-radius: 2px;
    }

    .legend-label {
        font-size: 13px;
    }

    #calendar {
        background-color: #fff;
        padding: 15px;
        border-radius: 3px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        height: 650px;
    }

    .fc-event-container .fc-content {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .fc-resource-cell {
        font-weight: 600;
        padding: 8px !important;
    }

    .fc-resource-area .fc-cell-content {
        display: flex;
        align-items: center;
        height: 100%;
    }

    .fc-loading {
        opacity: 0.7;
        position: relative;
    }

    .fc-loading:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -20px;
        margin-left: -20px;
        width: 40px;
        height: 40px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    #booking-stats-panel .list-group-item {
        padding: 8px 15px;
    }

    #booking-stats-panel .badge {
        background-color: #03a9f4;
        font-size: 12px;
    }

    #booking_details_modal .modal-body {
        padding: 20px;
    }

    .panel {
        margin-bottom: 20px;
        border-radius: 3px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .panel-heading {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }

    .panel-title {
        font-size: 14px;
        font-weight: 600;
        margin: 0;
    }

    .panel-body {
        padding: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .btn-block {
        margin-bottom: 5px;
    }

    .radio label, .checkbox label {
        font-weight: 400;
        cursor: pointer;
    }
</style>