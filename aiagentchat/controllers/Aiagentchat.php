<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Aiagentchat extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('aiagentchat_model');
        hooks()->do_action('aiagentchat_init');
    }

    public function index()
    {
        if (!has_permission('aiagentchat', '', 'view')) {
            access_denied('aiagentchat');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('aiagentchat', 'admin/table'));
        }

        $data['title'] = _l('aiagentchat');
        $this->load->view('admin/manage', $data);
    }


    public function create($id = '')
    {
        $isEditing = $id !== '';

        if ($isEditing) {
            if (!has_permission('aiagentchat', '', 'edit')) {
                access_denied('aiagentchat');
            }
        } else {
            if (!has_permission('aiagentchat', '', 'create')) {
                access_denied('aiagentchat');
            }
        }

        if ($this->input->post()) {

            $postRaw = $this->input->post(null, false);

            $chatData = [
                'chat_name' => trim($postRaw['chat_name'] ?? ''),
                'workflow_id' => trim($postRaw['workflow_id'] ?? ''),
                'settings_json' => trim($postRaw['settings_json'] ?? ''),
                'is_enabled' => isset($postRaw['is_enabled']) ? 1 : 0,
            ];

            if ($chatData['settings_json'] !== '') {
                json_decode($chatData['settings_json']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    set_alert('warning', _l('aiagentchat_invalid_json_field', _l('aiagentchat_settings_json_preview')));
                    $viewData['title'] = _l('aiagentchat');
                    $viewData['chat'] = (object)($chatData + ['id' => $id]);
                    return $this->load->view('admin/create', $viewData);
                }
            }

            if ($isEditing) {
                $updateOk = $this->aiagentchat_model->update_chat((int)$id, $chatData);
                if ($updateOk) {
                    set_alert('success', _l('aiagentchat_updated_successfully'));
                } else {
                    set_alert('warning', _l('aiagentchat_not_updated_successfully'));
                }
            } else {
                $chatData['created_at'] = date('Y-m-d H:i:s');
                $insertId = $this->aiagentchat_model->create_chat($chatData);
                if ($insertId) {
                    set_alert('success', _l('aiagentchat_created_successfully'));
                } else {
                    set_alert('warning', _l('aiagentchat_not_created_successfully'));
                }
            }

            redirect(admin_url('aiagentchat'));
        }

        $data['title'] = _l('aiagentchat');
        if ($isEditing) {
            $data['chat'] = $this->aiagentchat_model->get_chat((int)$id);
            if (!$data['chat']) {
                set_alert('warning', _l('not_found', _l('aiagentchat')));
                redirect(admin_url('aiagentchat'));
            }
        }

        $this->load->view('admin/create', $data);
    }

    public function delete($id = '')
    {
        if (!has_permission('aiagentchat', '', 'delete')) {
            access_denied('aiagentchat');
        }

        if (!$id) {
            redirect(admin_url('aiagentchat'));
        }

        $deleteOk = $this->aiagentchat_model->delete_chat((int)$id);

        if (is_array($deleteOk) && isset($deleteOk['referenced']) && $deleteOk['referenced'] === true) {
            set_alert('warning', _l('problem_deleting', _l('aiagentchat')) . ' - ' . _l('is_referenced'));
        } elseif ($deleteOk === true) {
            set_alert('success', _l('deleted', _l('aiagentchat')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('aiagentchat')));
        }

        redirect(admin_url('aiagentchat'));
    }

    public function settings()
    {
        if (!is_admin()) {
            access_denied('aiagentchat');
        }

        if ($this->input->post()) {

            $this->load->model('payment_modes_model');
            $this->load->model('settings_model');

            $postFiltered = $this->input->post();
            $postRaw = $this->input->post(null, false);

            $settingsFiltered = isset($postFiltered['settings']) ? $postFiltered['settings'] : [];
            $settingsRaw = isset($postRaw['settings']) ? $postRaw['settings'] : [];

            $openAiApiKey = isset($settingsRaw['aiagentchat_openai_api_key'])
                ? trim($settingsRaw['aiagentchat_openai_api_key'])
                : '';

            $iconClassAdminRaw = isset($settingsRaw['aiagentchat_bubble_chat_icon_admin'])
                ? trim($settingsRaw['aiagentchat_bubble_chat_icon_admin'])
                : '';

            $iconClassClientRaw = isset($settingsRaw['aiagentchat_bubble_chat_icon_client'])
                ? trim($settingsRaw['aiagentchat_bubble_chat_icon_client'])
                : '';

            $cssJsonAdminRaw = isset($settingsRaw['aiagentchat_bubble_chat_css_json_admin'])
                ? trim($settingsRaw['aiagentchat_bubble_chat_css_json_admin'])
                : '';

            $cssJsonClientRaw = isset($settingsRaw['aiagentchat_bubble_chat_css_json_client'])
                ? trim($settingsRaw['aiagentchat_bubble_chat_css_json_client'])
                : '';

            $iconClassAdmin = preg_replace('/[^a-z0-9\-\_\s]/i', '', $iconClassAdminRaw);
            $iconClassClient = preg_replace('/[^a-z0-9\-\_\s]/i', '', $iconClassClientRaw);

            if ($iconClassAdmin === '') {
                $iconClassAdmin = 'fa fa-commenting';
            }
            if ($iconClassClient === '') {
                $iconClassClient = 'fa fa-commenting';
            }

            $invalidFieldKey = null;

            if ($cssJsonAdminRaw !== '') {
                json_decode($cssJsonAdminRaw);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $invalidFieldKey = 'aiagentchat_bubble_chat_css_json_admin';
                }
            }

            if ($invalidFieldKey === null && $cssJsonClientRaw !== '') {
                json_decode($cssJsonClientRaw);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $invalidFieldKey = 'aiagentchat_bubble_chat_css_json_client';
                }
            }

            if ($invalidFieldKey !== null) {
                $data['title'] = _l('aiagentchat') . ' - ' . _l('settings');
                $data['posted_settings'] = $settingsRaw;

                set_alert('warning', _l('aiagentchat_invalid_json_field', _l($invalidFieldKey)));

                return $this->load->view('settings', $data);
            }

            $settingsFiltered['aiagentchat_openai_api_key'] = $openAiApiKey;
            $settingsFiltered['aiagentchat_bubble_chat_icon_admin'] = $iconClassAdmin;
            $settingsFiltered['aiagentchat_bubble_chat_icon_client'] = $iconClassClient;
            $settingsFiltered['aiagentchat_bubble_chat_css_json_admin'] = $cssJsonAdminRaw;
            $settingsFiltered['aiagentchat_bubble_chat_css_json_client'] = $cssJsonClientRaw;

            $updatedCount = $this->settings_model->update(['settings' => $settingsFiltered]);

            if ($updatedCount > 0) {
                set_alert('success', _l('aiagentchat_settings_saved'));
            } else {
                set_alert('success', _l('aiagentchat_settings_saved'));
            }

            redirect(admin_url(AIAGENTCHAT_MODULE_NAME . '/settings'), 'refresh');
        }

        $data['title'] = _l('aiagentchat') . ' - ' . _l('settings');
        $this->load->view('admin/settings', $data);
    }

    public function update_chat_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->aiagentchat_model->changeChatStatus($id, $status);
        }
    }

    public function assign($chat_id = '')
    {
        if (!has_permission('aiagentchat', '', 'assign_chat')) {
            access_denied('aiagentchat');
        }
        if (!$chat_id) {
            redirect(admin_url('aiagentchat'));
        }

        $this->load->model('aiagentchat_model');
        $chat = $this->aiagentchat_model->get_chat($chat_id);

        if (!$chat) {
            set_alert('warning', _l('aiagentchat_not_found'));
            redirect(admin_url('aiagentchat'));
        }

        if ($this->input->post()) {
            if (!has_permission('aiagentchat', '', 'edit')) {
                access_denied('aiagentchat');
            }

            $adminAll = $this->input->post('admin_all_staff') === '1';
            $clientAll = $this->input->post('client_all_customers') === '1';

            $payload = [
                'admin_all' => $adminAll,
                'staff_ids' => $adminAll ? [] : array_filter(array_map('intval', (array)$this->input->post('admin_staff_ids', true))),
                'dept_ids' => $adminAll ? [] : array_filter(array_map('intval', (array)$this->input->post('admin_department_ids', true))),
                'client_all' => $clientAll,
                'customer_ids' => $clientAll ? [] : array_filter(array_map('intval', (array)$this->input->post('client_customer_ids', true))),
                'group_ids' => $clientAll ? [] : array_filter(array_map('intval', (array)$this->input->post('client_group_ids', true))),
                'contact_ids' => $clientAll ? [] : array_filter(array_map('intval', (array)$this->input->post('client_contact_ids', true))),
            ];

            $ok = $this->aiagentchat_model->sync_chat_assignments((int)$chat_id, $payload);
            set_alert($ok ? 'success' : 'warning', $ok ? _l('aiagentchat_assignments_saved') : _l('problem_setting', _l('aiagentchat_assignments')));

            redirect(admin_url('aiagentchat/assign/' . (int)$chat_id));
        }

        $data['chat'] = $chat;

        $data['staff_options'] = $this->db->select('staffid as id, CONCAT(firstname," ",lastname) as name')
            ->from(db_prefix() . 'staff')
            ->where('active', 1)
            ->order_by('firstname', 'asc')
            ->get()
            ->result_array();

        $data['department_options'] = $this->db->select('departmentid as id, name')
            ->from(db_prefix() . 'departments')->order_by('name', 'asc')->get()->result_array();

        $data['customer_options'] = $this->db->select('userid as id, company as name')
            ->from(db_prefix() . 'clients')
            ->where('active', 1)
            ->order_by('company', 'asc')
            ->get()
            ->result_array();

        $data['group_options'] = $this->db->select('id, name')
            ->from(db_prefix() . 'customers_groups')->order_by('name', 'asc')->get()->result_array();

        $data['contact_options'] = $this->db->select('id, CONCAT(firstname," ",lastname," (",email,")") as name')
            ->from(db_prefix() . 'contacts')
            ->where('active', 1)
            ->order_by('firstname', 'asc')
            ->get()
            ->result_array();

        $data['preselect'] = $this->aiagentchat_model->get_chat_assignment_map((int)$chat_id);
        $data['title'] = _l('aiagentchat_assign_title', $chat->chat_name);

        $this->load->view('admin/assign_chat', $data);
    }


    public function widget_assigned_chats()
    {
        if (!is_staff_logged_in()) {
            show_404();
        }
        $this->load->model('aiagentchat_model');

        $staffId = get_staff_user_id();

        $deptRows = $this->db->select('departmentid')->from(db_prefix() . 'staff_departments')
            ->where('staffid', (int)$staffId)->get()->result_array();
        $departmentIds = array_map(fn($r) => (int)$r['departmentid'], $deptRows);

        $visible = $this->aiagentchat_model->get_visible_chats_for_context([
            'role' => 'staff',
            'staff_id' => (int)$staffId,
            'department_ids' => $departmentIds,
        ]);
        $out = ['chats' => $visible];

        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        $out[$csrf_name] = $csrf_hash;

        header('Content-Type: application/json');
        echo json_encode($out);
    }

    public function start_session($chat_id = '')
    {
        if (!is_staff_logged_in()) {
            show_404();
        }
        $chat_id = (int)$chat_id;
        $this->_create_session_from_chat($chat_id, 'staff');
    }

    public function refresh_session($chat_id = '')
    {
        return $this->start_session($chat_id);
    }

    private function _create_session_from_chat(int $chat_id, string $who)
    {
        header('Content-Type: application/json');

        $chat = $this->aiagentchat_model->get_chat($chat_id);
        if (!$chat || empty($chat->workflow_id) || (int)$chat->is_enabled !== 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid chat or not enabled']);
            return;
        }

        if ($who === 'staff') {
            $staffId = get_staff_user_id();

            $deptRows = $this->db->select('departmentid')->from(db_prefix() . 'staff_departments')
                ->where('staffid', (int)$staffId)->get()->result_array();
            $departmentIds = array_map(fn($r) => (int)$r['departmentid'], $deptRows);

            $visible = $this->aiagentchat_model->get_visible_chats_for_context([
                'role' => 'staff',
                'staff_id' => (int)$staffId,
                'department_ids' => $departmentIds,
            ]);

            $ids = array_map(fn($r) => (int)$r['id'], $visible);
            if (!in_array($chat_id, $ids, true)) {

                http_response_code(403);

                echo json_encode(['error' => 'Not allowed']);
                return;
            }
        } else {

            http_response_code(400);

            echo json_encode(['error' => 'Wrong context']);
            return;
        }

        $apiKey = trim((string)get_option('aiagentchat_openai_api_key'));

        if (!$apiKey) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing API key']);
            return;
        }

        $payload = json_encode([
            'workflow' => ['id' => (string)$chat->workflow_id],
            'user' => 'staff:' . (int)get_staff_user_id(),
        ]);

        $ch = curl_init('https://api.openai.com/v1/chatkit/sessions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'OpenAI-Beta: chatkit_beta=v1',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);
        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();

        if ($raw === false || $code >= 400) {
            http_response_code(500);

            $out = ['error' => 'Failed to create session', 'status' => $code, 'detail' => $err, 'body' => $raw];
            $out[$csrf_name] = $csrf_hash;

            echo json_encode($out);
            return;
        }

        $json = json_decode($raw, true);

        $out = [
            'client_secret' => $json['client_secret'] ?? null,
            'expires_at' => $json['expires_at'] ?? null,
        ];

        $out[$csrf_name] = $csrf_hash;

        echo json_encode($out);
    }

}
