<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Poly Utilities - Multiple Companies - Company Modal Script
 * @version 1.0
 * @author PolyXGO
 */
?>
<script>
    (function() {
        'use strict';

        var jq = window.jQuery || window.$;

        if (!jq || typeof jq.fn === 'undefined') {
            return;
        }

        jq(function() {
            jq('#company').on('change', function() {
                var newCompanyName = jq(this).val();

                if (!newCompanyName) {
                    return;
                }

                requestGet(
                    admin_url + "poly_utilities/multiple_companies/check_company_name?company=" + newCompanyName
                ).done(function(response) {
                    jq('#company').parent('.form-group').after(response);
                });
            });
        });
    })();
</script>

