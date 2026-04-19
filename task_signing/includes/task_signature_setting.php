<?php


echo "<hr />";

echo render_yes_no_option('ts_complete_task_without_sign','ts_complete_without_sign');

echo "<hr />";

echo render_yes_no_option('ts_followers_will_sign','ts_followers_will_sign');


echo "<hr />";

echo render_yes_no_option('ts_not_complete_without_customer_sign','ts_not_complete_without_customer_sign');


$task_merge_field = [
    '{signature_link}'      => _l('ts_signature_url'),
    '{task_name}'           => _l('ts_task_name'),
    '{client}'              => _l('client'),
    '{contact_full_name}'   => _l('contact_primary'),
];

$ts_signature_email_content = ts_task_client_signature_email_content();

?>

<hr />

<div class="row">
    <div class="col-md-8">
        <label class="control-label"><?php echo _l('ts_client_signature_email_content')?></label>
        <textarea rows="10" class="form-control" name="settings[ts_signature_email_content]" id="ts_signature_email_content"><?php echo $ts_signature_email_content?></textarea>
    </div>
    <div class="col-md-4">
        <?php foreach ( $task_merge_field as $slug => $label ) {

            echo "<a href='#' class='add_merge_field' merge_slug='$slug' > <span style='float: left'> ".$label." </span></a> <br />";

        } ?>
    </div>
</div>

<script>

    document.addEventListener("DOMContentLoaded", function() {

        $('.add_merge_field').on('click', function(e) {

            e.preventDefault();

            var merge_text = $(this).attr('merge_slug');

            $('#ts_signature_email_content').val(function(i, val) {
                return val + " "+merge_text;
            });


        });


    });

</script>