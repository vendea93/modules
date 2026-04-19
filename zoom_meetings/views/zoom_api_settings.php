<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-5 left-column">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open('zoom_meetings/zoom_meetings/api_meeting_submit', array('id' => 'meeting-submit-form')); ?>
                        <div class="form-group projects-wrapper">
                            <div id="project_ajax_search_wrapper"></div>
                        </div>
                        <?php
                        // Include the ID as a hidden input field
                        echo form_hidden('id', $settings[0]['id']);
                        ?>

                        <?php
                        $email = $settings[0]['zoom_email'];
                        echo render_input('zoom_email', 'zoom_email', $email, 'text', array('required' => 'true'));
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $api_key = $settings[0]['api_key'];
                                echo render_input('api_key', 'zoom_api_key', $api_key, 'text', array('required' => 'true'));
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $api_secret = $settings[0]['api_secret'];
                                echo render_input('api_secret', 'zoom_api_secret', $api_secret, 'text', array('required' => 'true'));
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                // Define variables
                                $uri = $settings[0]['api_key'];
                                $client_id = $settings[0]['zoom_email'];
                                $access_token = $settings[0]['access_token'];

                                // Initialize connection status
                                $isConnected = false;

                                if ($access_token) {
                                 $ch = curl_init();
                                 curl_setopt($ch, CURLOPT_URL, 'https://api.zoom.us/v2/users/me');
                                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                

                                 curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                     'Authorization: Bearer ' . $access_token,
                                     'Content-Type: application/json',
                                 ]);
                                 
                                 $response = curl_exec($ch);
                                 $error = curl_error($ch);
                                 $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                 
                                 curl_close($ch);
                                 
                                 if ($response === false || $httpCode !== 200) {
                                     echo '<pre>';
                                     echo "cURL Error: " . $error . "\n";
                                     echo "HTTP Status Code: " . $httpCode . "\n";
                                     exit;
                                 }
                                 
                                 $responseData = json_decode($response, true);
                                 
                                    if (isset($responseData['id']) && !empty($responseData['id'])) {
                                        $isConnected = true;
                                    } else {
                                        log_message('error', 'Zoom API response error: ' . json_encode($responseData));
                                    }

                                    curl_close($ch);
                                }

                                // Display App Status and Authorization Button
                                ?>
                                <div>
                                    <strong>App Status:</strong>
                                    <?php if ($isConnected) { ?>
                                        <span style="color: green;"><b>Connected</b></span>
                                    <?php } else { ?>
                                        <span style="color: red;"><b>Not Connected</b></span>
                                        <?php
                                        $authorizationUrl = "https://zoom.us/oauth/authorize?response_type=code&client_id={$client_id}&redirect_uri={$uri}";
                                        echo "<a href='{$authorizationUrl}' class='btn btn-primary mb-3'>Authorize the App</a>";
                                        ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

</body>
</html>
