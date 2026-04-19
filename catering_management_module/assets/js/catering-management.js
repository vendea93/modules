// /**
//  * Catering Management Module JavaScript
//  */
//
// (function($) {
//     'use strict';
//
//     // Global Catering Object
//     window.CateringManagement = {
//
//         /**
//          * Initialize the module
//          */
//         init: function() {
//             this.initTableView();
//             this.initKanbanView();
//             this.initTimelineView();
//             this.initEventForm();
//             this.initEventView();
//         },
//
//         /**
//          * Initialize Table View
//          */
//         initTableView: function() {
//             if ($('.table-events').length === 0) return;
//
//             // Additional table customizations can go here
//             $(document).on('draw.dt', '.table-events', function() {
//                 $('[data-toggle="tooltip"]').tooltip();
//             });
//         },
//
//         /**
//          * Initialize Kanban View
//          */
//         initKanbanView: function() {
//             if ($('#kanban').length === 0) return;
//
//             // Sortable is initialized in manage.php
//             // Additional kanban customizations here
//         },
//
//         /**
//          * Initialize Timeline/Calendar View
//          */
//         initTimelineView: function() {
//             if ($('#calendar').length === 0) return;
//
//             // FullCalendar is initialized in manage.php
//             // Additional calendar customizations here
//         },
//
//         /**
//          * Initialize Event Form
//          */
//         initEventForm: function() {
//             if ($('#event-form').length === 0) return;
//
//             // Client selection change
//             $('#client_id').on('change', function() {
//                 var clientId = $(this).val();
//                 if (clientId) {
//                     CateringManagement.loadClientDefaults(clientId);
//                 }
//             });
//
//             // Guest count validation
//             $('#guest_count_final').on('blur', function() {
//                 var expected = parseInt($('#guest_count_expected').val()) || 0;
//                 var final = parseInt($(this).val()) || 0;
//
//                 if (final > expected * 1.5) {
//                     alert('Warning: Final guest count is significantly higher than expected.');
//                 }
//             });
//
//             // Date validation
//             $('#event_end').on('change', function() {
//                 var start = $('#event_start').val();
//                 var end = $(this).val();
//
//                 if (start && end && new Date(end) < new Date(start)) {
//                     alert('Event end date cannot be before start date.');
//                     $(this).val('');
//                 }
//             });
//         },
//
//         /**
//          * Initialize Event View Page
//          */
//         initEventView: function() {
//             if ($('.tab-content').length === 0) return;
//
//             // Enable tooltips
//             $('[data-toggle="tooltip"]').tooltip();
//
//             // Tab change handler
//             $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
//                 var target = $(e.target).attr('href');
//                 CateringManagement.onTabChange(target);
//             });
//         },
//
//         /**
//          * Handle tab changes
//          */
//         onTabChange: function(tabId) {
//             switch(tabId) {
//                 case '#tab_menu':
//                     this.loadMenuData();
//                     break;
//                 case '#tab_staffing':
//                     this.loadStaffingData();
//                     break;
//                 case '#tab_logistics':
//                     this.loadLogisticsData();
//                     break;
//                 case '#tab_finance':
//                     this.loadFinanceData();
//                     break;
//             }
//         },
//
//         /**
//          * Load client defaults when selected
//          */
//         loadClientDefaults: function(clientId) {
//             // Placeholder for loading client-specific defaults
//             // Could fetch dietary preferences, default venue, etc.
//         },
//
//         /**
//          * Load menu data for event
//          */
//         loadMenuData: function() {
//             // Placeholder - will be implemented in menu management
//             console.log('Loading menu data...');
//         },
//
//         /**
//          * Load staffing data for event
//          */
//         loadStaffingData: function() {
//             // Placeholder - will be implemented in staffing management
//             console.log('Loading staffing data...');
//         },
//
//         /**
//          * Load logistics data for event
//          */
//         loadLogisticsData: function() {
//             // Placeholder - will be implemented in logistics management
//             console.log('Loading logistics data...');
//         },
//
//         /**
//          * Load finance data for event
//          */
//         loadFinanceData: function() {
//             // Placeholder - will be implemented in finance management
//             console.log('Loading finance data...');
//         },
//
//         /**
//          * Show loading overlay
//          */
//         showLoading: function() {
//             if ($('.loading-overlay').length === 0) {
//                 $('body').append('<div class="loading-overlay"><div class="loading-spinner"></div></div>');
//             }
//         },
//
//         /**
//          * Hide loading overlay
//          */
//         hideLoading: function() {
//             $('.loading-overlay').remove();
//         },
//
//         /**
//          * Confirm delete action
//          */
//         confirmDelete: function(message) {
//             return confirm(message || 'Are you sure you want to delete this item?');
//         },
//
//         /**
//          * Format currency
//          */
//         formatCurrency: function(amount, currency) {
//             currency = currency || '$';
//             return currency + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
//         },
//
//         /**
//          * Format date
//          */
//         formatDate: function(dateString, format) {
//             var date = new Date(dateString);
//             // Simple formatting - extend as needed
//             return date.toLocaleDateString();
//         },
//
//         /**
//          * Copy to clipboard
//          */
//         copyToClipboard: function(text) {
//             var tempInput = $('<input>');
//             $('body').append(tempInput);
//             tempInput.val(text).select();
//             document.execCommand('copy');
//             tempInput.remove();
//
//             alert_float('success', 'Copied to clipboard');
//         }
//     };
//
//     /**
//      * Document Ready
//      */
//     $(document).ready(function() {
//         CateringManagement.init();
//
//         // Global delete confirmation
//         $(document).on('click', '._delete', function(e) {
//             if (!CateringManagement.confirmDelete()) {
//                 e.preventDefault();
//                 return false;
//             }
//         });
//
//         // Initialize select pickers
//         if ($.fn.selectpicker) {
//             $('.selectpicker').selectpicker('refresh');
//         }
//     });
//
//     /**
//      * Helper function for AJAX requests
//      */
//     $.fn.cateringAjax = function(url, data, callback) {
//         CateringManagement.showLoading();
//
//         return $.ajax({
//             url: url,
//             type: 'POST',
//             data: data,
//             dataType: 'json',
//             success: function(response) {
//                 CateringManagement.hideLoading();
//                 if (typeof callback === 'function') {
//                     callback(response);
//                 }
//             },
//             error: function(xhr, status, error) {
//                 CateringManagement.hideLoading();
//                 alert_float('danger', 'An error occurred: ' + error);
//             }
//         });
//     };
//
// })(jQuery);