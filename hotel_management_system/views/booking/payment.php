<?php
/** @var array $rooms */
/** @var object $booking */
?>
<div class="row">
    <div class="col-md-12">
        <h1 class="text-center mbot30"><?php echo _l('payment'); ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel_s">
            <div class="panel-body">
                <div class="booking-details">
                    <h3 class="tw-text-center tw-mb-10"><?php echo _l('booking_details'); ?></h3>
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
                            <p><strong><?php echo _l('guest_name'); ?>:</strong> <?php echo $booking->guest_name ?></p>
                            <p><strong><?php echo _l('email'); ?>:</strong> <?php echo $booking->guest_email; ?></p>
                            <p><strong><?php echo _l('phone'); ?>:</strong> <?php echo $booking->guest_phone; ?></p>
                            <p><strong><?php echo _l('room_price'); ?>:</strong> <?php echo app_format_money($booking->room_price, get_base_currency()); ?></p>
                            <p><strong><?php echo _l('cleaning_fee'); ?>:</strong> <?php echo app_format_money($booking->cleaning_fee, get_base_currency()); ?></p>
                            <p><strong><?php echo _l('taxes'); ?>:</strong> <?php echo app_format_money($booking->taxes, get_base_currency()); ?></p>
                            <p><strong><?php echo _l('total_amount'); ?>:</strong> <?php echo app_format_money($booking->total_amount, get_base_currency()); ?></p>
                        </div>
                    </div>
                </div>

                <hr>

				<?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger">
						<?php echo $this->session->flashdata('error'); ?>
                    </div>
				<?php } ?>

                <div class="payment-form">
                    <h3><?php echo _l('payment_details'); ?></h3>
                    <p class="text-muted"><?php echo _l('cash'); ?></p>
					<?php echo form_open('hotel_management_system/booking/process_payment', ['id' => 'payment-form']); ?>
                    <input type="hidden" name="booking_id" value="<?php echo $booking->id; ?>">
                    <div class="form-group tw-mt-10">
                        <button type="submit" class="btn btn-success btn-lg btn-block"><?php echo _l('complete_payment'); ?></button>
                    </div>
					<?php echo form_close(); ?>
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

        // Format card number - only allow numbers
        $('#card_number').on('keypress change blur', function () {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
        });

        // Format CVV - only allow numbers
        $('#cvv').on('keypress change blur', function () {
            $(this).val($(this).val().replace(/[^0-9]/g, ''));
        });

        // Add card number formatting (spaces every 4 digits for display)
        $('#card_number').on('input', function () {
            var value = $(this).val().replace(/\s/g, '').replace(/[^0-9]/gi, '');
            var formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (value !== $(this).val().replace(/\s/g, '')) {
                $(this).val(formattedValue);
            }
        });

        // Form validation
        $('#payment-form').on('submit', function (e) {
            var cardNumber = $('#card_number').val().replace(/\s/g, ''); // Remove spaces
            var cvv = $('#cvv').val();
            var expiryMonth = $('select[name="expiry_month"]').val();
            var expiryYear = $('select[name="expiry_year"]').val();

            // Check card number format
            if (cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                e.preventDefault();
                alert('<?php echo _l("please_enter_valid_card_number"); ?>');
                $('#card_number').focus();
                return false;
            }

            // Check CVV format
            if (cvv.length < 3 || !/^\d+$/.test(cvv)) {
                e.preventDefault();
                alert('<?php echo _l("please_enter_valid_cvv"); ?>');
                $('#cvv').focus();
                return false;
            }

            // Check expiry date
            if (!expiryMonth || !expiryYear) {
                e.preventDefault();
                alert('<?php echo _l("please_select_expiry_date"); ?>');
                $('select[name="expiry_month"]').focus();
                return false;
            }

            var today = new Date();
            var currentMonth = today.getMonth() + 1;
            var currentYear = today.getFullYear();

            if (parseInt(expiryYear) == currentYear && parseInt(expiryMonth) < currentMonth) {
                e.preventDefault();
                alert('<?php echo _l("card_expired"); ?>');
                $('select[name="expiry_month"]').focus();
                return false;
            }

            // Show loading state
            $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l("processing"); ?>...');

            return true;
        });
    });
</script>

<style>
    .booking-details {
        margin-bottom: 30px;
    }

    .payment-methods {
        margin: 20px 0;
    }

    .payment-methods img {
        max-width: 300px;
    }

    .payment-summary {
        margin: 30px 0;
    }

    .alert {
        border-radius: 0;
    }

    .help-block {
        font-size: 12px;
        color: #777;
    }

    /* Additional payment form styles */
    #card_number, #cvv {
        letter-spacing: 1px;
        font-family: monospace;
    }

    .form-control:focus {
        border-color: #03a9f4;
        box-shadow: 0 0 8px rgba(3, 169, 244, 0.2);
    }

    .btn-success {
        background-color: #4CAF50;
        border-color: #43A047;
    }

    .btn-success:hover, .btn-success:focus {
        background-color: #388E3C;
        border-color: #2E7D32;
    }
</style>