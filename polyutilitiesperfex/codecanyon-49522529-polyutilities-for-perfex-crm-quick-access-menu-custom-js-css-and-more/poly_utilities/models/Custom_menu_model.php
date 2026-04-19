<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Custom Menu Model
 * Handles CRUD operations for custom menu items stored in database
 */
class Custom_menu_model extends App_Model
{
    private $table_menus;
    
    public function __construct()
    {
        parent::__construct();
        $this->table_menus = db_prefix() . 'poly_utilities_custom_menus';

        $this->ensure_table_schema();
    }
    
    /**
     * Get all menu items for a specific menu type
     * 
     * @param string $menu_type sidebar|setup|clients
     * @param bool $include_disabled Include disabled items
     * @param bool $load_permissions Load permission data
     * @return array Hierarchical menu structure
     */
    public function get_menus($menu_type, $include_disabled = true, $load_permissions = true)
    {
        // Check if table exists (prevent errors during deactivation)
        if (!$this->db->table_exists($this->table_menus)) {
            return [];
        }
        
        $this->db->select('*');
        $this->db->from($this->table_menus);
        $this->db->where('menu_type', $menu_type);
        
        if (!$include_disabled) {
            $this->db->where('disabled', 0);
        }
        
        $this->db->order_by('position', 'ASC');
        $this->db->order_by('level', 'ASC');
        
        $menus = $this->db->get()->result_array();
        
        // Parse JSON fields for each menu item
        if (!empty($menus)) {
            foreach ($menus as &$menu) {
                // Parse roles JSON
                if (isset($menu['roles'])) {
                    $roles_before = $menu['roles'];
                    $menu['roles'] = is_string($menu['roles']) ? json_decode($menu['roles'], true) : $menu['roles'];
                    if (!is_array($menu['roles'])) {
                        $menu['roles'] = [];
                    }
                } else {
                    $menu['roles'] = [];
                }
                
                // Parse users JSON
                if (isset($menu['users'])) {
                    $menu['users'] = is_string($menu['users']) ? json_decode($menu['users'], true) : $menu['users'];
                    if (!is_array($menu['users'])) {
                        $menu['users'] = [];
                    }
                } else {
                    $menu['users'] = [];
                }
                
                // Parse clients JSON
                if (isset($menu['clients'])) {
                    $menu['clients'] = is_string($menu['clients']) ? json_decode($menu['clients'], true) : $menu['clients'];
                    if (!is_array($menu['clients'])) {
                        $menu['clients'] = [];
                    }
                } else {
                    $menu['clients'] = [];
                }
                
                // Load permissions if requested (for backward compatibility)
                if ($load_permissions) {
                    $menu['permissions'] = $this->get_menu_permissions($menu['id']);
                }
            }
            unset($menu);
        }
        
        // Build hierarchical structure
        return $this->build_tree($menus);
    }
    
    /**
     * Get a single menu item by ID
     * 
     * @param int $id Menu ID
     * @param bool $load_permissions Load permission data
     * @return array|null Menu item data
     */
    public function get($id, $load_permissions = true)
    {
        $menu = $this->db->where('id', $id)
                         ->get($this->table_menus)
                         ->row_array();
        
        if ($menu && $load_permissions) {
            $menu['permissions'] = $this->get_menu_permissions($id);
        }
        
        return $menu;
    }
    
    /**
     * Get menu item by slug
     * 
     * @param string $slug Menu slug
     * @param string $menu_type Menu type
     * @return array|null Menu item data
     */
    public function get_by_slug($slug, $menu_type = null)
    {
        $this->db->where('slug', $slug);
        
        if ($menu_type) {
            $this->db->where('menu_type', $menu_type);
        }
        
        return $this->db->get($this->table_menus)->row_array();
    }
    
