<?php

use PhpOffice\PhpWord\IOFactory;

defined('BASEPATH') or exit('No direct script access allowed');

class Generate extends AdminController
{
    private const ALLOWED_FILE_TYPES = ['pdf', 'docx', 'csv', 'txt'];
    public $app_modules;

    public function __construct()
    {
        parent::__construct();

        // Initialize the app modules class
        $this->app_modules = new App_modules;
        if ($this->app_modules->is_inactive('ai_project_analyzer')) {
            access_denied();
        }

        // Load models
        $this->load->model('projects_model');
        $this->load->model('tasks_model');
    }

    /**
     * Generate AI project analysis
     * 
     * @param int $projectId
     * @param int $prompt_id
     * @return void
     */
    public function index()
    {
        // Validate request
        if (!$this->_validateGenerateRequest()) {
            return;
        }

        try {
            // Get required data
            $prompt_id = $this->input->post('prompt_id');
            $projectId = $this->input->post('project_id');
            $ai_prompt = $this->_getPromptById($prompt_id);
            if (!$ai_prompt) {
                throw new Exception('Invalid prompt ID');
            }

            $project = $this->projects_model->get($projectId);
            if (!$project) {
                throw new Exception('Project not found');
            }

            // Process data
            $hash = app_generate_hash();
            $processed_prompt = $this->_buildProcessedPrompt($ai_prompt, $project, $projectId);
            $post_data = $this->_getPostData();
            $uploaded_file = $this->_processUploadedFile($projectId);

            // Generate analysis
            if (get_option('ai_project_analyzer_use_cron')) {
                $this->_handleCronGeneration($projectId, $hash, $ai_prompt, $processed_prompt, $uploaded_file, $post_data);
            } else {
                $this->_handleDirectGeneration($projectId, $hash, $ai_prompt, $processed_prompt, $uploaded_file, $post_data);
            }

        } catch (Exception $e) {
            $this->_handleError($e->getMessage());
        }
    }

    /**
     * Validate generate request
     * 
     * @return bool
     */
    private function _validateGenerateRequest()
    {
        if (staff_cant('create', 'ai_project_analyzer') || !$this->input->post()) {
            access_denied('ai_project_analyzer');
            return false;
        }
        return true;
    }

    /**
     * Get prompt by ID
     * 
     * @param int $prompt_id
     * @return object|null
     */
    private function _getPromptById($prompt_id)
    {
        return $this->db->where('id', $prompt_id)
            ->get(AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE)
            ->row();
    }

    /**
     * Build processed prompt with replacements
     * 
     * @param object $ai_prompt
     * @param object $project
     * @param int $projectId
     * @return string
     */
    private function _buildProcessedPrompt($ai_prompt, $project, $projectId)
    {
        $replacements = [
            '{project_name}' => $project->name ?? '',
            '{project_customer}' => get_company_name(get_client_id_by_project_id($project->id), true) ?? '',
            '{project_status}' => $project->status ?? '',
            '{project_description}' => $project->description ?? '',
            '{project_start_date}' => $project->start_date ?? '',
            '{project_deadline}' => $project->deadline ?? '',
            '{project_members}' => $this->_getProjectMembersString($projectId),
            '{project_tasks}' => $this->_getProjectTasksString($projectId),
            '{project_milestones}' => $this->_getProjectMilestonesString($projectId),
            '{project_activity}' => $this->_getProjectActivityString($projectId),
        ];

        $processed_prompt = str_replace(array_keys($replacements), array_values($replacements), $ai_prompt->prompt);
        return strip_tags($processed_prompt);
    }

    /**
     * Get project members as formatted string
     * 
     * @param int $projectId
     * @return string
     */
    private function _getProjectMembersString($projectId)
    {
        $members = $this->projects_model->get_project_members($projectId, true);
        return format_project_items(
            $members,
            [],
            fn($m) => trim(($m['firstname'] ?? '') . ' ' . ($m['lastname'] ?? ''))
        );
    }

