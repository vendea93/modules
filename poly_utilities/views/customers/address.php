<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Poly Utilities - Customers - Address
 * @version 1.0
 * @author PolyXGO
 */
?>

<div class="poly-customer-addresses tw-space-y-6" data-customer-id="<?= isset($client) ? (int) $client->userid : 0; ?>">
    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
        <div class="tw-flex tw-flex-col">
            <h4 class="customer-profile-group-heading tw-mb-1">
                <?= _l('poly_utilities_address_tab_heading'); ?>
            </h4>
            <p class="tw-text-sm tw-text-neutral-500 tw-m-0">
                <?= _l('poly_utilities_address_tab_description'); ?>
            </p>
        </div>
        <div class="inline-block new-contact-wrapper tw-mb-4">
            <button type="button"
                class="btn btn-primary tw-mb-0 tw-inline-flex tw-items-center tw-gap-2 poly-address-open-modal"
                data-action="create">
                <i class="fa fa-plus"></i>
                <?= _l('poly_utilities_address_new_address'); ?>
            </button>
        </div>
    </div>

    <div class="panel panel-default tw-shadow-sm">
        <div class="panel-body">
            <?php
            $tableHeads = [
                _l('the_number_sign'),
                _l('poly_utilities_address_title'),
                _l('poly_utilities_address_inline'),
                _l('poly_utilities_address_contact_person'),
                _l('poly_utilities_address_phone'),
                _l('poly_utilities_address_email'),
                _l('options'),
            ];
            render_datatable($tableHeads, 'poly-addresses-table', [], [
                'data-empty' => _l('poly_utilities_address_empty_state'),
            ]);
            ?>
        </div>
    </div>

    <div id="poly-address-preview" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content tw-rounded-lg tw-overflow-hidden">
                <div class="modal-header tw-border-b tw-border-neutral-200">
                    <div class="tw-flex tw-items-center tw-justify-between tw-w-full">
                        <h5 class="modal-title tw-text-base tw-font-semibold tw-text-neutral-700 tw-m-0 tw-flex-1">
                            <?= _l('poly_utilities_address_preview_heading'); ?>
                        </h5>
                        <button type="button" class="close poly-address-preview-close tw-ml-3 tw-text-2xl tw-leading-none tw-text-neutral-500 tw-hover:text-neutral-700 tw-transition" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body tw-space-y-4 tw-px-4 tw-pb-4 tw-pt-2">
                    <div id="poly-address-preview-map" class="tw-rounded tw-overflow-hidden tw-bg-neutral-100 tw-border tw-h-64 tw-flex tw-items-center tw-justify-center tw-relative">
                        <span class="tw-text-neutral-500 tw-text-sm"><?= _l('poly_utilities_address_preview_no_map'); ?></span>
                    </div>
                    <div id="poly-address-preview-info" class="tw-space-y-4"></div>
                </div>
                <div class="modal-footer tw-flex tw-justify-end tw-gap-2 tw-px-4 tw-py-3 tw-bg-neutral-50">
                    <button type="button" class="btn btn-default poly-address-preview-close" data-dismiss="modal">
                        <?= _l('close'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="poly-address-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content tw-rounded-lg tw-overflow-hidden">
            <div class="modal-header tw-border-b tw-border-neutral-200">
                <div class="tw-flex tw-items-center tw-justify-between tw-w-full">
                    <h4 class="modal-title tw-text-lg tw-font-semibold tw-text-neutral-700 tw-m-0 tw-flex-1" id="poly-address-modal-title">
                        <?= _l('poly_utilities_address_modal_title'); ?>
                    </h4>
                    <button type="button" class="close tw-ml-3 tw-text-2xl tw-leading-none tw-text-neutral-500 tw-hover:text-neutral-700 tw-transition" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <?= form_open('', ['id' => 'poly-address-form', 'autocomplete' => 'off']); ?>
            <div class="modal-body tw-space-y-6">
                <input type="hidden" name="id" id="poly_address_id" value="">

                <div class="row">
                    <div class="col-md-6 tw-space-y-3">
                        <div class="form-group">
                            <label for="poly_address_title" class="control-label">
                                <?= _l('poly_utilities_address_field_title'); ?> <span class="tw-text-red-500">*</span>
                            </label>
                            <input type="text" class="form-control" id="poly_address_title" name="title"
                                placeholder="<?= _l('poly_utilities_address_field_title_placeholder'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="poly_address_line1" class="control-label">
                                <?= _l('poly_utilities_address_field_line1'); ?>
                            </label>
                            <textarea class="form-control" id="poly_address_line1" name="address_line1" rows="2"
                                placeholder="<?= _l('poly_utilities_address_field_line1_placeholder'); ?>"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="poly_address_line2" class="control-label">
                                <?= _l('poly_utilities_address_field_line2'); ?>
                            </label>
                            <textarea class="form-control" id="poly_address_line2" name="address_line2" rows="2"
                                placeholder="<?= _l('poly_utilities_address_field_line2_placeholder'); ?>"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="poly_address_city" class="control-label">
                                <?= _l('poly_utilities_address_field_city'); ?>
                            </label>
                            <input type="text" class="form-control" id="poly_address_city" name="city"
                                placeholder="<?= _l('poly_utilities_address_field_city_placeholder'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="poly_address_state" class="control-label">
                                <?= _l('poly_utilities_address_field_state'); ?>
                            </label>
                            <input type="text" class="form-control" id="poly_address_state" name="state"
                                placeholder="<?= _l('poly_utilities_address_field_state_placeholder'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="poly_address_zip" class="control-label">
                                <?= _l('poly_utilities_address_field_zip'); ?>
                            </label>
                            <input type="text" class="form-control" id="poly_address_zip" name="zip"
                                placeholder="<?= _l('poly_utilities_address_field_zip_placeholder'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="poly_address_country" class="control-label">
                                <?= _l('poly_utilities_address_field_country'); ?>
                            </label>
                            <select name="country_id" id="poly_address_country" class="form-control">
                                <option value=""><?= _l('poly_utilities_address_field_country_placeholder'); ?></option>
                                <?php foreach (get_all_countries() as $country) { ?>
                                <option value="<?= (int) $country['country_id']; ?>">
                                    <?= e($country['short_name']); ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 tw-space-y-3">
                        <div class="form-group">
                            <label for="poly_address_contact_person" class="control-label">
                                <?= _l('poly_utilities_address_field_contact_person'); ?>
                            </label>
                            <input type="text" class="form-control" id="poly_address_contact_person"
                                name="contact_person"
                                placeholder="<?= _l('poly_utilities_address_field_contact_person_placeholder'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="poly_address_phone" class="control-label">
                                <?= _l('poly_utilities_address_field_phone'); ?>
                            </label>
                            <input type="text" class="form-control" id="poly_address_phone" name="phone"
                                placeholder="<?= _l('poly_utilities_address_field_phone_placeholder'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="poly_address_email" class="control-label">
                                <?= _l('poly_utilities_address_field_email'); ?>
                            </label>
                            <input type="email" class="form-control" id="poly_address_email" name="email"
                                placeholder="<?= _l('poly_utilities_address_field_email_placeholder'); ?>">
                        </div>
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-3">
                            <div class="checkbox">
                                <label class="tw-flex tw-items-center tw-space-x-2">
                                    <input type="checkbox" name="is_default_billing" id="poly_address_default_billing"
                                        value="1">
                                    <span><?= _l('poly_utilities_address_field_default_billing'); ?></span>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label class="tw-flex tw-items-center tw-space-x-2">
                                    <input type="checkbox" name="is_default_shipping"
                                        id="poly_address_default_shipping" value="1">
                                    <span><?= _l('poly_utilities_address_field_default_shipping'); ?></span>
                                </label>
                            </div>
                        </div>
                        <script type="text/template" id="poly-address-social-template">
                            <div class="poly-address-social-row panel panel-default tw-shadow-sm" data-social-id="<%=id%>">
                                <div class="panel-body">
                                    <div class="tw-grid tw-items-center tw-gap-3" style="display:grid;grid-template-columns:1.4fr 1fr 1.2fr auto;">
                                        <div>
                                            <input type="text"
                                                class="form-control poly-social-icon"
                                                placeholder="<?= _l('poly_utilities_address_social_icon_placeholder'); ?>"
                                                aria-label="<?= _l('poly_utilities_address_social_icon'); ?>"
                                                value="<%=icon%>">
                                        </div>
                                        <div>
                                            <input type="text"
                                                class="form-control poly-social-title"
                                                placeholder="<?= _l('poly_utilities_address_social_title_placeholder'); ?>"
                                                aria-label="<?= _l('poly_utilities_address_social_title'); ?>"
                                                value="<%=title%>">
                                        </div>
                                        <div>
                                            <input type="text"
                                                class="form-control poly-social-value"
                                                placeholder="<?= _l('poly_utilities_address_social_value_placeholder'); ?>"
                                                aria-label="<?= _l('poly_utilities_address_social_value'); ?>"
                                                value="<%=value%>">
                                        </div>
                                        <div class="tw-flex tw-justify-end tw-items-center">
                                            <button type="button" class="btn btn-danger tw-inline-flex tw-items-center tw-justify-center tw-px-3 poly-address-social-remove" title="<?= _l('poly_utilities_address_social_remove'); ?>">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </script>
                    </div>
                </div>
                <div class="tw-space-y-4 tw-mt-4">
                <div class="form-group">
                    <label for="poly_address_map_url" class="control-label">
                        <?= _l('poly_utilities_address_field_map_url'); ?>
                    </label>
                    <input type="text" class="form-control" id="poly_address_map_url" name="map_url"
                        placeholder="<?= _l('poly_utilities_address_field_map_url_placeholder'); ?>">
                </div>
                <div class="form-group">
                    <label for="poly_address_map" class="control-label">
                        <?= _l('poly_utilities_address_field_map_embed'); ?>
                    </label>
                    <textarea class="form-control" id="poly_address_map" name="map_embed" rows="3"
                        placeholder="<?= _l('poly_utilities_address_field_map_embed_placeholder'); ?>"></textarea>
                    <span class="help-block tw-text-xs tw-text-neutral-500">
                        <?= _l('poly_utilities_address_field_map_embed_help'); ?>
                    </span>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="poly_address_latitude" class="control-label">
                                <?= _l('poly_utilities_address_field_latitude'); ?>
                            </label>
                            <input type="text" class="form-control" id="poly_address_latitude" name="latitude"
                                placeholder="<?= _l('poly_utilities_address_field_latitude_placeholder'); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="poly_address_longitude" class="control-label">
                                <?= _l('poly_utilities_address_field_longitude'); ?>
                            </label>
                            <input type="text" class="form-control" id="poly_address_longitude" name="longitude"
                                placeholder="<?= _l('poly_utilities_address_field_longitude_placeholder'); ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="poly_address_additional_info" class="control-label">
                        <?= _l('poly_utilities_address_field_additional_info'); ?>
                    </label>
                    <textarea class="form-control" id="poly_address_additional_info" name="additional_info"
                        rows="3"
                        placeholder="<?= _l('poly_utilities_address_field_additional_info_placeholder'); ?>"></textarea>
                </div>
                <div class="tw-space-y-3 poly-address-social-wrapper">
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <h5 class="tw-text-base tw-font-semibold tw-text-neutral-700 tw-m-0">
                            <?= _l('poly_utilities_address_social_links_heading'); ?>
                        </h5>
                        <button type="button" class="btn btn-default btn-sm poly-address-social-add">
                            <i class="fa fa-plus"></i> <?= _l('poly_utilities_address_social_add'); ?>
                        </button>
                    </div>
                    <div class="tw-space-y-3 poly-address-social-list"></div>
                </div>
            </div>
            </div>
            
            <div class="modal-footer tw-flex tw-justify-end tw-gap-2">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= _l('close'); ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <?= _l('save'); ?>
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

