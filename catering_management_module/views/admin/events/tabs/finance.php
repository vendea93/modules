<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-md-8">

        <!-- Estimate/Invoice Links -->
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-file-text-o tw-mr-2"></i>
					<?php echo _l('quotes_invoices'); ?>
                </h4>

                <div class="row">
                    <div class="col-md-6">
                        <div class="well">
                            <h5 class="tw-font-semibold"><?php echo _l('estimate'); ?></h5>
							<?php if ($event->estimate_id && $estimate): ?>
                                <p>
                                    <a href="<?php echo admin_url('estimates/list_estimates/'.$event->estimate_id); ?>" class="tw-font-medium">
										<?php echo format_estimate_number($estimate->id); ?>
                                    </a>
                                </p>
                                <p class="text-muted">Status: <?php echo format_estimate_status($estimate->status, '', TRUE); ?></p>
                                <p class="text-muted">Amount: <strong><?php echo app_format_money($estimate->total, $estimate->currency_name); ?></strong></p>
                                <a href="<?php echo admin_url('estimates/list_estimates/'.$event->estimate_id); ?>" class="btn btn-default btn-icon">
                                    <i class="fa fa-eye"></i> <?php echo _l('view_estimate'); ?>
                                </a>
							<?php else: ?>
                                <p class="text-muted"><?php echo _l('no_estimate_created'); ?></p>
								<?php if (staff_can('create', 'estimates')): ?>
                                    <a href="<?php echo admin_url('catering_management_module/events/generate_estimate/'.$event->eventid); ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> <?php echo _l('generate_estimate'); ?>
                                    </a>
								<?php endif; ?>
							<?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                            <h5 class="tw-font-semibold"><?php echo _l('invoice'); ?></h5>
							<?php if ($event->invoice_id && $invoice): ?>
                                <p>
                                    <a href="<?php echo admin_url('invoices/list_invoices/'.$event->invoice_id); ?>" class="tw-font-medium">
										<?php echo format_invoice_number($invoice->id); ?>
                                    </a>
                                </p>
                                <p class="text-muted">Status: <?php echo format_invoice_status($invoice->status, '', TRUE); ?></p>
                                <p class="text-muted">Amount: <strong><?php echo app_format_money($invoice->total, $invoice->currency_name); ?></strong></p>
								<?php if (isset($total_paid)): ?>
                                    <p class="text-muted">Paid: <strong><?php echo app_format_money($total_paid, $invoice->currency_name); ?></strong> (<?php echo round($payment_percentage); ?>%)</p>
								<?php endif; ?>
                                <a href="<?php echo admin_url('invoices/list_invoices/'.$event->invoice_id); ?>" class="btn btn-default btn-icon">
                                    <i class="fa fa-eye"></i> <?php echo _l('view_invoice'); ?>
                                </a>
							<?php else: ?>
                                <p class="text-muted"><?php echo _l('no_invoice_created'); ?></p>
								<?php if ($event->estimate_id && staff_can('create', 'invoices')): ?>
                                    <button class="btn btn-primary btn-sm" id="create-invoice">
                                        <i class="fa fa-plus"></i> <?php echo _l('convert_to_invoice'); ?>
                                    </button>
								<?php endif; ?>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Breakdown (Permission Gated) -->
		<?php if (can_view_event_costs()): ?>
            <div class="panel_s">
                <div class="panel-body">
                    <h4 class="tw-font-semibold tw-mb-4">
                        <i class="fa fa-calculator tw-mr-2"></i>
						<?php echo _l('cost_breakdown'); ?>
                    </h4>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th><?php echo _l('category'); ?></th>
                                <th><?php echo _l('description'); ?></th>
                                <th class="text-right"><?php echo _l('cost'); ?></th>
                                <th class="text-right"><?php echo _l('revenue'); ?></th>
                                <th class="text-right"><?php echo _l('margin'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php if ($financials): ?>
                                <tr>
                                    <td><span class="label label-info">Food</span></td>
                                    <td>Menu items for <?php echo $event->guest_count_expected ?: 0; ?> guests</td>
                                    <td class="text-right"><?php echo app_format_money($financials['menu']['total_cost'], get_base_currency()); ?></td>
                                    <td class="text-right"><?php echo app_format_money($financials['menu']['total_revenue'], get_base_currency()); ?></td>
                                    <td class="text-right">
										<?php
										$menu_margin = $financials['menu']['total_revenue'] > 0 ? (($financials['menu']['total_revenue'] - $financials['menu']['total_cost']) / $financials['menu']['total_revenue']) * 100 : 0;
										echo catering_margin_badge(round($menu_margin, 2));
										?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="label label-warning">Labor</span></td>
                                    <td><?php echo $financials['labor']['staff_count']; ?> staff members, <?php echo round($financials['labor']['total_hours'], 1); ?> total hours</td>
                                    <td class="text-right"><?php echo app_format_money($financials['labor']['total_cost'], get_base_currency()); ?></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                </tr>
                                <tr>
                                    <td><span class="label label-default">Other</span></td>
                                    <td>Miscellaneous expenses</td>
                                    <td class="text-right"><?php echo app_format_money($financials['expenses']['total_cost'], get_base_currency()); ?></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                </tr>
							<?php endif; ?>
                            </tbody>
                            <tfoot>
							<?php if ($financials): ?>
                                <tr class="active tw-font-semibold">
                                    <td colspan="2"><strong><?php echo _l('totals'); ?></strong></td>
                                    <td class="text-right"><strong><?php echo app_format_money($financials['summary']['total_cost'], get_base_currency()); ?></strong></td>
                                    <td class="text-right"><strong><?php echo app_format_money($financials['summary']['total_revenue'], get_base_currency()); ?></strong></td>
                                    <td class="text-right">
										<?php echo catering_margin_badge(round($financials['summary']['profit_margin'], 2)); ?>
                                    </td>
                                </tr>
                                <tr class="success">
                                    <td colspan="4" class="text-right"><strong><?php echo _l('net_profit'); ?>:</strong></td>
                                    <td class="text-right"><strong><?php echo app_format_money($financials['summary']['net_profit'], get_base_currency()); ?></strong></td>
                                </tr>
							<?php endif; ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
		<?php endif; ?>

        <!-- Payment History -->
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-money tw-mr-2"></i>
					<?php echo _l('payment_history'); ?>
                </h4>

				<?php if ($event->invoice_id && ! empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th><?php echo _l('date'); ?></th>
                                <th><?php echo _l('payment_mode'); ?></th>
                                <th><?php echo _l('transaction_id'); ?></th>
                                <th><?php echo _l('amount'); ?></th>
                                <th><?php echo _l('note'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?php echo _d($payment['date']); ?></td>
                                    <td><span class="label label-info"><?php echo $payment['name']; ?></span></td>
                                    <td><?php echo $payment['transactionid'] ? '<code>'.htmlspecialchars($payment['transactionid']).'</code>' : '-'; ?></td>
                                    <td><strong><?php echo app_format_money($payment['amount'], $invoice->currency_name); ?></strong></td>
                                    <td><?php echo htmlspecialchars($payment['note']); ?></td>
                                </tr>
							<?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr class="active">
                                <td colspan="3" class="text-right"><strong><?php echo _l('total_paid'); ?>:</strong></td>
                                <td colspan="2"><strong><?php echo app_format_money($total_paid, $invoice->currency_name); ?></strong></td>
                            </tr>
                            <tr class="warning">
                                <td colspan="3" class="text-right"><strong><?php echo _l('balance_due'); ?>:</strong></td>
                                <td colspan="2"><strong><?php echo app_format_money($balance_due, $invoice->currency_name); ?></strong></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
				<?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <?php echo _l('no_payments_recorded'); ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-md-4">

        <!-- Financial Summary -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-bar-chart tw-mr-2"></i>
					<?php echo _l('financial_summary'); ?>
                </h5>
                <div class="tw-space-y-3">
					<?php if ($financials): ?>
                        <div class="tw-flex tw-justify-between">
                            <span><?php echo _l('total_revenue'); ?>:</span>
                            <strong class="text-success"><?php echo app_format_money($financials['summary']['total_revenue'], get_base_currency()); ?></strong>
                        </div>
						<?php if (can_view_event_costs()): ?>
                            <div class="tw-flex tw-justify-between">
                                <span><?php echo _l('total_cost'); ?>:</span>
                                <strong class="text-danger"><?php echo app_format_money($financials['summary']['total_cost'], get_base_currency()); ?></strong>
                            </div>
                            <hr>
                            <div class="tw-flex tw-justify-between">
                                <span><strong><?php echo _l('net_profit'); ?>:</strong></span>
                                <strong class="text-success"><?php echo app_format_money($financials['summary']['net_profit'], get_base_currency()); ?></strong>
                            </div>
                            <div class="tw-flex tw-justify-between">
                                <span><?php echo _l('profit_margin'); ?>:</span>
								<?php echo catering_margin_badge(round($financials['summary']['profit_margin'], 2)); ?>
                            </div>
						<?php endif; ?>
					<?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No financial data available yet.
                        </div>
					<?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-credit-card tw-mr-2"></i>
					<?php echo _l('payment_status'); ?>
                </h5>

				<?php if ($event->invoice_id && $invoice): ?>
					<?php $payment_percentage = round($payment_percentage ?? 0); ?>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar progress-bar-success" data-percent="<?php echo $payment_percentage ?>" style="width: <?php echo round($payment_percentage); ?>%">
							<?php echo $payment_percentage ?>%
                        </div>
                    </div>

                    <div class="tw-space-y-2 mt-3">
                        <div class="tw-flex tw-justify-between">
                            <span class="text-muted"><?php echo _l('invoiced'); ?>:</span>
                            <strong><?php echo app_format_money($invoice->total, $invoice->currency_name); ?></strong>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <span class="text-success"><?php echo _l('paid'); ?>:</span>
                            <strong class="text-success"><?php echo app_format_money($total_paid, $invoice->currency_name); ?></strong>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <span class="text-warning"><?php echo _l('outstanding'); ?>:</span>
                            <strong class="text-warning"><?php echo app_format_money($balance_due, $invoice->currency_name); ?></strong>
                        </div>
                    </div>
                    <a href="<?php echo admin_url('invoices#'.$invoice->id); ?>" class="btn btn-success btn-block mt-3">
                        <i class="fa fa-plus"></i> <?php echo _l('record_payment'); ?>
                    </a>
				<?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No invoice created yet.
                    </div>
				<?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-bolt tw-mr-2"></i>
					<?php echo _l('quick_actions'); ?>
                </h5>
                <button class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-envelope"></i> <?php echo _l('send_invoice_reminder'); ?>
                </button>
                <button class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-download"></i> <?php echo _l('export_financial_report'); ?>
                </button>
                <button class="btn btn-default btn-block btn-sm">
                    <i class="fa fa-print"></i> <?php echo _l('print_receipt'); ?>
                </button>
            </div>
        </div>

		<?php if (can_view_event_costs()): ?>
            <!-- Profitability Analysis -->
            <div class="panel_s">
                <div class="panel-body">
                    <h5 class="tw-font-semibold tw-mb-3">
                        <i class="fa fa-pie-chart tw-mr-2"></i>
						<?php echo _l('cost_distribution'); ?>
                    </h5>
                    <canvas id="cost-chart" height="200"></canvas>
                </div>
            </div>
		<?php endif; ?>

    </div>

</div>
<?php
$chart_data = [$financials['menu']['total_cost'], $financials['labor']['total_cost']];
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
		<?php if (can_view_event_costs() && $financials): ?>
        // Cost distribution chart
        $(function () {
            var ctx = document.getElementById('cost-chart');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Food Cost', 'Labor Cost'],
                    datasets: [{
                        data: <?php echo json_encode($chart_data) ?>,
                        backgroundColor: [
                            '#5bc0de',
                            '#f0ad4e',
                            '#999'
                        ]
                    }],
                    options: {
                        responsive: true,
                    }
                }
            });
        });
		<?php endif; ?>
        $(function () {
            // Create invoice
            $('#create-invoice').on('click', function () {
                if (confirm('<?php echo _l('confirm_convert_estimate_to_invoice'); ?>')) {
                    window.location.href = admin_url + 'catering_management_module/events/convert_to_invoice/<?php echo $event->eventid; ?>';
                }
            });

            // Add expense
            $('#add-expense').on('click', function () {
                window.location.href = admin_url + 'expenses/expense?event_id=<?php echo $event->eventid; ?>';
            });
        });
    });
</script>