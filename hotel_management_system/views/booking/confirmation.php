<?php
/** @var array $rooms */
/** @var object $booking */
?>
<div class="row">
    <div class="col-md-12">
        <h1 class="text-center mbot30"><?php echo _l('booking_confirmation'); ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel_s">
            <div class="panel-body">
                <div class="confirmation-message text-center">
                    <i class="fa fa-check-circle confirmation-icon"></i>
                    <h2><?php echo _l('thank_you_for_your_booking'); ?></h2>
                </div>

                <div class="booking-reference text-center">
                    <h4><?php echo _l('booking_reference'); ?></h4>
                    <div class="reference-number"><?php echo $booking->booking_reference; ?></div>
                </div>

                <hr>

                <div class="booking-details">
                    <h3><?php echo _l('booking_details'); ?></h3>
                    <div class="row">
                        <div class="col-md-6">
							<?php foreach ($rooms as $index => $room): ?>
                                <div>
									<?php if (count($rooms) > 1) : ?>
                                        <h3><?php echo _l('room') . ' ' . ($index + 1) ?></h3>
									<?php endif ?>
                                    <p><strong><?php echo _l('hotel'); ?>:</strong> <?php echo $room->property->name; ?></p>
                                    <p><strong><?php echo _l('room'); ?>:</strong> <?php echo $room->name; ?></p>
                                    <p><strong><?php echo _l('check_in_date'); ?>:</strong> <?php echo date('l, j F Y', strtotime($booking->check_in_date)) ?></p>
                                    <p><strong><?php echo _l('check_out_date'); ?>:</strong> <?php echo date('l, j F Y', strtotime($booking->check_out_date)) ?></p>
                                    <p><strong><?php echo _l('nights'); ?>:</strong> <?php echo $booking->total_nights; ?></p>
                                </div>
							<?php endforeach; ?>
                        </div>
                        <div class="col-md-6">
                            <p><strong><?php echo _l('guest_name'); ?>:</strong> <?php echo $booking->guest_name; ?></p>
                            <p><strong><?php echo _l('email'); ?>:</strong> <?php echo $booking->guest_email; ?></p>
                            <p><strong><?php echo _l('phone'); ?>:</strong> <?php echo $booking->guest_phone; ?></p>
                            <p><strong><?php echo _l('taxes'); ?>:</strong> <?php echo app_format_money($booking->taxes, get_base_currency()); ?></p>
                            <p><strong><?php echo _l('total_amount'); ?>:</strong> <?php echo app_format_money($booking->total_amount, get_base_currency()); ?></p>
                            <p><strong><?php echo _l('payment_status'); ?>:</strong> <span class="label label-success"><?php echo _l('paid'); ?></span></p>
                        </div>
                    </div>
                </div>

                <hr>
                <hr>

                <div class="text-center mbot30">
                    <a href="<?php echo site_url('hotel_management_system/booking'); ?>" class="btn btn-info"><?php echo _l('book_another_room'); ?></a>
                    <a href="javascript:window.print();" class="btn btn-default"><i class="fa fa-print"></i> <?php echo _l('print'); ?></a>
                </div>

                <div class="text-center">
                    <p class="text-muted"><?php echo _l('confirmation_email_sent'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Ensure jQuery is properly loaded
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }

        // Auto-scroll to top of page on load
        $('html, body').animate({
            scrollTop: 0
        }, 500);

        // Add some nice animations to the confirmation elements
        $('.confirmation-icon').hide().fadeIn(1000);
        $('.reference-number').hide().delay(500).fadeIn(800);

        // Print functionality
        $('a[href="javascript:window.print();"]').on('click', function (e) {
            e.preventDefault();
            window.print();
        });

        // Add click-to-copy functionality for booking reference
        $('.reference-number').on('click', function () {
            var referenceText = $(this).text();

            // Try to copy to clipboard
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(referenceText).then(function () {
                    // Show temporary success message
                    var originalHtml = $('.reference-number').html();
                    $('.reference-number').html('<i class="fa fa-check"></i> Copied!').addClass('text-success');

                    setTimeout(function () {
                        $('.reference-number').html(originalHtml).removeClass('text-success');
                    }, 2000);
                }).catch(function () {
                    // Fallback for older browsers
                    copyToClipboardFallback(referenceText);
                });
            } else {
                // Fallback for older browsers
                copyToClipboardFallback(referenceText);
            }
        });

        function copyToClipboardFallback(text) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                var originalHtml = $('.reference-number').html();
                $('.reference-number').html('<i class="fa fa-check"></i> Copied!').addClass('text-success');

                setTimeout(function () {
                    $('.reference-number').html(originalHtml).removeClass('text-success');
                }, 2000);
            } catch (err) {
                console.error('Could not copy text: ', err);
            }

            document.body.removeChild(textArea);
        }

        // Add hover effect to reference number to indicate it's clickable
        $('.reference-number').css('cursor', 'pointer').attr('title', 'Click to copy booking reference');
    });
</script>

<style>
    .confirmation-message {
        margin: 30px 0;
    }

    .confirmation-icon {
        font-size: 80px;
        color: #4CAF50;
        margin-bottom: 20px;
    }

    .booking-reference {
        margin: 30px 0;
    }

    .reference-number {
        font-size: 24px;
        font-weight: bold;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 4px;
        letter-spacing: 2px;
        margin: 10px 0;
        border: 2px dashed #ddd;
        transition: all 0.3s ease;
    }

    .reference-number:hover {
        background-color: #e3f2fd;
        border-color: #03a9f4;
        transform: scale(1.02);
    }

    .hotel-details, .booking-details {
        margin: 30px 0;
    }

    .check-in-info {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 4px;
    }

    /* Print styles */
    @media print {
        body {
            background-color: #fff;
        }

        .panel_s {
            box-shadow: none;
            border: 1px solid #ddd;
        }

        .btn {
            display: none;
        }

        .reference-number {
            border: 1px solid #ddd;
        }

        .confirmation-message p {
            margin-bottom: 20px;
        }

        hr {
            margin: 20px 0;
            border-top: 1px solid #ddd;
        }

        /* Ensure the confirmation page prints on one page if possible */
        .booking-details, .hotel-details {
            page-break-inside: avoid;
        }
    }
</style>