    /**
     * Add a new menu item
     * 
     * @param array $data Menu data
     * @param array $permissions Permission data ['roles' => [], 'users' => [], 'clients' => []]
     * @return int|false Insert ID or false on failure
     */
    public function add($data, $permissions = [])
    {
        $this->ensure_table_schema();

        $this->db->trans_start();
        
        // Prepare data
        $insert_data = $this->prepare_menu_data($data);

        // Calculate position if not provided
        if (!isset($insert_data['position']) || $insert_data['position'] === 0) {
            if (isset($insert_data['parent_slug']) && ($insert_data['parent_slug'] === 'root' || $insert_data['parent_slug'] === null || $insert_data['parent_slug'] === '')) {
                $insert_data['position'] = 0;
            } else {
                $insert_data['position'] = $this->get_next_position(
                    $insert_data['menu_type'],
                    $insert_data['parent_id'] ?? null
                );
            }
        }
        
        // Calculate level based on parent
        if (!isset($insert_data['level'])) {
            $insert_data['level'] = $this->calculate_level($insert_data['parent_id'] ?? null);
        }
        
        //  Sync parent_slug with parent_id
        $insert_data['parent_slug'] = $this->get_parent_slug_from_parent_id($insert_data['parent_id'] ?? null);
        
        // Insert menu item
        $this->db->insert($this->table_menus, $insert_data);
        $menu_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $this->db->trans_status() ? $menu_id : false;
    }
    