    /**
     * Get project tasks as formatted string
     * 
     * @param int $projectId
     * @return string
     */
    private function _getProjectTasksString($projectId)
    {
        $tasks = $this->projects_model->get_tasks($projectId);

        return format_project_items(
            $tasks,
            [],
            function ($task) {
                $parts = ["Task Name: " . ($task['name'] ?? '')];

                if (!empty($task['status'])) {
                    $status = get_task_status_by_id($task['status']);
                    $parts[] = "Task Status: " . ($status['name'] ?? '');
                }

                // Add task details
                $fields = [
                    'description' => 'Task Description',
                    'duedate' => 'Task Deadline',
                    'milestone_name' => 'Task Milestone'
                ];

                foreach ($fields as $key => $label) {
                    if (!empty($task[$key])) {
                        $parts[] = "{$label}: {$task[$key]}";
                    }
                }

                // Add related data
                $this->_addTaskRelatedData($task['id'], $parts);

                return implode(' | ', $parts);
            }
        );
    }

    /**
     * Add task related data (assignees, followers, comments, timesheets)
     * 
     * @param int $taskId
     * @param array &$parts
     * @return void
     */
    private function _addTaskRelatedData($taskId, &$parts)
    {
        // Get task related data
        $assignees = $this->tasks_model->get_task_assignees($taskId);
        $followers = $this->tasks_model->get_task_followers($taskId);
        $comments = $this->tasks_model->get_task_comments($taskId);
        $timesheets = $this->tasks_model->get_timesheeets($taskId);

        // Process assignees
        if ($assignees) {
            $assignee_names = array_filter(array_map(function ($a) {
                return trim(($a['firstname'] ?? '') . ' ' . ($a['lastname'] ?? ''));
            }, $assignees));

            if ($assignee_names) {
                $parts[] = "Task Assignees: " . implode(', ', $assignee_names);
            }
        }

        // Process followers
        if ($followers) {
            $follower_names = array_filter(array_map(function ($f) {
                return trim(($f['firstname'] ?? '') . ' ' . ($f['lastname'] ?? ''));
            }, $followers));

            if ($follower_names) {
                $parts[] = "Task Followers: " . implode(', ', $follower_names);
            }
        }

        // Process comments
        if ($comments) {
            $comment_contents = array_filter(array_map(function ($c) {
                if (empty($c['content']))
                    return null;
                $author = trim(($c['firstname'] ?? '') . ' ' . ($c['lastname'] ?? ''));
                return "{$c['content']} By {$author}";
            }, $comments));

            if ($comment_contents) {
                $parts[] = "Task Comments: " . implode(' || ', $comment_contents);
            }
        }

        // Process timesheets
        if ($timesheets) {
            $timesheet_contents = array_filter(array_map(function ($t) {
                if (!isset($t['full_name'], $t['note'], $t['start_time'], $t['end_time'])) {
                    return null;
                }
                return "Timesheet for {$t['full_name']} | Note: {$t['note']} | Start: " .
                    _dt($t['start_time'], true) . " | End: " . _dt($t['end_time'], true);
            }, $timesheets));

            if ($timesheet_contents) {
                $parts[] = "Task Timesheets: " . implode(' || ', $timesheet_contents);
            }
        }
    }

    /**
     * Get project milestones as formatted string
     * 
     * @param int $projectId
     * @return string
     */
    private function _getProjectMilestonesString($projectId)
    {
        $milestones = $this->projects_model->get_milestones($projectId);
        return format_project_items(
            $milestones,
            [],
            function ($ms) {
                $parts = ["Milestone Name: " . ($ms['name'] ?? '')];

                $fields = [
                    'start_date' => 'Milestone Start Date',
                    'due_date' => 'Milestone Due Date'
                ];

                foreach ($fields as $field => $label) {
                    if (!empty($ms[$field])) {
                        $parts[] = "{$label}: {$ms[$field]}";
                    }
                }

                return implode(' | ', $parts);
            }
        );
    }

