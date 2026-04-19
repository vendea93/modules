<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_name_patterns_model extends CI_Model
{
    private $project_name_patterns;
    private $patterns_dirty = false;

    public function __construct()
    {
        parent::__construct();
        $this->project_name_patterns = json_decode(get_option(POLYUTILITIES_PROJECT_NAME_PATTERNS), true) ?: [];
        $this->normalize_patterns();
    }

    private function normalize_patterns()
    {
        if (!is_array($this->project_name_patterns)) {
            $this->project_name_patterns = [];
            return;
        }

        foreach ($this->project_name_patterns as $index => &$pattern) {
            if (!isset($pattern['order']) || !is_numeric($pattern['order'])) {
                $pattern['order'] = $index + 1;
                $this->patterns_dirty = true;
            } else {
                $pattern['order'] = (int) $pattern['order'];
            }

            if (!isset($pattern['timestamp']) || !is_numeric($pattern['timestamp'])) {
                $pattern['timestamp'] = time();
                $this->patterns_dirty = true;
            } else {
                $pattern['timestamp'] = (int) $pattern['timestamp'];
            }
        }
        unset($pattern);

        $this->sort_patterns();

        if ($this->patterns_dirty) {
            update_option(POLYUTILITIES_PROJECT_NAME_PATTERNS, json_encode($this->project_name_patterns));
            $this->patterns_dirty = false;
        }
    }

    private function get_next_order_value()
    {
        if (empty($this->project_name_patterns)) {
            return 1;
        }

        $maxOrder = 0;

        foreach ($this->project_name_patterns as $pattern) {
            if (!isset($pattern['order'])) {
                $currentOrder = 0;
            } else {
                $currentOrder = is_numeric($pattern['order']) ? (int) $pattern['order'] : 0;
            }

            if ($currentOrder > $maxOrder) {
                $maxOrder = $currentOrder;
            }
        }

        return $maxOrder + 1;
    }

    private function sort_patterns()
    {
        if (empty($this->project_name_patterns)) {
            return;
        }

        usort($this->project_name_patterns, function ($a, $b) {
            $orderA = isset($a['order']) && is_numeric($a['order']) ? (int) $a['order'] : 0;
            $orderB = isset($b['order']) && is_numeric($b['order']) ? (int) $b['order'] : 0;
            return $orderA === $orderB ? 0 : ($orderA > $orderB ? -1 : 1);
        });
    }

    public function add($name, $note, $active, $created_by, $updated_by)
    {
        if (empty($name) || empty($created_by)) {
            return false;
        }

        $new_template = [
            'id' => uniqid(),
            'name' => $name,
            'note' => $note,
            'active' => $active,
            'order' => $this->get_next_order_value(),
            'timestamp' => time(),
            'created_by' => $created_by,
            'updated_by' => $updated_by,
        ];

        $this->project_name_patterns[] = $new_template;
        $this->sort_patterns();
        return update_option(POLYUTILITIES_PROJECT_NAME_PATTERNS, json_encode($this->project_name_patterns));
    }

    public function is_existed($name)
    {
        foreach ($this->project_name_patterns as $template) {
            if (strtolower($template['name']) === strtolower($name)) {
                return true;
            }
        }
        return false;
    }

    public function delete_project_name_pattern($id)
    {
        foreach ($this->project_name_patterns as $key => $template) {
            if ($template['id'] === $id) {
                unset($this->project_name_patterns[$key]);
                $this->project_name_patterns = array_values($this->project_name_patterns);
                $this->sort_patterns();
                return update_option(POLYUTILITIES_PROJECT_NAME_PATTERNS, json_encode($this->project_name_patterns));
            }
        }
        return false;
    }

    public function update($id, $name = null, $note = null, $active = null, $updated_by = null, $order = null)
    {
        foreach ($this->project_name_patterns as &$pattern) {
            if ($pattern['id'] === $id) {
                if ($name !== null) {
                    $pattern['name'] = $name;
                }
                if ($note !== null) {
                    $pattern['note'] = $note;
                }
                if ($active !== null) {
                    $pattern['active'] = $active;
                }
                if ($updated_by !== null) {
                    $pattern['updated_by'] = $updated_by;
                }
                if ($order !== null) {
                    $pattern['order'] = (int) $order;
                } elseif (!isset($pattern['order']) || !is_numeric($pattern['order'])) {
                    $pattern['order'] = 1;
                } else {
                    $pattern['order'] = (int) $pattern['order'];
                }

                $pattern['timestamp'] = time();

                $this->sort_patterns();

                return update_option(POLYUTILITIES_PROJECT_NAME_PATTERNS, json_encode($this->project_name_patterns));
            }
        }
        return false;
    }

    public function update_orders(array $orderedItems)
    {
        if (empty($orderedItems)) {
            return false;
        }

        $orderMap = [];
        $fallbackPosition = 1;

        foreach ($orderedItems as $entry) {
            if (is_array($entry)) {
                $id = isset($entry['id']) ? $entry['id'] : null;
                $position = isset($entry['order']) ? (int) $entry['order'] : $fallbackPosition;
            } else {
                $id = $entry;
                $position = $fallbackPosition;
            }

            if (!$id) {
                $fallbackPosition++;
                continue;
            }

            $orderMap[$id] = max(1, $position);
            $fallbackPosition++;
        }

        if (empty($orderMap)) {
            return false;
        }

        $updated = false;

        foreach ($this->project_name_patterns as &$pattern) {
            if (isset($orderMap[$pattern['id']])) {
                $pattern['order'] = $orderMap[$pattern['id']];
                $updated = true;
            } elseif (!isset($pattern['order']) || !is_numeric($pattern['order'])) {
                $pattern['order'] = 1;
                $updated = true;
            } else {
                $pattern['order'] = (int) $pattern['order'];
            }
        }
        unset($pattern);

        if (!$updated) {
            return false;
        }

        $this->sort_patterns();

        return update_option(POLYUTILITIES_PROJECT_NAME_PATTERNS, json_encode($this->project_name_patterns));
    }

    public function get_all($active = null)
    {
        if (!is_array($this->project_name_patterns)) {
            $this->project_name_patterns = [];
        }

        $this->sort_patterns();

        if ($active !== null) {
            return array_filter($this->project_name_patterns, function ($item) use ($active) {
                return (bool)$item['active'] === (bool)$active;
            });
        }

        return $this->project_name_patterns;
    }

}