    /**
     * Update a menu item
     * 
     * @param int $id Menu ID
     * @param array $data Menu data
     * @param array $permissions Permission data (optional)
     * @return bool Success status
     */
    public function update($id, $data, $permissions = null)
    {
        $this->ensure_table_schema();

        $this->db->trans_start();
        
        // Prepare data
        $update_data = $this->prepare_menu_data($data, false);
        
        // Recalculate level if parent changed or if parent_id is in data (even if null)
        if (array_key_exists('parent_id', $update_data)) {
            $update_data['level'] = $this->calculate_level($update_data['parent_id']);
            
            //  Sync parent_slug with parent_id
            $update_data['parent_slug'] = $this->get_parent_slug_from_parent_id($update_data['parent_id']);
        }
        
        // Update menu item
        $this->db->where('id', $id)
                 ->update($this->table_menus, $update_data);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    /**
     * Delete a menu item and its children
     * 
     * @param int $id Menu ID
     * @return bool Success status
     */
    public function delete($id)
    {
        $this->db->trans_start();
        
        // Foreign key cascade will handle children and permissions
        $this->db->where('id', $id)
                 ->delete($this->table_menus);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    /**
     * Bulk delete menu items
     * 
     * @param array $ids Array of menu IDs
     * @return bool Success status
     */
    public function bulk_delete($ids)
    {
        if (empty($ids)) {
            return false;
        }
        
        $this->db->trans_start();
        
        $this->db->where_in('id', $ids)
                 ->delete($this->table_menus);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    /**
     * Update positions of multiple menu items
     * 
     * @param array $positions Array of ['id' => position, ...]
     * @param array $parent_changes Array of ['id' => parent_id, ...]
     * @return bool Success status
     */
    public function update_positions($positions, $parent_changes = [])
    {
        $this->db->trans_start();
        
        // Cache current parent_ids before updating
        $menu_parents = [];
        $menu_ids = array_keys($positions);
        
        if (!empty($menu_ids)) {
            $menus = $this->db->select('id, parent_id')
                              ->where_in('id', $menu_ids)
                              ->get($this->table_menus)
                              ->result_array();
            
            foreach ($menus as $menu) {
                $menu_parents[$menu['id']] = $menu['parent_id'];
            }
        }
        
        // First pass: Update parent_id, parent_slug and level for items that changed parent
        foreach ($parent_changes as $id => $new_parent_id) {
            $new_parent_slug = $this->get_parent_slug_from_parent_id($new_parent_id);
            
            $this->db->where('id', $id)
                     ->update($this->table_menus, [
                         'parent_id' => $new_parent_id,
                         'parent_slug' => $new_parent_slug,
                         'level' => $this->calculate_level($new_parent_id)
                     ]);
            
            // Update cached parent_id
            $menu_parents[$id] = $new_parent_id;
        }
        
        // Second pass: Group positions by parent_id
        // Use cached parent_id (which includes updates from parent_changes)
        $positions_by_parent = [];
        
        foreach ($positions as $id => $position) {
            $parent_id = isset($menu_parents[$id]) ? $menu_parents[$id] : null;
            $parent_key = $parent_id === null ? 'root' : $parent_id;
            
            if (!isset($positions_by_parent[$parent_key])) {
                $positions_by_parent[$parent_key] = [];
            }
            $positions_by_parent[$parent_key][$id] = $position;
        }
        
        // Third pass: Update positions within each parent context
        foreach ($positions_by_parent as $parent_key => $items) {
            // Sort by position to maintain order from frontend
            asort($items);
            $ordered_position = 0;
            
            foreach ($items as $id => $original_position) {
                $this->db->where('id', $id)
                         ->update($this->table_menus, ['position' => $ordered_position]);
                $ordered_position++;
            }
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    /**
     * Get children of a menu item
     * 
     * @param int $parent_id Parent menu ID
     * @param bool $recursive Get all descendants
     * @return array Children menu items
     */
    public function get_children($parent_id, $recursive = false)
    {
        $children = $this->db->where('parent_id', $parent_id)
                             ->order_by('position', 'ASC')
                             ->get($this->table_menus)
                             ->result_array();
        
        if ($recursive && !empty($children)) {
            foreach ($children as &$child) {
                $child['children'] = $this->get_children($child['id'], true);
            }
            unset($child);
        }
        
        return $children;
    }
    
    /**
     * Clone a menu item
     * 
     * @param int $id Menu ID to clone
     * @param bool $clone_children Also clone children
     * @return int|false New menu ID or false on failure
     */
    public function clone_menu($id, $clone_children = true)
    {
        $menu = $this->get($id, true);
        
        if (!$menu) {
            return false;
        }
        
        $this->db->trans_start();
        
        // Store original position and parent_id to insert clone right below
        $original_position = $menu['position'];
        $original_parent_id = $menu['parent_id'] ?? null;
        $menu_type = $menu['menu_type'];
        
        // Shift positions of menu items after the original (same parent level)
        // This makes room for the cloned item
        $this->db->where('menu_type', $menu_type);
        if ($original_parent_id === null) {
            $this->db->where('parent_id IS NULL', null, false);
        } else {
            $this->db->where('parent_id', $original_parent_id);
        }
        $this->db->where('position >', $original_position);
        $this->db->set('position', 'position + 1', false);
        $this->db->update($this->table_menus);
        
        // Prepare new menu data
        unset($menu['id']);
        $menu['slug'] = $menu['slug'] . '_' . uniqid();
        $menu['name'] = _l($menu['name']) . ' (Copy)';
        $menu['position'] = $original_position + 1; // Insert right below original
        $menu['is_custom'] = 1; // Force cloned menu to be custom
        $menu['created_at'] = date('Y-m-d H:i:s');
        $menu['updated_at'] = date('Y-m-d H:i:s');
        
        $permissions = $menu['permissions'] ?? [];
        unset($menu['permissions']);
        
        // Insert cloned menu with specific position
        $this->db->insert($this->table_menus, $menu);
        $new_id = $this->db->insert_id();
        
        // Clone children if requested (recursive - clone entire structure)
        if ($clone_children && $new_id) {
            $this->clone_children_recursive($id, $new_id);
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status() ? $new_id : false;
    }
    
    /**
     * Recursively clone children and nested children
     * 
     * @param int $original_parent_id Original parent ID
     * @param int $new_parent_id New parent ID for cloned items
     * @return void
     */
    private function clone_children_recursive($original_parent_id, $new_parent_id)
    {
        // Get direct children of original parent
        $children = $this->get_children($original_parent_id, false);
        
        foreach ($children as $child) {
            // Store original child ID before modifying
            $original_child_id = $child['id'];
            
            // Get permissions for this child
            $child_permissions = $this->get_menu_permissions($original_child_id);
            
            // Prepare child data for cloning
            unset($child['id']);
            $child['parent_id'] = $new_parent_id;
            $child['slug'] = $child['slug'] . '_' . uniqid();
            $child['name'] = _l($child['name']) . ' (Copy)';
            $child['is_custom'] = 1; // Force all cloned items to be custom
            $child['created_at'] = date('Y-m-d H:i:s');
            $child['updated_at'] = date('Y-m-d H:i:s');
            
            // Insert cloned child
            $this->db->insert($this->table_menus, $child);
            $new_child_id = $this->db->insert_id();
            
            // Recursively clone children of this child
            if ($new_child_id) {
                $this->clone_children_recursive($original_child_id, $new_child_id);
            }
        }
    }
    
    /**
     * Reset all menus for a menu type
     * 
     * @param string $menu_type Menu type to reset
     * @param bool $only_custom Only delete custom menus
     * @return bool Success status
     */
    public function reset_menus($menu_type, $only_custom = true)
    {
        $this->db->trans_start();
        
        // Get system menus to retrieve default positions
        $CI = &get_instance();
        $system_menus = [];
        
        switch ($menu_type) {
            case 'sidebar':
                $system_menus = $CI->app_menu->get_sidebar_menu_items();
                break;
            case 'setup':
                $system_menus = $CI->app_menu->get_setup_menu_items();
                break;
            case 'clients':
                $system_menus = $CI->app_menu->get_theme_items();
                break;
        }
        
        // Build a map of slug => default position
        $default_positions = [];
        $this->build_position_map($system_menus, $default_positions);
        
        // Reset custom menu items: set parent_id to null
        if ($only_custom) {
            $this->db->where('menu_type', $menu_type);
            $this->db->where('is_custom', 1);
            $this->db->update($this->table_menus, ['parent_id' => null]);
        }
        
        // Reset system menu items: restore default positions
        foreach ($default_positions as $slug => $position) {
            $this->db->where('menu_type', $menu_type);
            $this->db->where('slug', $slug);
            $this->db->where('is_custom', 0); // Only system menus
            $this->db->update($this->table_menus, ['position' => $position]);
        }
        
        //  Also delete old options (JSON storage) for this menu type
        $this->delete_old_options_for_menu_type($menu_type);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    /**
     * Build position map from menu array recursively
     * 
     * @param array $menus Menu items array
     * @param array &$position_map Reference to position map
     * @return void
     */
    private function build_position_map($menus, &$position_map)
    {
        foreach ($menus as $slug => $menu) {
            if (is_array($menu) && isset($menu['position'])) {
                $position_map[$slug] = $menu['position'];
            }
            
            // Recursively process children
            if (isset($menu['children']) && is_array($menu['children'])) {
                $this->build_position_map($menu['children'], $position_map);
            }
        }
    }
    
    /**
     * Delete old options (JSON storage) for a specific menu type
     * Called when resetting menus to clean up obsolete data
     * 
     * @param string $menu_type Menu type: sidebar, setup, clients
     * @return void
     */
    private function delete_old_options_for_menu_type($menu_type)
    {
        // Map menu types to their old option keys
        $option_keys_map = [
            'sidebar' => [
                'poly_utilities_global_menu_sidebar_custom',
                'poly_utilities_global_menu_sidebar_custom_active'
            ],
            'setup' => [
                'poly_utilities_global_menu_setup_custom',
                'poly_utilities_global_menu_setup_custom_active'
            ],
            'clients' => [
                'poly_utilities_global_menu_clients_custom',
                'poly_utilities_global_menu_clients_custom_active'
            ]
        ];
        
        // Get option keys for this menu type
        $option_keys = $option_keys_map[$menu_type] ?? [];
        
        if (empty($option_keys)) {
            return;
        }
        
        // Delete each option
        foreach ($option_keys as $option_name) {
            try {
                $this->db->where('name', $option_name)
                         ->delete(db_prefix() . 'options');
            } catch (Exception $e) {
                // Log error but don't fail the transaction
            }
        }
    }
    
    /**
     * Search menus by name or slug
     * 
     * @param string $keyword Search keyword
     * @param string $menu_type Menu type filter
     * @return array Matching menu items
     */
    public function search($keyword, $menu_type = null)
    {
        $this->db->select('*')
                 ->from($this->table_menus);
        
        if ($menu_type) {
            $this->db->where('menu_type', $menu_type);
        }
        
        $this->db->group_start();
        $this->db->like('name', $keyword);
        $this->db->or_like('slug', $keyword);
        $this->db->or_like('href', $keyword);
        $this->db->group_end();
        
        $this->db->order_by('menu_type', 'ASC');
        $this->db->order_by('position', 'ASC');
        
        return $this->db->get()->result_array();
    }
    
    /**
     * Get menu statistics
     * 
     * @return array Statistics data
     */
    public function get_statistics()
    {
        $stats = [];
        
        // Total menus by type
        $result = $this->db->select('menu_type, COUNT(*) as count')
                           ->from($this->table_menus)
                           ->group_by('menu_type')
                           ->get()
                           ->result_array();
        
        foreach ($result as $row) {
            $stats[$row['menu_type']] = (int)$row['count'];
        }
        
        // Custom vs system menus
        $custom_count = $this->db->where('is_custom', 1)
                                 ->count_all_results($this->table_menus);
        $stats['custom_menus'] = $custom_count;
        
        // Disabled menus
        $disabled_count = $this->db->where('disabled', 1)
                                   ->count_all_results($this->table_menus);
        $stats['disabled_menus'] = $disabled_count;
        
        return $stats;
    }
    
    // =============== PRIVATE HELPER METHODS ===============
    
    /**
     * Prepare menu data for insert/update
     * 
     * @param array $data Raw menu data
     * @param bool $is_insert Is this for insert operation
     * @return array Prepared data
     */
    private function prepare_menu_data($data, $is_insert = true)
    {
        $allowed_fields = [
            'menu_type', 'slug', 'parent_id', 'parent_slug', 'name', 'href', 'icon', 'svg',
            'type', 'target', 'rel', 'css', 'position', 'level', 'disabled',
            'is_custom', 'require_login', 'badge_value', 'badge_color',
            'popup_description', 'href_original', 'option_settings',
            'roles', 'users', 'clients'
        ];
        
        $prepared = [];
        
        foreach ($allowed_fields as $field) {
            // Use array_key_exists instead of isset to handle NULL values correctly
            if (array_key_exists($field, $data)) {
                $prepared[$field] = $data[$field];
            } elseif ($is_insert) {
                // Set defaults for insert
                switch ($field) {
                    case 'position':
                    case 'level':
                        $prepared[$field] = 0;
                        break;
                    case 'disabled':
                    case 'is_custom':
                    case 'require_login':
                        $prepared[$field] = 0;
                        break;
                    case 'type':
                        $prepared[$field] = 'default';
                        break;
                }
            }
        }
        
        // Handle JSON fields
        if (isset($prepared['popup_description']) && is_array($prepared['popup_description'])) {
            $prepared['popup_description'] = json_encode($prepared['popup_description']);
        }
        
        //  Handle option_settings JSON field (flexible object)
        if (isset($prepared['option_settings'])) {
            if (is_array($prepared['option_settings'])) {
                $prepared['option_settings'] = json_encode($prepared['option_settings']);
            }
            // If it's already a string (from frontend), keep as-is
            // If empty string, set to null
            if ($prepared['option_settings'] === '' || $prepared['option_settings'] === '{}') {
                $prepared['option_settings'] = null;
            }
        }
        
        // Handle permissions JSON fields (roles, users, clients)
        foreach (['roles', 'users', 'clients'] as $field) {
            if (isset($prepared[$field])) {
                // If already a string (from frontend JSON), keep as-is
                if (is_string($prepared[$field])) {
                    // Validate JSON string
                    $decoded = json_decode($prepared[$field], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Invalid JSON, set to empty array
                        $prepared[$field] = '[]';
                    }
                    // If empty string, set to empty array
                    if ($prepared[$field] === '') {
                        $prepared[$field] = '[]';
                    }
                } elseif (is_array($prepared[$field])) {
                    // If array, encode to JSON
                    $prepared[$field] = json_encode($prepared[$field]);
                }
            } else {
                // Default to empty array if not set
                $prepared[$field] = '[]';
            }
        }
        
        // Handle boolean conversions (clearer logic)
        // disabled: Frontend 'true' = ENABLED = 0, Frontend 'false' = DISABLED = 1
        if (isset($prepared['disabled'])) {
            $original_value = $prepared['disabled'];
            if ($prepared['disabled'] === 'true' || $prepared['disabled'] === true) {
                $prepared['disabled'] = 0;  // Enabled
            } elseif ($prepared['disabled'] === 'false' || $prepared['disabled'] === false) {
                $prepared['disabled'] = 1;  // Disabled
            } elseif ($prepared['disabled'] === '1' || $prepared['disabled'] === 1) {
                $prepared['disabled'] = 1;  // Already database format: disabled
            } else {
                $prepared['disabled'] = 0;  // Default: enabled
            }
        }
        
        // is_custom: 'true' = 1, 'false' = 0
        if (isset($prepared['is_custom'])) {
            if ($prepared['is_custom'] === 'true' || $prepared['is_custom'] === '1' || $prepared['is_custom'] === 1 || $prepared['is_custom'] === true) {
                $prepared['is_custom'] = 1;
            } else {
                $prepared['is_custom'] = 0;
            }
        }
        
        // require_login: 'on' or 'true' = 1, otherwise = 0
        if (isset($prepared['require_login'])) {
            if ($prepared['require_login'] === 'on' || $prepared['require_login'] === 'true' || $prepared['require_login'] === '1' || $prepared['require_login'] === 1 || $prepared['require_login'] === true) {
                $prepared['require_login'] = 1;
            } else {
                $prepared['require_login'] = 0;
            }
        }
        
        // Handle parent_id - must be NULL for root items or valid ID
        if (isset($prepared['parent_id'])) {
            // Convert empty values to NULL
            if ($prepared['parent_id'] === '' || 
                $prepared['parent_id'] === 0 || 
                $prepared['parent_id'] === '0' || 
                $prepared['parent_id'] === 'root' || 
                $prepared['parent_id'] === 'null' ||
                $prepared['parent_id'] === null) {
                $prepared['parent_id'] = null;
            } else {
                // Validate that parent_id exists
                $parent_exists = $this->db->where('id', $prepared['parent_id'])
                                          ->count_all_results($this->table_menus) > 0;
                if (!$parent_exists) {
                    // Parent doesn't exist, set to NULL (root level)
                    $prepared['parent_id'] = null;
                }
            }
        }
        
        return $prepared;
    }
    
    /**
     * Build hierarchical tree structure from flat array
     * 
     * @param array $menus Flat array of menus
     * @param int $parent_id Parent ID to start from
     * @return array Hierarchical structure
     */
    private function build_tree($menus, $parent_id = null)
    {
        $tree = [];
        
        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parent_id) {
                $children = $this->build_tree($menus, $menu['id']);
                if (!empty($children)) {
                    $menu['children'] = $children;
                }
                $tree[] = $menu;
            }
        }
        return $tree;
    }
    
    /**
     * Calculate menu level based on parent
     * 
     * @param int $parent_id Parent menu ID
     * @return int Level (1, 2, or 3)
     */
    private function calculate_level($parent_id)
    {
        if ($parent_id === null) {
            return 1;
        }
        
        $parent = $this->db->select('level')
                           ->where('id', $parent_id)
                           ->get($this->table_menus)
                           ->row();
        
        return $parent ? ($parent->level + 1) : 1;
    }
    
    /**
     * Get next available position for menu item
     * 
     * @param string $menu_type Menu type
     * @param int $parent_id Parent menu ID
     * @return int Next position
     */
    private function get_next_position($menu_type, $parent_id = null)
    {
        $this->db->select_max('position');
        $this->db->where('menu_type', $menu_type);
        
        if ($parent_id === null) {
            $this->db->where('parent_id IS NULL', null, false);
        } else {
            $this->db->where('parent_id', $parent_id);
        }
        
        $result = $this->db->get($this->table_menus)->row();
        
        return $result ? ($result->position + 1) : 0;
    }
    
    /**
     * Get parent_slug from parent_id
     * 
     * @param int $parent_id Parent menu ID
     * @return string Parent slug or 'root'
     */
    private function get_parent_slug_from_parent_id($parent_id = null)
    {
        if ($parent_id === null) {
            return 'root';
        }
        
        $parent = $this->db->select('slug')
                          ->where('id', $parent_id)
                          ->get($this->table_menus)
                          ->row();
        
        return $parent ? $parent->slug : 'root';
    }
    
    /**
     * Ensure required dynamic columns exist for the custom menu table.
     * Needed for tenant databases that may not have run the module installer.
     * NOTE: keep this guard even after adding migrations because tenant seeds may lag behind module upgrades.
     *
     * @return void
     */
    private function ensure_table_schema()
    {
        static $schema_verified = false;

        if ($schema_verified) {
            return;
        }

        if (!$this->db->table_exists($this->table_menus)) {
            return;
        }

        $columns = [
            'option_settings' => "ALTER TABLE `{$this->table_menus}` ADD COLUMN `option_settings` TEXT DEFAULT NULL COMMENT 'JSON object for custom settings (e.g., popup_size, etc.)' AFTER `href_original`",
            'roles'           => "ALTER TABLE `{$this->table_menus}` ADD COLUMN `roles` TEXT DEFAULT NULL COMMENT 'JSON array of role IDs for permission control' AFTER `option_settings`",
            'users'           => "ALTER TABLE `{$this->table_menus}` ADD COLUMN `users` TEXT DEFAULT NULL COMMENT 'JSON array of user IDs for permission control' AFTER `roles`",
            'clients'         => "ALTER TABLE `{$this->table_menus}` ADD COLUMN `clients` TEXT DEFAULT NULL COMMENT 'JSON array of client IDs for permission control' AFTER `users`",
        ];

        foreach ($columns as $column => $statement) {
            try {
                if (!$this->db->field_exists($column, $this->table_menus)) {
                    $this->db->query($statement);
                }
            } catch (\Throwable $th) {
                log_message('error', 'poly_utilities ensure_table_schema error: ' . $th->getMessage());
            }
        }

        $schema_verified = true;
    }

    /**
     * Get permissions for a menu item
     * 
     * @param int $menu_id Menu ID
     * @return array Permissions grouped by type
     * @deprecated Table permissions removed - returns empty structure
     */
    public function get_menu_permissions($menu_id)
    {
        // Table permissions has been removed
        // Return empty structure for backward compatibility
        return [
            'roles' => [],
            'users' => [],
            'clients' => []
        ];
    }
    
    /**
     * Update disabled status of a menu item (on/off switch)
     * 
     * @param string $menu_slug Menu slug (e.g., 'menu_68f8da673ba3a')
     * @param int $disabled 1 for disabled, 0 for enabled
     * @param string $menu_type Menu type (sidebar, setup, clients)
     * @return bool Success status
     */
    public function update_disabled_status($menu_slug, $disabled, $menu_type = 'sidebar')
    {
        $this->db->where('slug', $menu_slug);
        $this->db->where('menu_type', $menu_type);
        
        //  Check if record exists before update
        $existing = $this->db->select('id, slug, disabled')
                            ->where('slug', $menu_slug)
                            ->where('menu_type', $menu_type)
                            ->get($this->table_menus)
                            ->row();
        
        if (!$existing) {
            return false;
        }
        
        //  CRITICAL: Use ID for update to ensure only 1 record is updated
        $this->db->where('id', $existing->id);
        
        $result = $this->db->update($this->table_menus, [
            'disabled' => (int)$disabled,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        //  Check affected rows
        $affected_rows = $this->db->affected_rows();
        
        return $result;
    }
}