    /**
     * Get project activity as formatted string
     * 
     * @param int $projectId
     * @return string
     */
    private function _getProjectActivityString($projectId)
    {
        $limit = (int) get_option('ai_project_analyzer_data_limit');
        $activities = $this->projects_model->get_activity($projectId, $limit);

        return format_project_items(
            $activities,
            [],
            fn($act) => trim(
                ($act['fullname'] ?? '') . ' ' . ($act['description'] ?? '') .
                (!empty($act['additional_data']) ? " {$act['additional_data']}" : '')
            )
        );
    }

    /**
     * Get sanitized POST data
     * 
     * @return array
     */
    private function _getPostData()
    {
        $custom_instructions = $this->input->post('custom_instructions') ?? get_option('ai_project_analyzer_custom_instructions');

        return [
            'custom_instructions' => $custom_instructions ?? '',
            'tone' => $this->input->post('tone') ?? '',
            'language' => $this->input->post('language') ?? 'English',
        ];
    }

    /**
     * Process uploaded file and extract text
     * 
     * @param int $projectId
     * @return array|null
     */
    private function _processUploadedFile($projectId)
    {
        if (empty($_FILES['attachment']['tmp_name'])) {
            return null;
        }

        $fileName = $_FILES['attachment']['name'];
        $fileTmp = $_FILES['attachment']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file
        if (!in_array($fileExt, self::ALLOWED_FILE_TYPES)) {
            throw new Exception('File type not allowed');
        }

        try {
            $text = $this->_extractTextFromFile($fileTmp, $fileExt);
            $storedFile = $this->_storeFile($fileName, $fileTmp, $projectId);

            return [
                'file_text' => $text,
                'stored_name' => $storedFile['stored_name'],
                'original_name' => $storedFile['original_name'],
            ];

        } catch (Exception $e) {
            throw new Exception('File processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract text from file based on type
     * 
     * @param string $fileTmp
     * @param string $fileExt
     * @return string
     */
    private function _extractTextFromFile($fileTmp, $fileExt)
    {
        return match ($fileExt) {
            'pdf' => $this->_extractPdfText($fileTmp),
            'docx' => $this->_extractDocxText($fileTmp),
            'csv' => $this->_extractCsvText($fileTmp),
            'txt' => file_get_contents($fileTmp),
            default => '',
        };
    }

    /**
     * Extract text from PDF
     * 
     * @param string $fileTmp
     * @return string
     */
    private function _extractPdfText($fileTmp)
    {
        try {
            return (new \Smalot\PdfParser\Parser())->parseFile($fileTmp)->getText();
        } catch (Exception $e) {
            throw new Exception('Failed to parse PDF: ' . $e->getMessage());
        }
    }

    /**
     * Extract text from DOCX
     * 
     * @param string $fileTmp
     * @return string
     */
    private function _extractDocxText($fileTmp)
    {
        try {
            $phpWord = IOFactory::load($fileTmp, 'Word2007');
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }
            return $text;
        } catch (Exception $e) {
            throw new Exception('Failed to parse DOCX: ' . $e->getMessage());
        }
    }

    /**
     * Extract text from CSV
     * 
     * @param string $fileTmp
     * @return string
     */
    private function _extractCsvText($fileTmp)
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
            $spreadsheet = $reader->load($fileTmp);
            $sheet = $spreadsheet->getActiveSheet();

            $text = '';
            foreach ($sheet->getRowIterator() as $row) {
                $rowCells = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowCells[] = $cell->getValue();
                }
                $text .= implode(' | ', $rowCells) . "\n";
            }
            return $text;
        } catch (Exception $e) {
            throw new Exception('Failed to parse CSV: ' . $e->getMessage());
        }
    }

    /**
     * Store uploaded file
     * 
     * @param string $fileName
     * @param string $fileTmp
     * @param int $projectId
     * @return array
     */
    private function _storeFile($fileName, $fileTmp, $projectId)
    {
        $originalName = basename($fileName);
        $timestamp = time();
        $uniqueName = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);

        $projectPath = get_upload_path_by_type('project') . $projectId . '/';
        $uploadPath = 'analysis/';

        _maybe_create_upload_path($projectPath);
        _maybe_create_upload_path($projectPath . $uploadPath);

        $finalPath = $projectPath . $uploadPath . $uniqueName;

        if (!move_uploaded_file($fileTmp, $finalPath)) {
            throw new Exception('Failed to store file');
        }

        return [
            'stored_name' => $uniqueName,
            'original_name' => $originalName,
        ];
    }

    /**
     * Handle cron-based generation
     * 
     * @param int $projectId
     * @param string $hash
     * @param object $ai_prompt
     * @param string $processed_prompt
     * @param array $uploaded_file
     * @param array $post_data
     * @return void
     */
    private function _handleCronGeneration($projectId, $hash, $ai_prompt, $processed_prompt, $uploaded_file, $post_data)
    {
        $insert_data = $this->_buildInsertData($projectId, $hash, $ai_prompt, $processed_prompt, $uploaded_file, $post_data);
        $insert_data['analysis'] = 'Generating...';
        $insert_data['status'] = 'processing';

        $this->db->trans_start();

        $this->db->insert(AI_PROJECT_ANALYZER_TABLE, $insert_data);
        $this->db->insert(AI_PROJECT_ANALYZER_QUEUE_TABLE, [
            'project_id' => $projectId,
            'analysis_hash' => $hash,
            'prompt_id' => $ai_prompt->id,
            'iscronfinished' => 0,
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            throw new Exception('Failed to queue analysis');
        }

        echo json_encode(['status' => 'processing']);
    }

    /**
     * Handle direct generation
     * 
     * @param int $projectId
     * @param string $hash
     * @param object $ai_prompt
     * @param string $processed_prompt
     * @param array $uploaded_file
     * @param array $post_data
     * @return void
     */
    private function _handleDirectGeneration($projectId, $hash, $ai_prompt, $processed_prompt, $uploaded_file, $post_data)
    {
        $response = generate_ai_analysis(
            $processed_prompt,
            $post_data['custom_instructions'],
            $post_data['tone'],
            $post_data['language'],
            $uploaded_file
        );

        if (!$response || !isset($response['choices'][0]['message']['content'])) {
            throw new Exception('Invalid AI response');
        }

        $insert_data = $this->_buildInsertData($projectId, $hash, $ai_prompt, $processed_prompt, $uploaded_file, $post_data);
        $insert_data['analysis'] = $response['choices'][0]['message']['content'];
        $insert_data['tokens_used'] = ($response['usage']['prompt_tokens'] ?? 0) + ($response['usage']['completion_tokens'] ?? 0);
        $insert_data['cost_usd'] = calculate_model_cost($response['usage']['prompt_tokens'], $response['usage']['completion_tokens']);
        $insert_data['status'] = 'generated';

        $this->db->insert(AI_PROJECT_ANALYZER_TABLE, $insert_data);

        echo json_encode(['status' => 'generated']);
    }

    /**
     * Build common insert data array
     * 
     * @param int $projectId
     * @param string $hash
     * @param object $ai_prompt
     * @param string $processed_prompt
     * @param array $uploaded_file
     * @param array $post_data
     * @return array
     */
    private function _buildInsertData($projectId, $hash, $ai_prompt, $processed_prompt, $uploaded_file, $post_data)
    {
        return [
            'project_id' => $projectId,
            'hash' => $hash,
            'owner' => get_staff_user_id(),
            'prompt_name' => $ai_prompt->name,
            'ai_prompt' => nl2br($processed_prompt),
            'model' => get_option('ai_project_analyzer_api_provider_model'),
            'attachment' => $uploaded_file['stored_name'] ?? '',
            'attachment_label' => $uploaded_file['original_name'] ?? '',
            'attachment_text' => $uploaded_file['file_text'] ?? '',
            'tone' => $post_data['tone'],
            'language' => $post_data['language'],
            'custom_instructions' => $post_data['custom_instructions'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Handle errors consistently
     * 
     * @param string $message
     * @return void
     */
    private function _handleError($message)
    {
        http_response_code(500);
        echo json_encode(['error' => $message]);
    }
}