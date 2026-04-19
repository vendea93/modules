<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
			<?php echo form_open($this->uri->uri_string(), ['id' => 'landlord-form']); ?>
            <div class="col-md-8 col-md-offset-2">
                <div class="tw-mt-12 md:tw-mt-0 tw-w-full">
                    <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
						<?php echo(isset($landlord) ? (_l('edit_landlord') . ': ' . $landlord->name) : $title) ?>
                    </h4>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
								<?php echo render_input('name', 'name', isset($landlord) ? $landlord->name : '', 'text', ['required' => TRUE]); ?>
                            </div>
                            <div class="col-md-6">
								<?php echo render_input('company', 'client_company', isset($landlord) ? $landlord->company : ''); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
								<?php echo render_input('contact_person', 'hms_landlord_contact_person', isset($landlord) ? $landlord->contact_person : ''); ?>
                            </div>
                            <div class="col-md-6">
								<?php echo render_input('email', 'client_email', isset($landlord) ? $landlord->email : '', 'email', ['required' => TRUE]); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
								<?php echo render_input('phone', 'client_phonenumber', isset($landlord) ? $landlord->phone : ''); ?>
                            </div>
                            <div class="col-md-6">
								<?php echo render_input('tax_id', 'client_vat_number', isset($landlord) ? $landlord->tax_id : ''); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
								<?php echo render_input('address', 'client_address', isset($landlord) ? $landlord->address : ''); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
								<?php echo render_input('city', 'client_city', isset($landlord) ? $landlord->city : ''); ?>
                            </div>
                            <div class="col-md-6">
								<?php echo render_input('state', 'client_state', isset($landlord) ? $landlord->state : ''); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
								<?php echo render_input('postal_code', 'client_postal_code', isset($landlord) ? $landlord->postal_code : ''); ?>
                            </div>
                            <div class="col-md-6">
								<?php
								$countries = get_all_countries();
								$customer_default_country = get_option('customer_default_country');
								$selected = (isset($landlord) ? $landlord->country : $customer_default_country);
								?>

								<?php
								echo render_select('country', $countries, ['country_id', ['short_name']], 'clients_country', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
								?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
								<?php echo render_input('commission_rate', 'hms_landlord_commission_rate', isset($landlord) ? $landlord->commission_rate : '', 'number', ['min' => '0', 'max' => '100', 'step' => '0.01']); ?>
                                <p class="text-muted"><small><?php echo _l('commission_rate_help'); ?></small></p>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="active"><?php echo _l('hms_landlord_status'); ?></label>
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" name="active"
                                               id="active" <?php if (isset($landlord) && $landlord->active == 1 || ! isset($landlord))
										{
											echo 'checked';
										} ?>>
                                        <label for="active"><?php echo _l('active'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="payment_details"><?php echo _l('hms_landlord_payment_details'); ?></label>
                            <textarea name="payment_details" id="payment_details" class="form-control"
                                      rows="4"><?php echo isset($landlord) ? $landlord->payment_details : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="contact_notes"><?php echo _l('hms_landlord_contact_notes'); ?></label>
                            <textarea name="contact_notes" id="contact_notes" class="form-control"
                                      rows="4"><?php echo isset($landlord) ? $landlord->contact_notes : ''; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn-bottom-toolbar tw-w-full bottom-transaction tw-flex tw-items-center tw-justify-end">
                <a href="<?php echo admin_url('hotel_management_system/landlords'); ?>"
                   class="btn btn-default"><?php echo _l('cancel'); ?></a>
                <button class="btn btn-primary mleft5 proposal-form-submit transaction-submit" type="submit">
                    Save
                </button>
            </div>

			<?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    $(function () {
        appValidateForm($('#landlord-form'), {
            name: 'required',
            email: {
                required: true,
                email: true
            }
        });
    });
</script>