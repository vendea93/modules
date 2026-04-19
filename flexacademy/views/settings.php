<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s tw-max-w-xl tw-mx-auto">
                    <div class="panel-heading tw-flex tw-items-center tw-justify-between">
                        <span class="tw-font-semibold tw-text-lg">
                            <?php echo $title; ?>
                        </span>
                        <a href="<?php echo admin_url('flexacademy'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-graduation-cap tw-mr-1"></i>
                            <?php echo _flexacademy_lang('flexacademy'); ?>
                        </a>
                    </div>
                    <?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'flexacademy_settings_form']); ?>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="flexacademy_certificate_issuer_signature" class="control-label">
                                <?php echo _flexacademy_lang('certificate_issuer_signature'); ?>
                            </label>
                            <input type="file" name="flexacademy_certificate_issuer_signature" id="flexacademy_certificate_issuer_signature" class="form-control" accept="image/*">
                            <?php if (!empty($certificate_issuer_signature_url)) : ?>
                                <div class="tw-flex tw-items-center tw-gap-3 tw-mt-2">
                                    <img src="<?php echo $certificate_issuer_signature_url; ?>" alt="<?php echo _flexacademy_lang('certificate_issuer_signature'); ?>" class="img-thumbnail" style="max-height: 80px;">
                                    <div class="checkbox checkbox-primary tw-mt-0">
                                        <input type="checkbox" id="remove_certificate_issuer_signature" name="remove_certificate_issuer_signature" value="1">
                                        <label for="remove_certificate_issuer_signature">
                                            <?php echo _flexacademy_lang('remove_file'); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php if (!empty($certificate_issuer_signature_path)) : ?>
                                    <p class="help-block tw-mt-2">
                                        <?php echo _flexacademy_lang('current_file'); ?>:
                                        <span class="tw-font-mono"><?php echo basename($certificate_issuer_signature_path); ?></span>
                                    </p>
                                <?php endif; ?>
                            <?php else : ?>
                                <p class="help-block">
                                    <?php echo _flexacademy_lang('certificate_issuer_signature_help'); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="flexacademy_certificate_prefix" class="control-label">
                                <?php echo _flexacademy_lang('certificate_prefix'); ?>
                            </label>
                            <input type="text" class="form-control" id="flexacademy_certificate_prefix" name="flexacademy_certificate_prefix" value="<?php echo html_escape($certificate_prefix); ?>" maxlength="10">
                            <p class="help-block">
                                <?php echo _flexacademy_lang('certificate_prefix_help'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="panel-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save tw-mr-1"></i>
                            <?php echo _flexacademy_lang('save_settings'); ?>
                        </button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        appValidateForm($('#flexacademy_settings_form'), {});
    });
</script>
</body>
</html>

