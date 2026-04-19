<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>

<body class="tw-bg-neutral-100 login_admin">
    <div class="tw-max-w-md tw-mx-auto tw-pt-24 authentication-form-wrapper tw-relative tw-z-20">
        <?php $this->load->view('authentication/includes/alerts'); ?>
        <?php echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>

        <!-- OTP-less Login Container -->
        <div id="otpless-login-page"></div>
    </div>

    <!-- Hidden Form for OTP-less Login -->
    <?php echo form_open(admin_url('otpless/admin_login'), ['id' => 'otpless_form']); ?>
        <input type="hidden" id="hidden_data" name="data">
    <?php echo form_close(); ?>

 
    <!-- OTP-less SDK -->
    <script id="otpless-sdk" type="text/javascript" data-appid="<?php echo get_option('otpless_appid'); ?>" src="https://otpless.com/v2/auth.js"></script>
    <script type="text/javascript" src="<?php echo module_dir_url('otpless','assets/js/otpless.js') ?>"></script>

</body>
</html>
