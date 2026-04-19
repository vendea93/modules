<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Aiagentchat_client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }
        $this->load->model('clients_model');
        $this->load->model('aiagentchat_model');
    }

    public function widget_assigned_chats()
    {
        $this->output->set_content_type('application/json');

        $contactId = (int)get_contact_user_id();
        $contact = $this->clients_model->get_contact($contactId);
        $customerId = $contact ? (int)$contact->userid : 0;

        if (!$contactId || !$customerId) {
            echo json_encode($this->_with_csrf(['chats' => []]));
            return;
        }

        $assignedChatIds = $this->_get_assigned_chat_ids_for_contact($customerId, $contactId);

        if (empty($assignedChatIds)) {
            echo json_encode($this->_with_csrf(['chats' => []]));
            return;
        }

        $this->db->select('id, chat_name, workflow_id, settings_json, is_enabled');
        $this->db->from(db_prefix() . 'aiagentchat_chats');
        $this->db->where_in('id', $assignedChatIds);
        $this->db->where('is_enabled', 1);

        $rows = $this->db->get()->result_array();

        $chats = array_map(function ($r) {
            return [
                'id' => (int)$r['id'],
                'chat_name' => (string)$r['chat_name'],
                'workflow_id' => (string)$r['workflow_id'],
                'settings_json' => (string)$r['settings_json'],
            ];
        }, $rows);

        echo json_encode($this->_with_csrf(['chats' => $chats]));
    }

    public function start_session($chatId = '')
    {
        $this->output->set_content_type('application/json');

        $chatId = (int)$chatId;
        if (!$chatId) {
            return $this->_json_error(400, 'Missing chat id');
        }

        $contactId = (int)get_contact_user_id();
        $contact = $this->clients_model->get_contact($contactId);
        $customerId = $contact ? (int)$contact->userid : 0;

        if (!$contactId || !$customerId) {
            return $this->_json_error(401, 'Not authenticated');
        }

        $assignedIds = $this->_get_assigned_chat_ids_for_contact($customerId, $contactId);
        if (!in_array($chatId, $assignedIds, true)) {
            return $this->_json_error(403, 'Chat not assigned to this contact');
        }

        $chat = $this->aiagentchat_model->get_chat($chatId);
        if (!$chat || (int)$chat->is_enabled !== 1 || empty($chat->workflow_id)) {
            return $this->_json_error(404, 'Chat not available');
        }

        $apiKey = trim((string)get_option('aiagentchat_openai_api_key'));
        if (!$apiKey) {
            return $this->_json_error(400, 'Missing API key');
        }

        $contactEmail = $contact && isset($contact->email) ? $contact->email : '';
        $userString = 'client:' . $customerId . ':' . $contactEmail;

        $payload = json_encode([
            'workflow' => ['id' => (string)$chat->workflow_id],
            'user' => $userString,
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

        if ($raw === false || $code >= 400) {
            $out = ['error' => 'Failed to create session', 'status' => $code, 'detail' => $err, 'body' => $raw];
            echo json_encode($this->_with_csrf($out));
            return;
        }

        $json = json_decode($raw, true);
        $out = [
            'client_secret' => $json['client_secret'] ?? null,
            'expires_at' => $json['expires_at'] ?? null,
        ];
        echo json_encode($this->_with_csrf($out));
    }

    private function _get_assigned_chat_ids_for_contact($customerId, $contactId)
    {
        $assignedIds = [];

        $groupMapRows = $this->db->select('groupid')
            ->from(db_prefix() . 'customer_groups')
            ->where('customer_id', $customerId)
            ->get()->result_array();

        $groupIds = array_map(function ($r) {
            return (int)$r['groupid'];
        }, $groupMapRows);

        $rows = $this->db->select('chat_id, rel_id, rel_type')
            ->from(db_prefix() . 'aiagentchat_chats_assignments')
            ->get()->result_array();


        foreach ($rows as $row) {
            $chatIdTxt = isset($row['chat_id']) ? (string)$row['chat_id'] : '';
            $chatId = (int)(is_numeric($chatIdTxt) ? $chatIdTxt : 0);
            if ($chatId <= 0) {
                continue;
            }

            $relType = strtolower(trim((string)$row['rel_type']));
            $relType = str_replace([' ', '-'], '_', $relType);
            $relId = trim((string)$row['rel_id']);

            $isAll = (
                $relType === 'all' ||
                $relType === 'everyone' ||
                $relType === 'public' ||
                $relType === 'all_clients' ||
                $relType === 'all_customers' ||
                $relType === 'all_contacts' ||
                $relType === 'all_clients_contacts' ||
                $relType === 'all_customers_contacts' ||
                $relType === 'client_all' ||
                $relType === 'customers_and_contacts'
            );
            if ($isAll) {
                $assignedIds[] = $chatId;
                continue;
            }

            $isCustomer = in_array($relType, ['customer', 'customers', 'client', 'clients'], true);
            if ($isCustomer && (int)$relId === (int)$customerId) {
                $assignedIds[] = $chatId;
                continue;
            }

            $isContact = in_array($relType, ['contact', 'contacts', 'client_contact', 'customer_contact'], true);
            if ($isContact && (int)$relId === (int)$contactId) {
                $assignedIds[] = $chatId;
                continue;
            }

            $isGroup = (
                strpos($relType, 'group') !== false
            );
            if ($isGroup && !empty($groupIds)) {
                $rid = (int)$relId;
                if ($rid > 0 && in_array($rid, $groupIds, true)) {
                    $assignedIds[] = $chatId;
                    continue;
                }
            }
        }

        return array_values(array_unique($assignedIds));
    }

    private function _with_csrf(array $payload)
    {
        $CI = &get_instance();
        if (method_exists($CI->security, 'get_csrf_token_name')) {
            $payload[$CI->security->get_csrf_token_name()] = $CI->security->get_csrf_hash();
        }
        return $payload;
    }

    private function _json_error($status, $message)
    {
        $this->output->set_status_header($status);
        echo json_encode($this->_with_csrf(['error' => $message]));
    }

}
