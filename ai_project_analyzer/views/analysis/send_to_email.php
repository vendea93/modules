<div class="modal fade" id="sendToEmail" tabindex="-1" role="dialog" aria-labelledby="sendToEmail">
    <div class="modal-dialog" role="document">
        <?= form_open(admin_url('ai_project_analyzer/send_to_email'), ['id' => 'sendToEmailForm']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="ai-text-xl ai-font-semibold ai-text-left">
                    <?= _l('ai_project_analyzer_send_to_email_analysis') ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        echo form_hidden('analysis_id', $analysis_id);
                        echo form_hidden('project_id', $project_id);

                        $staff_list = $this->staff_model->get();
                        $customers_list = $this->clients_model->get_contacts();
                        $send_to = [];

                        $send_to[] = [
                            'email' => '--- Staff ---',
                            'name' => '',
                            'option_attributes' => ['disabled' => 'true']
                        ];

                        foreach ($staff_list as $staff) {
                            $send_to[] = [
                                'email' => $staff['email'],
                                'name' => get_staff_full_name($staff['staffid']),
                            ];
                        }

                        $send_to[] = [
                            'email' => '--- Customers ---',
                            'name' => '',
                            'option_attributes' => ['disabled' => 'true']
                        ];

                        foreach ($customers_list as $customer) {
                            $send_to[] = [
                                'email' => $customer['email'],
                                'name' => get_company_name($customer['id']) . ' (' . $customer['firstname'] . ' ' . $customer['lastname'] . ')',
                            ];
                        }
                        echo render_select('send_to[]', $send_to, ['email', 'email', 'name'], 'invoice_estimate_sent_to_email', '', ['data-none-selected-text' => _l('ai_project_analyzer_select_recipients'), 'multiple' => true], [], '', '', false);
                        ?>

                        <h5 class="bold">
                            <?= _l('invoice_send_to_client_preview_template'); ?>
                        </h5>

                        <?php
                        $default_template = 'Dear {recipient_name},<br><br>
Please find attached the <strong>{analysis_prompt_name}</strong> analysis report for the <strong>{project_name}</strong> project.<br><br>
Best regards,<br>
' . get_option('companyname');

                        echo render_textarea('email_template_custom', '', $default_template, [], [], 'ai-mt-2', 'tinymce');
                        ?>

                        <div class="alert alert-info" style="margin-bottom: 10px;">
                            <strong>Available Variables:</strong><br>
                            <code>{recipient_name}</code> - Recipient's full name<br>
                            <code>{project_name}</code> - Project name<br>
                            <code>{analysis_prompt_name}</code> - Analysis name
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" autocomplete="off" class="btn btn-primary">
                    <?= _l('send'); ?>
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>