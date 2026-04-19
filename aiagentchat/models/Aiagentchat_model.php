<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Aiagentchat_model extends App_Model
{

    const REL_ADMIN_ALL = 'admin_all';
    const REL_STAFF = 'staff';
    const REL_DEPT = 'department';
    const REL_CLIENT_ALL = 'client_all';
    const REL_CUSTOMER = 'customer';
    const REL_GROUP = 'group';
    const REL_CONTACT = 'contact';

    public function __construct()
    {
        parent::__construct();
    }

    public function create_chat($data)
    {
        $this->db->insert(db_prefix() . 'aiagentchat_chats', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    public function get_chat($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'aiagentchat_chats')->row();
    }

    public function get_all_chats()
    {
        return $this->db->get(db_prefix() . 'aiagentchat_chats')->result_array();
    }

    public function update_chat($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'aiagentchat_chats', $data);

        return $this->db->affected_rows() > 0;
    }

    public function delete_chat($id)
    {
        $id = (int)$id;

        $this->db->trans_start();

        // Delete assignments first (cascade)
        $this->db->where('chat_id', $id)
            ->delete(db_prefix() . 'aiagentchat_chats_assignments');

        // Delete chat
        $this->db->where('id', $id)
            ->delete(db_prefix() . 'aiagentchat_chats');

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function changeChatStatus($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'aiagentchat_chats', [
            'is_enabled' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function get_chat_assignment_map(int $chat_id): array
    {
        $rows = $this->db->where('chat_id', $chat_id)
            ->get(db_prefix() . 'aiagentchat_chats_assignments')->result_array();

        $map = [
            'admin_all' => false,
            'client_all' => false,
            'staff_ids' => [],
            'dept_ids' => [],
            'customer_ids' => [],
            'group_ids' => [],
            'contact_ids' => [],
        ];
        foreach ($rows as $r) {
            switch ($r['rel_type']) {
                case self::REL_ADMIN_ALL:
                    $map['admin_all'] = true;
                    break;
                case self::REL_CLIENT_ALL:
                    $map['client_all'] = true;
                    break;
                case self::REL_STAFF:
                    $map['staff_ids'][] = (int)$r['rel_id'];
                    break;
                case self::REL_DEPT:
                    $map['dept_ids'][] = (int)$r['rel_id'];
                    break;
                case self::REL_CUSTOMER:
                    $map['customer_ids'][] = (int)$r['rel_id'];
                    break;
                case self::REL_GROUP:
                    $map['group_ids'][] = (int)$r['rel_id'];
                    break;
                case self::REL_CONTACT:
                    $map['contact_ids'][] = (int)$r['rel_id'];
                    break;
            }
        }
        return $map;
    }

    public function sync_chat_assignments(int $chat_id, array $payload): bool
    {
        $now = date('Y-m-d H:i:s');
        $rows = [];

        if (!empty($payload['admin_all'])) {
            $rows[] = ['chat_id' => $chat_id, 'rel_id' => 0, 'rel_type' => self::REL_ADMIN_ALL, 'created_at' => $now];
        } else {
            foreach ($payload['staff_ids'] ?? [] as $sid) {
                $rows[] = ['chat_id' => $chat_id, 'rel_id' => (int)$sid, 'rel_type' => self::REL_STAFF, 'created_at' => $now];
            }
            foreach ($payload['dept_ids'] ?? [] as $did) {
                $rows[] = ['chat_id' => $chat_id, 'rel_id' => (int)$did, 'rel_type' => self::REL_DEPT, 'created_at' => $now];
            }
        }

        if (!empty($payload['client_all'])) {
            $rows[] = ['chat_id' => $chat_id, 'rel_id' => 0, 'rel_type' => self::REL_CLIENT_ALL, 'created_at' => $now];
        } else {
            foreach ($payload['customer_ids'] ?? [] as $cid) {
                $rows[] = ['chat_id' => $chat_id, 'rel_id' => (int)$cid, 'rel_type' => self::REL_CUSTOMER, 'created_at' => $now];
            }
            foreach ($payload['group_ids'] ?? [] as $gid) {
                $rows[] = ['chat_id' => $chat_id, 'rel_id' => (int)$gid, 'rel_type' => self::REL_GROUP, 'created_at' => $now];
            }
            foreach ($payload['contact_ids'] ?? [] as $ctid) {
                $rows[] = ['chat_id' => $chat_id, 'rel_id' => (int)$ctid, 'rel_type' => self::REL_CONTACT, 'created_at' => $now];
            }
        }

        $this->db->trans_start();
        $this->db->where('chat_id', $chat_id)->delete(db_prefix() . 'aiagentchat_chats_assignments');
        if (!empty($rows)) {
            $this->db->insert_batch(db_prefix() . 'aiagentchat_chats_assignments', $rows);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function get_visible_chats_for_context(array $context): array
    {
        $this->db->where('is_enabled', 1);
        $chats = $this->db->get(db_prefix() . 'aiagentchat_chats')->result_array();
        if (!$chats) return [];

        $chatIds = array_column($chats, 'id');

        $assigns = $this->db->where_in('chat_id', $chatIds)
            ->get(db_prefix() . 'aiagentchat_chats_assignments')->result_array();

        $byChat = [];
        foreach ($assigns as $a) {
            $byChat[(int)$a['chat_id']][] = $a;
        }

        $visible = [];
        foreach ($chats as $chat) {
            $cid = (int)$chat['id'];
            $rules = $byChat[$cid] ?? [];

            if ($context['role'] === 'staff') {
                $isAll = array_filter($rules, fn($r) => $r['rel_type'] === self::REL_ADMIN_ALL);
                if ($isAll) {
                    $visible[] = $chat;
                    continue;
                }

                $staffHit = array_filter($rules, fn($r) => $r['rel_type'] === self::REL_STAFF && (int)$r['rel_id'] === (int)$context['staff_id']);
                if ($staffHit) {
                    $visible[] = $chat;
                    continue;
                }

                $deptIds = $context['department_ids'] ?? [];
                if (!empty($deptIds)) {
                    $deptHit = array_filter($rules, function ($r) use ($deptIds) {
                        return $r['rel_type'] === self::REL_DEPT && in_array((int)$r['rel_id'], $deptIds, true);
                    });
                    if ($deptHit) {
                        $visible[] = $chat;
                        continue;
                    }
                }
            }

            if ($context['role'] === 'client') {
                $isAll = array_filter($rules, fn($r) => $r['rel_type'] === self::REL_CLIENT_ALL);
                if ($isAll) {
                    $visible[] = $chat;
                    continue;
                }

                $custHit = array_filter($rules, fn($r) => $r['rel_type'] === self::REL_CUSTOMER && (int)$r['rel_id'] === (int)$context['customer_id']);
                if ($custHit) {
                    $visible[] = $chat;
                    continue;
                }

                $groupIds = $context['group_ids'] ?? [];
                if (!empty($groupIds)) {
                    $grpHit = array_filter($rules, function ($r) use ($groupIds) {
                        return $r['rel_type'] === self::REL_GROUP && in_array((int)$r['rel_id'], $groupIds, true);
                    });
                    if ($grpHit) {
                        $visible[] = $chat;
                        continue;
                    }
                }

                $contactHit = array_filter($rules, fn($r) => $r['rel_type'] === self::REL_CONTACT && (int)$r['rel_id'] === (int)$context['contact_id']);
                if ($contactHit) {
                    $visible[] = $chat;
                    continue;
                }
            }
        }

        return $visible;
    }
}
