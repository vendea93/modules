<?php

class Ai_project_analyzer_module
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function queue()
    {
        $this->ci->db->where('iscronfinished', 0);
        $ai_project_analyzer_queues = $this->ci->db->get(AI_PROJECT_ANALYZER_QUEUE_TABLE)->result_array();
        if (!empty($ai_project_analyzer_queues)) {
            foreach ($ai_project_analyzer_queues as $ai_project_analyzer_queue) {
                // Get project id and analysis id
                $projectId = $ai_project_analyzer_queue['project_id'];
                $analysis_hash = $ai_project_analyzer_queue['analysis_hash'];

                // Get the final prompt from the analysis
                $this->ci->db->where('project_id', $projectId)
                    ->where('hash', $analysis_hash);
                $analysis = $this->ci->db->get(AI_PROJECT_ANALYZER_TABLE)->row();

                // Make an API call to AI Provider and generate the analysis
                $response = generate_ai_analysis(
                    nl2br($analysis->ai_prompt),
                    $analysis->custom_instructions ?? '',
                    $analysis->tone ?? '',
                    $analysis->language ?? 'English',
                    [
                        'original_name' => $analysis->attachment_label ?? '',
                        'file_text' => $analysis->attachment_text ?? '',
                    ]
                );

                // Update the analysis in the database
                $this->ci->db->where('project_id', $projectId)
                    ->where('hash', $analysis_hash);
                $this->ci->db->update(AI_PROJECT_ANALYZER_TABLE, [
                    'analysis' => $response['choices'][0]['message']['content'],
                    'tokens_used' => $response['usage']['prompt_tokens'] + $response['usage']['completion_tokens'],
                    'cost_usd' => calculate_model_cost($response['usage']['prompt_tokens'], $response['usage']['completion_tokens']),
                    'status' => 'generated',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                // Set cron as finished
                $this->ci->db->where('project_id', $projectId)
                    ->where('analysis_hash', $analysis_hash);
                $this->ci->db->update(AI_PROJECT_ANALYZER_QUEUE_TABLE, [
                    'iscronfinished' => 1,
                ]);
            }
        }
    }
}