<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
	<div id="wrapper">
		<div class="content">
			<div class="row">
				<div class="col-md-12">
					<div class="panel_s">
						<div class="panel-body">
							<div class="_buttons">
								<a href="<?php echo admin_url('hotel_management_system/services/assignments'); ?>" class="btn btn-default pull-left display-block">
									<?php echo _l('back_to_assignments'); ?>
								</a>
							</div>
							<div class="clearfix"></div>
							<hr class="hr-panel-heading" />

							<div class="row">
								<div class="col-md-12">
									<h4><?php echo $title; ?></h4>
									<p class="text-muted"><?php echo _l('staff_schedule_description'); ?></p>
								</div>
							</div>

							<div class="row">
								<div class="col-md-4">
									<div class="panel panel-info">
										<div class="panel-heading">
											<h3 class="panel-title"><?php echo _l('staff_details'); ?></h3>
										</div>
										<div class="panel-body">
											<p><strong><?php echo _l('staff_name'); ?>:</strong> <?php echo $staff->firstname . ' ' . $staff->lastname; ?></p>
											<p><strong><?php echo _l('email'); ?>:</strong> <?php echo $staff->email; ?></p>
											<p><strong><?php echo _l('phone'); ?>:</strong> <?php echo $staff->phonenumber; ?></p>
										</div>
									</div>
								</div>
							</div>

							<hr class="hr-panel-heading" />

							<!-- Weekly schedule -->
							<div class="row">
								<div class="col-md-12">
									<h4><?php echo _l('weekly_schedule'); ?></h4>
								</div>
							</div>

							<div class="row">
								<?php foreach ($days_of_week as $day_number => $day_name) { ?>
									<div class="col-md-6">
										<div class="panel panel-default">
											<div class="panel-heading">
												<h3 class="panel-title"><?php echo $day_name; ?></h3>
											</div>
											<div class="panel-body">
												<?php if (isset($schedule[$day_number]) && count($schedule[$day_number]) > 0) { ?>
													<div class="table-responsive">
														<table class="table table-striped">
															<thead>
															<tr>
																<th><?php echo _l('time'); ?></th>
																<th><?php echo _l('property'); ?></th>
																<th><?php echo _l('room'); ?></th>
																<th><?php echo _l('service'); ?></th>
															</tr>
															</thead>
															<tbody>
															<?php foreach ($schedule[$day_number] as $assignment) { ?>
																<tr>
																	<td><?php echo date('H:i', strtotime($assignment['start_time'])) . ' - ' . date('H:i', strtotime($assignment['end_time'])); ?></td>
																	<td><?php echo $assignment['property_name']; ?></td>
																	<td><?php echo $assignment['room_name']; ?></td>
																	<td><?php echo $assignment['service_name']; ?></td>
																</tr>
															<?php } ?>
															</tbody>
														</table>
													</div>
												<?php } else { ?>
													<p class="text-muted"><?php echo _l('no_assignments_for_this_day'); ?></p>
												<?php } ?>
											</div>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php init_tail(); ?>