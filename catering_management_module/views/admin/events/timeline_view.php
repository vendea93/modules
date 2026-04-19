<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div id="events_calendar"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

    });
</script>
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }

    .fc-event {
        border-radius: 4px;
        border: none;
        padding: 2px 5px;
    }

    .event-status-enquiry {
        background-color: #5bc0de !important;
    }

    .event-status-quoted {
        background-color: #337ab7 !important;
    }

    .event-status-confirmed {
        background-color: #5cb85c !important;
    }

    .event-status-in_progress {
        background-color: #f0ad4e !important;
    }

    .event-status-completed {
        background-color: #777 !important;
    }

    .event-status-cancelled {
        background-color: #d9534f !important;
    }

    .event-status-lost {
        background-color: #999 !important;
    }

    .fc-day-grid-event {
        margin: 1px 2px !important;
    }
</style>