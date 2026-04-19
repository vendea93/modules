<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Poly Utilities - Multiple Companies - Contact Modal Script
 * @version 1.0
 * @author PolyXGO
 */
?>
<script>
    var poly_mc_customer_id = 0;
    var poly_mc_contact_id = 0;

    $(document).ready(function() {

        if ($('#contact-form').find('input[name="email"]').length) {

            if ("undefined" != typeof customer_id) {
                poly_mc_customer_id = customer_id;
            }

            poly_mc_contact_id = $('input[name="contactid"]').val();

            $('input[name="email"]').on('blur', function() {
                var emailValue = $(this).val();

                if (emailValue) {
                    requestGet(admin_url + "poly_utilities/multiple_companies/get_contact_companies/" + 
                        poly_mc_customer_id + "/" + poly_mc_contact_id + "?email_adress=" + emailValue)
                        .done(function(response) {
                            if ($('#poly_mc_contact_companies').length) {
                                $('#poly_mc_contact_companies').remove();
                            }
                            $('input[name="email"]').parent('.form-group').after(response);
                        });
                }
            });

            // Load on page load if email exists
            if ($('input[name="email"]').val()) {
                requestGet(admin_url + "poly_utilities/multiple_companies/get_contact_companies/" + 
                    poly_mc_customer_id + "/" + poly_mc_contact_id + "?email_adress=" + $('#email').val())
                    .done(function(response) {
                        $('input[name="email"]').parent('.form-group').after(response);
                    });
            }

            // Override form validation
            appValidateForm('#contact-form', {
                firstname: 'required',
                lastname: 'required',
                password: {
                    required: {
                        depends: function(element) {
                            var $sentSetPassword = $('input[name="send_set_password_email"]');
                            if ($('#contact input[name="contactid"]').val() == '' && 
                                $sentSetPassword.prop('checked') == false) {
                                return true;
                            }
                        }
                    }
                },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: admin_url + "poly_utilities/multiple_companies/check_contact_email",
                        type: 'post',
                        data: {
                            email: function() {
                                return $('#contact-form input[name="email"]').val();
                            },
                            customer_id: poly_mc_customer_id,
                            contact_id: poly_mc_contact_id,
                        }
                    }
                }
            }, contactFormHandler);

        }

    });
</script>

