<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
    <div id="wrapper">
        <div class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="no-margin"><?php echo _l('landlord_details'); ?>
                                        : <?php echo $landlord_data['landlord']->name; ?></h4>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="<?php echo admin_url('hotel_management_system/landlords/landlord/' . $landlord_data['landlord']->id); ?>"
                                       class="btn btn-default btn-sm">
                                        <i class="fa fa-pencil"></i> <?php echo _l('edit'); ?>
                                    </a>
                                    <a href="<?php echo admin_url('hotel_management_system/landlords'); ?>"
                                       class="btn btn-default btn-sm">
										<?php echo _l('back_to_landlords'); ?>
                                    </a>
                                </div>
                            </div>
                            <hr class="hr-panel-heading"/>

                            <div class="row mtop20">
                                <div class="col-md-6">
                                    <table class="table table-striped table-bordered">
                                        <tbody>
                                        <tr>
                                            <td class="bold"><?php echo _l('id'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->id; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('name'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->name; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('client_company'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->company; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('hms_landlord_contact_person'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->contact_person; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('client_email'); ?></td>
                                            <td>
                                                <a href="mailto:<?php echo $landlord_data['landlord']->email; ?>"><?php echo $landlord_data['landlord']->email; ?></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('client_phonenumber'); ?></td>
                                            <td>
                                                <a href="tel:<?php echo $landlord_data['landlord']->phone; ?>"><?php echo $landlord_data['landlord']->phone; ?></a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <table class="table table-striped table-bordered">
                                        <tbody>
                                        <tr>
                                            <td class="bold"><?php echo _l('client_address'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->address; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('client_city'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->city; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('client_state'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->state; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('client_postal_code'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->postal_code; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('clients_country'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->country; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('hms_landlord_commission_rate'); ?></td>
                                            <td><?php echo $landlord_data['landlord']->commission_rate; ?>%</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

							<?php if ( ! empty($landlord_data['landlord']->tax_id) || ! empty($landlord_data['landlord']->payment_details)) { ?>
                                <div class="row mtop20">
                                    <div class="col-md-12">
                                        <h4 class="bold"><?php echo _l('financial_information'); ?></h4>
                                        <hr/>
                                    </div>

									<?php if ( ! empty($landlord_data['landlord']->tax_id)) { ?>
                                        <div class="col-md-6">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><?php echo _l('client_vat_number'); ?></h3>
                                                </div>
                                                <div class="panel-body">
													<?php echo $landlord_data['landlord']->tax_id; ?>
                                                </div>
                                            </div>
                                        </div>
									<?php } ?>

									<?php if ( ! empty($landlord_data['landlord']->payment_details)) { ?>
                                        <div class="col-md-6">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><?php echo _l('hms_landlord_payment_details'); ?></h3>
                                                </div>
                                                <div class="panel-body">
													<?php echo nl2br($landlord_data['landlord']->payment_details); ?>
                                                </div>
                                            </div>
                                        </div>
									<?php } ?>
                                </div>
							<?php } ?>

							<?php if ( ! empty($landlord_data['landlord']->contact_notes)) { ?>
                                <div class="row mtop20">
                                    <div class="col-md-12">
                                        <h4 class="bold"><?php echo _l('hms_landlord_contact_notes'); ?></h4>
                                        <hr/>
                                        <div class="panel panel-default">
                                            <div class="panel-body">
												<?php echo nl2br($landlord_data['landlord']->contact_notes); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
							<?php } ?>

                            <div class="row mtop20">
                                <div class="col-md-12">
                                    <h4 class="bold">
										<?php echo _l('properties'); ?>
                                        <a href="<?php echo admin_url('hotel_management_system/properties/property?landlord_id=' . $landlord_data['landlord']->id); ?>"
                                           class="btn btn-info btn-xs pull-right">
                                            <i class="fa fa-plus"></i> <?php echo _l('new_property'); ?>
                                        </a>
                                    </h4>
                                    <hr/>

									<?php if (count($landlord_data['properties']) > 0) { ?>
                                        <div class="table-responsive">
                                            <table class="table dt-table">
                                                <thead>
                                                <tr>
                                                    <th><?php echo _l('id'); ?></th>
                                                    <th><?php echo _l('name'); ?></th>
                                                    <th><?php echo _l('address'); ?></th>
                                                    <th><?php echo _l('city'); ?></th>
                                                    <th><?php echo _l('property_type'); ?></th>
                                                    <th><?php echo _l('status'); ?></th>
                                                    <th><?php echo _l('options'); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
												<?php foreach ($landlord_data['properties'] as $property) { ?>
                                                    <tr>
                                                        <td><?php echo $property['id']; ?></td>
                                                        <td>
                                                            <a href="<?php echo admin_url('hotel_management_system/properties/view/' . $property['id']); ?>">
																<?php echo $property['name']; ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo $property['address']; ?></td>
                                                        <td><?php echo $property['city']; ?></td>
                                                        <td><?php echo $property['property_type']; ?></td>
                                                        <td>
															<?php if ($property['status'] == 'active') { ?>
                                                                <span class="label label-success"><?php echo _l('active'); ?></span>
															<?php } else { ?>
                                                                <span class="label label-danger"><?php echo _l('inactive'); ?></span>
															<?php } ?>
                                                        </td>
                                                        <td class="tw-flex tw-flex-nowrap tw-gap-2">
                                                            <a href="<?php echo admin_url('hotel_management_system/properties/property/' . $property['id']); ?>"
                                                               class="btn btn-default btn-icon">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                            <a href="<?php echo admin_url('hotel_management_system/properties/delete/' . $property['id']); ?>"
                                                               class="btn btn-danger btn-icon _delete">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
												<?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
									<?php } else { ?>
                                        <p class="no-margin"><?php echo _l('no_properties_found'); ?></p>
									<?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php init_tail(); ?>