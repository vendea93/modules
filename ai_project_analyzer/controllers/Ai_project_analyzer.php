<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ai_project_analyzer extends AdminController
{
    public $app_modules;

    public function __construct()
    {
        parent::__construct();

        // Initialize and check module access
        $this->app_modules = new App_modules();
        if ($this->app_modules->is_inactive('ai_project_analyzer')) {
            access_denied();
        }

        // Load models
        $this->load->model('projects_model');
    }

    /**
     * Send project analysis to client/staff via email
     * 
     * @return void
     */
    public function send_to_email()
    {
        if (staff_cant('send_to_email', 'ai_project_analyzer') || !$this->input->post()) {
            access_denied('ai_project_analyzer');
        }

        try {
            $analysis_id = $this->input->post('analysis_id');
            $project_id = $this->input->post('project_id');

            if (!$analysis_id) {
                throw new Exception('Analysis ID is required');
            }

            // Get AI analysis
            $analysis = $this->db->where('id', $analysis_id)
                ->get(AI_PROJECT_ANALYZER_TABLE)
                ->row();

            // Get the project
            $project = $this->projects_model->get($project_id);

            if (!$analysis || !$project) {
                throw new Exception('Something went wrong!');
            }

            // Get recipients and email template
            $recipients = $this->input->post('send_to');
            $email_template = $this->input->post('email_template_custom');
            // dd($recipients);

            if (empty($recipients)) {
                throw new Exception('No recipients selected');
            }

            if (empty($email_template)) {
                throw new Exception('Email template is required');
            }

            // Generate PDF attachment
            $pdf_path = $this->_generatePdf($analysis, 'email');

            $success_count = 0;
            $failed_recipients = [];

            // Send email to each recipient
            foreach ($recipients as $recipient_email) {
                try {
                    // Get recipient details
                    $recipient = $this->_getRecipientDetails($recipient_email);

                    if (!$recipient) {
                        $failed_recipients[] = "Unknown recipient (Email: $recipient_email)";
                        continue;
                    }

                    // Process email template with variables
                    $processed_template = $this->_processEmailTemplate($email_template, $recipient, $analysis, $project);

                    // Send email
                    if ($this->_sendAnalysisEmail($recipient, $processed_template, $pdf_path, $analysis)) {
                        $success_count++;
                    } else {
                        $failed_recipients[] = $recipient['name'] . ' (' . $recipient['email'] . ')';
                    }

                } catch (Exception $e) {
                    $failed_recipients[] = "Email $recipient_email: " . $e->getMessage();
                }
            }

            // Clean up temporary PDF file
            if (file_exists($pdf_path)) {
                unlink($pdf_path);
            }

            // Set appropriate alert message
            if ($success_count > 0) {
                $message = sprintf('Analysis sent successfully to %d recipient(s)', $success_count);
                if (!empty($failed_recipients)) {
                    $message .= '. Failed to send to: ' . implode(', ', $failed_recipients);
                }
                set_alert('success', $message);
            } else {
                set_alert('danger', 'Failed to send analysis to all recipients: ' . implode(', ', $failed_recipients));
            }

        } catch (Exception $e) {
            set_alert('danger', $e->getMessage());
        }

        redirect(admin_url('projects/view/' . $analysis->project_id . '?group=project_analysis'));
    }

    /**
     * Get recipient details by email
     * 
     * @param string $recipient_email
     * @return array|null
     */
    private function _getRecipientDetails($recipient_email)
    {
        // Skip if empty email (disabled options)
        if (empty($recipient_email)) {
            return null;
        }

        // Check if it's a staff member
        $staff = $this->db->where('email', $recipient_email)->get(db_prefix() . 'staff')->row_array();
        if ($staff) {
            return [
                'name' => get_staff_full_name($staff['staffid']),
                'email' => $staff['email'],
                'type' => 'staff'
            ];
        }

        // Check if it's a customer contact
        $contact = $this->db->where('email', $recipient_email)->get(db_prefix() . 'contacts')->row_array();
        if ($contact) {
            return [
                'name' => $contact['firstname'] . ' ' . $contact['lastname'],
                'email' => $contact['email'],
                'type' => 'customer'
            ];
        }

        return null;
    }

    /**
     * Process email template with variables
     * 
     * @param string $template
     * @param array $recipient
     * @param object $analysis
     * @return string
     */
    private function _processEmailTemplate($template, $recipient, $analysis, $project)
    {
        $replacements = [
            '{recipient_name}' => $recipient['name'],
            '{project_name}' => $project->name,
            '{analysis_prompt_name}' => $analysis->prompt_name
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Send analysis email
     * 
     * @param array $recipient
     * @param string $message
     * @param string $pdf_path
     * @param object $analysis
     * @return bool
     */
    private function _sendAnalysisEmail($recipient, $message, $pdf_path, $analysis)
    {
        $this->load->library('email');

        $project_name = get_project_name_by_id($analysis->project_id);
        $subject = 'AI Project Analysis - ' . $project_name;

        // Configure email
        $this->email->clear();
        $this->email->from(get_option('smtp_email'), get_option('companyname'));
        $this->email->to($recipient['email']);
        $this->email->subject($subject);
        $this->email->message($message);

        // Attach PDF
        if (file_exists($pdf_path)) {
            $this->email->attach($pdf_path);
        }

        return $this->email->send();
    }

    /**
     * Delete AI Project Analysis
     * 
     * @return void
     */
    public function delete()
    {
        if (!staff_can('delete', 'ai_project_analyzer') || !$this->input->post()) {
            access_denied('ai_project_analyzer');
        }

        try {
            $ai_analysis_id = $this->input->post('analysis_id');
            $project_id = $this->input->post('project_id');

            if (!$ai_analysis_id || !$project_id) {
                throw new Exception('Missing required parameters');
            }

            // Get and validate analysis
            $analysis = $this->db->where('id', $ai_analysis_id)
                ->get(AI_PROJECT_ANALYZER_TABLE)
                ->row();

            if (!$analysis) {
                throw new Exception('Analysis not found');
            }

            // Delete operations in transaction
            $this->db->trans_start();

            $this->db->where('id', $ai_analysis_id)->delete(AI_PROJECT_ANALYZER_TABLE);

            if ($analysis->hash) {
                $this->db->where('analysis_hash', $analysis->hash)
                    ->delete(AI_PROJECT_ANALYZER_QUEUE_TABLE);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed to delete analysis');
            }

            set_alert('success', _l('ai_project_analyzer_successfully_deleted'));
            redirect(admin_url('projects/view/' . $project_id . '?group=project_analysis'));

        } catch (Exception $e) {
            set_alert('danger', $e->getMessage());
            redirect(admin_url('projects'));
        }
    }

    /**
     * Download AI analysis as PDF
     * 
     * @return void
     */
    public function download_pdf()
    {
        if (!staff_can('download', 'ai_project_analyzer') || !$this->input->post()) {
            access_denied('ai_project_analyzer');
        }

        try {
            $id = $this->input->post('analysis_id');
            if (!$id) {
                throw new Exception('Analysis ID required');
            }

            $analysis = $this->db->where('id', $id)
                ->get(AI_PROJECT_ANALYZER_TABLE)
                ->row();

            if (!$analysis) {
                throw new Exception('Analysis not found');
            }

            $this->_generatePdf($analysis);

        } catch (Exception $e) {
            set_alert('danger', $e->getMessage());
            redirect(admin_url('projects/view/' . $analysis->project_id . '?group=project_analysis'));
        }
    }

    /**
     * Generate PDF for analysis (both download and email)
     * 
     * @param object $analysis
     * @param string $type - 'download' or 'email'
     * @return string|void Returns file path for email, void for download
     */
    private function _generatePdf($analysis, $type = 'download')
    {
        $project_name = get_project_name_by_id($analysis->project_id);

        $pdf = new TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetCreator(creator: get_option('companyname'));
        $pdf->SetAuthor(get_option('companyname'));
        $pdf->SetTitle('AI Analysis for ' . $project_name);
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        // Clean the HTML for PDF - now returns proper HTML for TCPDF
        $analysis_html = $this->_cleanHtmlForPdf($analysis->analysis);

        // Compact CSS for better formatting
        $css = '
    <style>
        body { 
            font-family: "DejaVu Sans", sans-serif; 
            line-height: 1.4; 
            color: #333; 
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        h1 { 
            color: #333; 
            font-size: 18px;
            margin-bottom: 8px;
            margin-top: 0px;
            font-weight: bold;
        }
        h2 {
            color: #333;
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            page-break-after: avoid;
        }
        h3 {
            color: #333;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 3px;
        }
        .subtitle {
            color: #666;
            font-size: 13px;
            margin-top: 2px;
            margin-bottom: 12px;
            font-style: italic;
        }
        .author {
            color: #e74c3c;
            font-weight: bold;
        }
        .date {
            color: #666;
            font-size: 10px;
        }
        p {
            margin-bottom: 8px;
            text-align: justify;
        }
        ul {
            margin: 5px 0;
            padding-left: 18px;
            page-break-inside: avoid;
        }
        li {
            margin-bottom: 4px;
            line-height: 1.3;
            page-break-inside: avoid;
        }
        .recommendations {
            page-break-before: auto;
            page-break-inside: avoid;
        }
        .key-point {
            margin: 8px 0;
            padding: 6px 8px;
        }
        .key-point-label {
            font-weight: bold;
            color: #92400e;
            display: inline;
            margin-right: 5px;
        }
        .action-item {
            margin: 8px 0;
            padding: 6px 8px;
        }
        .action-label {
            font-weight: bold;
            color: #007bff;
            display: inline;
            margin-right: 5px;
        }
        .action-item {
            margin: 8px 0;
            padding: 6px 8px;
        }
        .action-label {
            font-weight: bold;
            color: #007bff;
            display: inline;
            margin-right: 5px;
        }
        .timeline-item {
            margin: 3px 0;
            padding-left: 12px;
            border-left: 2px solid #ddd;
        }
        .timeline-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 1px;
            margin-top: 0px;
        }
        .timeline-status {
            font-style: italic;
            color: #666;
            font-size: 9px;
            margin-top: 1px;
            margin-bottom: 0px;
        }
    </style>';

        $html = sprintf(
            '%s<h1>AI Analysis for %s</h1><div class="subtitle">%s</div><p><span class="author">%s</span> - <span class="date">%s</span></p><div>%s</div>',
            $css,
            htmlspecialchars($project_name),
            htmlspecialchars($analysis->prompt_name),
            htmlspecialchars(get_staff_full_name($analysis->owner)),
            _dt($analysis->created_at),
            $analysis_html
        );

        if ($analysis->language === 'Arabic') {
            $pdf->setRTL(true);
        }

        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = slug_it($project_name) . '_analysis_' . $analysis->id . '.pdf';

        if ($type === 'download') {
            $pdf->Output($filename, 'D');
        } else {
            $temp_dir = get_temp_dir();
            $filepath = $temp_dir . $filename;
            $pdf->Output($filepath, 'F');
            return $filepath;
        }
    }
    private function _cleanHtmlForPdf($html)
    {
        // Remove script tags for security
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);

        // Remove outer container div with AI classes
        $html = preg_replace('/<div[^>]*ai-mt-6[^>]*>/', '', $html);
        $html = preg_replace('/<\/div>$/', '', $html);

        // Convert AI section titles to proper h2 tags
        $html = preg_replace('/<h2[^>]*ai-section-title[^>]*>(.*?)<\/h2>/s', '<h2>$1</h2>', $html);

        // Convert key points to inline format for compactness
        $html = preg_replace(
            '/<div[^>]*ai-key-point[^>]*>.*?<div[^>]*ai-key-point-label[^>]*>(.*?)<\/div>.*?<div[^>]*ai-key-point-content[^>]*>(.*?)<\/div>.*?<\/div>/s',
            '<div class="key-point"><div class="key-point-label">$1</div> $2</div>',
            $html
        );

        // Convert key points to inline format for compactness
        $html = preg_replace(
            '/<div[^>]*ai-action-item[^>]*>.*?<div[^>]*ai-action-label[^>]*>(.*?)<\/div>.*?<div[^>]*ai-action-content[^>]*>(.*?)<\/div>.*?<\/div>/s',
            '<div class="action-item"><div class="action-label">$1</div> $2</div>',
            $html
        );

        // Handle timeline items more compactly
        $html = preg_replace('/<div[^>]*ai-timeline[^>]*>/', '', $html);
        $html = preg_replace('/<div[^>]*ai-timeline-item[^>]*>/', '<div class="timeline-item">', $html);
        $html = preg_replace('/<div[^>]*ai-timeline-marker[^>]*><\/div>/', '', $html);
        $html = preg_replace('/<div[^>]*ai-timeline-content[^>]*>/', '', $html);
        $html = preg_replace('/<h3[^>]*ai-timeline-title[^>]*>(.*?)<\/h3>/s', '<h3 class="timeline-title">$1</h3>', $html);
        $html = preg_replace('/<p[^>]*ai-timeline-description[^>]*>(.*?)<\/p>/s', '<p style="margin-bottom:2px;">$1</p>', $html);
        $html = preg_replace('/<span[^>]*ai-timeline-status[^>]*>(.*?)<\/span>/s', '<div class="timeline-status">Status: $1</div>', $html);

        // Close timeline divs properly to avoid extra spacing
        $html = preg_replace('/<\/div>\s*<\/div>\s*<\/div>/', '</div>', $html);

        // Handle lists
        $html = preg_replace('/<ul[^>]*ai-list[^>]*>/', '<ul>', $html);
        $html = preg_replace('/<li[^>]*ai-list-item[^>]*>/s', '<li>', $html);

        // Convert paragraphs with AI classes to regular paragraphs
        $html = preg_replace('/<p[^>]*ai-paragraph[^>]*>/s', '<p>', $html);

        // Clean up any remaining AI classes
        $html = preg_replace('/\s*class="[^"]*ai-[^"]*"/', '', $html);
        $html = preg_replace('/\s*class=""/', '', $html);

        // Clean up extra divs and spacing
        $html = preg_replace('/<div>\s*<\/div>/', '', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = str_replace('> <', '><', $html);

        // Remove excessive line breaks and spacing
        $html = preg_replace('/<br\s*\/?>/i', '', $html);

        return trim($html);
    }
}