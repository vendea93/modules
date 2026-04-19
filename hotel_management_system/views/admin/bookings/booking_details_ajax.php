<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if ($booking) { ?>
	<div class="booking-quick-details">
		<div class="row">
			<div class="col-md-12">
				<h4 class="bold"><?php echo $booking->booking_reference; ?></h4>
				<p>
                <span class="label label-<?php
				switch ($booking->booking_status) {
					case 'confirmed':
						echo 'info';
						break;
					case 'checked_in':
						echo 'success';
						break;
					case 'checked_out':
						echo 'default';
						break;
					case 'cancelled':
						echo 'danger';
						break;
					case 'no_show':
						echo 'warning';
						break;
					default:
						echo 'default';
				}
				?>"><?php echo _l($booking->booking_status); ?></span>

					<span class="mleft5 label label-<?php
					switch ($booking->payment_status) {
						case 'paid':
							echo 'success';
							break;
						case 'partial':
							echo 'info';
							break;
						case 'overdue':
							echo 'danger';
							break;
						case 'refunded':
							echo 'warning';
							break;
						default:
							echo 'default';
					}
					?>"><?php echo _l($booking->payment_status); ?></span>
				</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<p><strong><?php echo _l('guest'); ?>:</strong> <?php echo $booking->guest_name; ?></p>
				<p><strong><?php echo _l('email'); ?>:</strong> <?php echo $booking->guest_email; ?></p>
				<?php if ($booking->guest_phone) { ?>
					<p><strong><?php echo _l('phone'); ?>:</strong> <?php echo $booking->guest_phone; ?></p>
				<?php } ?>
			</div>
			<div class="col-md-6">
				<p><strong><?php echo _l('room'); ?>:</strong> <?php echo $booking->room_name; ?></p>
				<p><strong><?php echo _l('property'); ?>:</strong> <?php echo $booking->property_name; ?></p>
				<p><strong><?php echo _l('dates'); ?>:</strong> <?php echo _d($booking->check_in_date); ?> - <?php echo _d($booking->check_out_date); ?> (<?php echo $booking->total_nights; ?> <?php echo _l('nights'); ?>)</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<p><strong><?php echo _l('total_amount'); ?>:</strong> <?php echo app_format_money($booking->total_amount, get_base_currency()); ?></p>
			</div>
		</div>

		<?php if (!empty($booking->special_requests)) { ?>
			<div class="row">
				<div class="col-md-12">
					<p><strong><?php echo _l('special_requests'); ?>:</strong></p>
					<div class="well well-sm mtop5">
						<?php echo $booking->special_requests; ?>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
<?php } else { ?>
	<div class="alert alert-danger">
		<?php echo _l('booking_not_found'); ?>
	</div>
<?php } ?>