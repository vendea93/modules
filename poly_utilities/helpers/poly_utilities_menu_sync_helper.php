<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Poly Utilities Menu Sync Helper
 * Sync system menus to database to ensure all items have IDs
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 * MULTI-LANGUAGE SUPPORT IMPLEMENTATION
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * PROBLEM:
 * - System menus are registered using _l('language_key') which returns translated text
 * - When syncing to database, we receive translated text (e.g., "Dashboard", "Bảng điều khiển")
 * - Storing translated text means menus won't change when user switches language
 * 
 * SOLUTION:
 * - Reverse lookup: Find original language key from translated text
 * - Store language key (e.g., "als_dashboard") in database instead of translated text
 * - When rendering, _l() function converts key back to current language
 * 
 * STRATEGY:
 * 1. Load all English language files (core + modules) as reference standard
 * 2. Search for exact match between translated text and English values
 * 3. Return the language key if found
 * 4. Fallback to original text if not found (for custom menus)
 * 
 * EXAMPLE FLOW:
 * 1. Core registers: _l('als_dashboard') → "Dashboard" (EN) or "Bảng điều khiển" (VI)
 * 2. Sync detects: "Dashboard" or "Bảng điều khiển"
 * 3. Reverse lookup in English files: Find key "als_dashboard"
 * 4. Store in DB: name = "als_dashboard"
 * 5. Render: _l("als_dashboard") → Auto translates to current language ✅
 * 
 * WHY ENGLISH AS STANDARD:
 * - English is the default/fallback language in Perfex CRM
 * - All modules must have English language files
 * - Consistent reference regardless of current user language
 * - Works even when user is using Vietnamese, Chinese, etc.
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

/**
 * Normalize feature flag values stored in options to booleans.
 *
 * @param mixed $value
 * @param bool  $default
 * @return bool
 */
function poly_utilities_cast_boolean_flag($value, $default = true)
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_string($value)) {
        $normalized = strtolower($value);
        if (in_array($normalized, ['true', '1', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($normalized, ['false', '0', 'no', 'off'], true)) {
            return false;
        }
    }

    if (is_int($value)) {
        return $value === 1;
    }

    if ($value === null) {
        return (bool) $default;
    }

    return (bool) $value;
}

/**
 * Determine if the Custom Menu feature is enabled from stored settings.
 *
 * @param bool $default
 * @return bool
 */
function poly_utilities_is_custom_menu_enabled($default = true)
{
    $settings_raw = clear_textarea_breaks(get_option('poly_utilities_settings'));
    $settings = $settings_raw ? json_decode($settings_raw, true) : [];

    if (!is_array($settings)) {
        $settings = [];
    }

    $value = array_key_exists('enable_custom_menu_hooks', $settings)
        ? $settings['enable_custom_menu_hooks']
        : $default;

    return poly_utilities_cast_boolean_flag($value, $default);
}

/**
 * Find language key from translated text (reverse lookup)
 * Search in both core and module language files (English as standard)
 * 
 * OPTIMIZED WITH CACHING:
 * - Caches lookup results to avoid repeated searches
 * - Significantly improves performance when processing many menu items
 * 
 * @param string $text Translated text
 * @param string $prefix Language key prefix to search (e.g., 'als_', 'acs_')
 * @param array $context Additional context data
 * @return string|null Language key if found, null otherwise
 */
function poly_find_language_key_from_text($text, $prefix = '', $context = [])
{
    static $lookup_cache = [];
    
    $CI =& get_instance();
    
    if (empty($text)) {
        return null;
    }
    
    // Create cache key from text, prefix, and context
    $normalized_text = trim($text);
    $cache_key = md5($normalized_text . '|' . $prefix . '|' . serialize($context));
    
    // Check cache first
    if (isset($lookup_cache[$cache_key])) {
        return $lookup_cache[$cache_key];
    }
    
    try {
        $matches = [];
        
        // Strategy 1: Search in current loaded language array
        $lang_data = isset($CI->lang->language) && is_array($CI->lang->language) 
            ? $CI->lang->language 
            : [];
        
        if (!empty($lang_data)) {
            foreach ($lang_data as $key => $value) {
                // Skip non-string values
                if (!is_string($value) || !is_string($key)) {
                    continue;
                }
                
                // Compare (case-insensitive and trimmed)
                if (strcasecmp(trim($value), $normalized_text) === 0) {
                    $matches[$key] = true;
                }
            }
        }
        
        // Strategy 2: Search in English language files (core + all modules)
        // This is the standard reference for finding original keys
        // Now uses cached language keys (loaded from file cache if available)
        $english_keys = poly_load_all_english_language_keys();
        
        if (!empty($english_keys) && is_array($english_keys)) {
            foreach ($english_keys as $key => $value) {
                // Skip non-string values
                if (!is_string($value) || !is_string($key)) {
                    continue;
                }
                
                // Compare
                if (strcasecmp(trim($value), $normalized_text) === 0) {
                    $matches[$key] = true;
                }
            }
        }
        
        $result = null;
        if (!empty($matches)) {
            $selected = poly_select_best_language_key(array_keys($matches), $prefix, $context, $normalized_text);
            if ($selected !== null) {
                poly_ensure_language_key_available($selected, $normalized_text);
                $result = $selected;
            }
        }
        
        // Cache the result (even if null to avoid repeated searches)
        $lookup_cache[$cache_key] = $result;
        
        return $result;
    } catch (Exception $e) {
        // Silently catch errors and return null
        $lookup_cache[$cache_key] = null;
        return null;
    } catch (Error $e) {
        // Catch PHP 7+ errors
        $lookup_cache[$cache_key] = null;
        return null;
    }
}

/**
 * Select the best matching language key from a list of candidates.
 *
 * @param array  $keys     Candidate language keys
 * @param string $prefix   Preferred prefix (if any)
 * @param array  $context  Additional context (slug, module_hints, etc.)
 * @param string $text     Original translated text
 *
 * @return string|null
 */
function poly_select_best_language_key($keys, $prefix = '', $context = [], $text = '')
{
    if (empty($keys)) {
        return null;
    }
    
    $slug = isset($context['slug']) ? strtolower($context['slug']) : '';
    $module_hints = [];
    if (!empty($context['module_hints']) && is_array($context['module_hints'])) {
        foreach ($context['module_hints'] as $hint) {
            if (is_string($hint) && $hint !== '') {
                $module_hints[] = strtolower($hint);
            }
        }
    }
    $module_hints = array_values(array_unique($module_hints));
    
    $slug_variants = poly_build_language_slug_variants($slug, $module_hints);
    $preferred_prefix = strtolower($prefix);
    
    $best_key = null;
    $best_score = PHP_INT_MIN;
    
    foreach ($keys as $key) {
        if (!is_string($key) || $key === '') {
            continue;
        }
        
        $lower_key = strtolower($key);
        $score = 0;
        
        // Strong preference for explicit prefix matches
        if ($preferred_prefix !== '' && strpos($lower_key, $preferred_prefix) === 0) {
            $score += 400;
        }
        
        // Prefer keys that contain module hints
    $has_strong_hint = false;
    
    foreach ($module_hints as $hint) {
            if ($hint === '') {
                continue;
            }
            
            if (strpos($lower_key, $hint . '_') === 0) {
            $score += 600;
            $has_strong_hint = true;
            } elseif (strpos($lower_key, $hint . '_') !== false) {
                $score += 160;
            $has_strong_hint = true;
            } elseif (strpos($lower_key, $hint) !== false) {
                $score += 100;
            $has_strong_hint = true;
            }
        }
        
        // Prefer keys that best align with slug variants
        foreach ($slug_variants as $variant) {
            if ($variant === '') {
                continue;
            }
            
            if (strcasecmp($key, $variant) === 0) {
            if (!empty($module_hints) && !$has_strong_hint) {
                // Generic key equal to slug but no module hint – keep lower priority
                $score += 220;
            } else {
                $score += 500;
            }
            } elseif (stripos($key, $variant) !== false) {
                $score += 180;
            }
        }
        
        // Prefer longer, more descriptive keys (avoid overly-generic ones)
        $score += min(strlen($key), 255);
        $score += substr_count($key, '_') * 10;
        
        if ($best_key === null || $score > $best_score || ($score === $best_score && strlen($key) > strlen($best_key))) {
            $best_score = $score;
            $best_key = $key;
        }
    }
    
    return $best_key;
}

/**
 * Build a set of slug-related variants to help match language keys.
 *
 * @param string $slug
 * @param array  $module_hints
 *
 * @return array
 */
function poly_build_language_slug_variants($slug, $module_hints = [])
{
    $variants = [];
    $base_variants = [];
    
    if (!empty($slug)) {
        $normalized_slug = strtolower($slug);
        $base_variants[] = $normalized_slug;
        $base_variants[] = str_replace('-', '_', $normalized_slug);
        $base_variants[] = str_replace(['-', '_'], '', $normalized_slug);
    }
    
    foreach ($base_variants as $variant) {
        if ($variant !== '') {
            $variants[] = $variant;
        }
    }
    
    foreach ($module_hints as $hint) {
        if (!is_string($hint) || $hint === '') {
            continue;
        }
        
        $lower_hint = strtolower($hint);
        $variants[] = $lower_hint;
        
        foreach ($base_variants as $variant) {
            if ($variant === '') {
                continue;
            }
            
            $variants[] = $lower_hint . '_' . $variant;
            $variants[] = $lower_hint . $variant;
        }
    }
    
    return array_values(array_unique(array_filter($variants)));
}

/**
 * Ensure the detected language key has a translation loaded.
 * If missing, register the provided text as a fallback so _l() won't echo the raw key.
 *
 * @param string $key
 * @param string $fallback_text
 * @return void
 */
function poly_ensure_language_key_available($key, $fallback_text = '')
{
    if ($key === '' || !is_string($key)) {
        return;
    }
    
    $CI =& get_instance();
    
    if (!isset($CI->lang) || !is_object($CI->lang)) {
        return;
    }
    
    $current = $CI->lang->line($key, false);
    
    if ($current === false || $current === '' || $current === $key) {
        // Fallback to provided text (usually English). If empty, use key in Title Case.
        $fallback = $fallback_text !== '' ? $fallback_text : ucwords(str_replace(['_', '-'], ' ', $key));
        if (!isset($CI->lang->language) || !is_array($CI->lang->language)) {
            $CI->lang->language = [];
        }
        $CI->lang->language[$key] = $fallback;
    }
}

/**
 * Get cache directory for language keys
 * 
 * @return string Cache directory path
 */
function poly_get_language_cache_dir()
{
    $cache_dir = APPPATH . 'cache/poly_utilities_lang/';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }
    return $cache_dir;
}

/**
 * Get cache file path for language keys
 * 
 * @return string Cache file path
 */
function poly_get_language_cache_file()
{
    return poly_get_language_cache_dir() . 'english_language_keys.cache';
}

/**
 * Get cache metadata file path
 * 
 * @return string Cache metadata file path
 */
function poly_get_language_cache_meta_file()
{
    return poly_get_language_cache_dir() . 'english_language_keys.meta';
}

/**
 * Generate checksum of all English language files
 * Used to detect if language files have changed
 * 
 * @return string MD5 checksum
 */
function poly_get_language_files_checksum()
{
    $checksum_data = [];
    
    // 1. Core English language files
    $core_lang_path = APPPATH . 'language/english/';
    if (is_dir($core_lang_path)) {
        $files = glob($core_lang_path . '*_lang.php');
        foreach ($files as $file) {
            if (is_file($file) && is_readable($file)) {
                $checksum_data[] = $file . ':' . filemtime($file);
            }
        }
    }
    
    // 2. Module English language files
    $modules_path = FCPATH . 'modules/';
    if (is_dir($modules_path)) {
        $modules = @scandir($modules_path);
        if ($modules !== false) {
            foreach ($modules as $module) {
                if ($module === '.' || $module === '..') {
                    continue;
                }
                
                $module_lang_path = $modules_path . $module . '/language/english/';
                if (is_dir($module_lang_path)) {
                    $files = @glob($module_lang_path . '*_lang.php');
                    if ($files !== false) {
                        foreach ($files as $file) {
                            if (is_file($file) && is_readable($file)) {
                                $checksum_data[] = $file . ':' . filemtime($file);
                            }
                        }
                    }
                }
            }
        }
    }
    
    return md5(implode('|', $checksum_data));
}

/**
 * Load all English language keys from core and modules
 * English is used as the standard reference for finding original keys
 * 
 * OPTIMIZED WITH PERSISTENT FILE CACHE:
 * - Uses file cache to avoid loading all language files on every request
 * - Cache is invalidated when language files are modified
 * - Includes reverse index (text => key) for faster lookups
 * 
 * @return array All language keys
 */
function poly_load_all_english_language_keys()
{
    static $cached_keys = null;
    
    // Return static cache if already loaded in this request
    if ($cached_keys !== null) {
        return $cached_keys;
    }
    
    $cache_file = poly_get_language_cache_file();
    $meta_file = poly_get_language_cache_meta_file();
    $current_checksum = poly_get_language_files_checksum();
    
    // Try to load from file cache
    if (file_exists($cache_file) && file_exists($meta_file)) {
        $meta = @json_decode(file_get_contents($meta_file), true);
        if (is_array($meta) && isset($meta['checksum']) && $meta['checksum'] === $current_checksum) {
            // Cache is valid, load from file
            $cache_data = @unserialize(file_get_contents($cache_file));
            if (is_array($cache_data) && isset($cache_data['keys'])) {
                $cached_keys = $cache_data['keys'];
                return $cached_keys;
            }
        }
    }
    
    // Cache miss or invalid - load from files
    $all_keys = [];
    
    // 1. Load core English language files
    $core_lang_path = APPPATH . 'language/english/';
    if (is_dir($core_lang_path)) {
        $files = glob($core_lang_path . '*_lang.php');
        foreach ($files as $file) {
            if (!is_file($file) || !is_readable($file)) {
                continue;
            }
            
            try {
                $lang = [];
                @include($file);
                
                if (!empty($lang) && is_array($lang)) {
                    $all_keys = array_merge($all_keys, $lang);
                }
            } catch (Exception $e) {
                continue;
            } catch (Error $e) {
                continue;
            }
        }
    }
    
    // 2. Load module English language files
    $modules_path = FCPATH . 'modules/';
    if (is_dir($modules_path)) {
        $modules = @scandir($modules_path);
        if ($modules === false) {
            $modules = [];
        }
        
        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }
            
            $module_lang_path = $modules_path . $module . '/language/english/';
            if (!is_dir($module_lang_path)) {
                continue;
            }
            
            $files = @glob($module_lang_path . '*_lang.php');
            if ($files === false) {
                $files = [];
            }
            
            foreach ($files as $file) {
                if (!is_file($file) || !is_readable($file)) {
                    continue;
                }
                
                try {
                    $lang = [];
                    @include($file);
                    
                    if (!empty($lang) && is_array($lang)) {
                        $all_keys = array_merge($all_keys, $lang);
                    }
                } catch (Exception $e) {
                    continue;
                } catch (Error $e) {
                    continue;
                }
            }
        }
    }
    
    // Store in static cache for this request
    $cached_keys = $all_keys;
    
    // Save to file cache for future requests
    try {
        $cache_data = [
            'keys' => $all_keys,
            'created' => time(),
            'count' => count($all_keys)
        ];
        
        $meta_data = [
            'checksum' => $current_checksum,
            'created' => time(),
            'count' => count($all_keys)
        ];
        
        @file_put_contents($cache_file, serialize($cache_data), LOCK_EX);
        @file_put_contents($meta_file, json_encode($meta_data), LOCK_EX);
    } catch (Exception $e) {
        // Silently fail if cache write fails
    }
    
    return $all_keys;
}

/**
 * Clear language keys cache
 * Call this when language files are updated
 * 
 * @return bool Success status
 */
function poly_clear_language_keys_cache()
{
    $cache_file = poly_get_language_cache_file();
    $meta_file = poly_get_language_cache_meta_file();
    
    $success = true;
    if (file_exists($cache_file)) {
        $success = @unlink($cache_file) && $success;
    }
    if (file_exists($meta_file)) {
        $success = @unlink($meta_file) && $success;
    }
    
    return $success;
}

/**
 * Try to get original language key for menu name
 * Supports common Perfex CRM menu prefixes and module keys
 * 
 * @param string $name Translated menu name
 * @param string $slug Menu slug (for context)
 * @param array  $context Additional context data (module hints, etc.)
 * @return string Language key or original name if not found
 */
function poly_get_original_language_key($name, $slug = '', $context = [])
{
    if (empty($name)) {
        return '';
    }
    
    $context = is_array($context) ? $context : [];
    if (!isset($context['slug'])) {
        $context['slug'] = $slug;
    }
    
    $module_hints = [];
    if (!empty($context['module_hints']) && is_array($context['module_hints'])) {
        $module_hints = array_values(array_unique(array_filter($context['module_hints'])));
    }
    
    // Common prefixes used in Perfex CRM menus
    $prefixes = [
        'als_',         // Admin Left Sidebar
        'acs_',         // Admin Configuration/Setup
        'clients_nav_', // Clients navigation
        'poly_utilities_', // Poly Utilities module
    ];
    
    // Strategy 1: Try each common prefix
    foreach ($prefixes as $prefix) {
        $key = poly_find_language_key_from_text($name, $prefix, $context);
        if ($key) {
            return $key;
        }
    }
    
    // Strategy 2: Try without prefix (general search)
    $key = poly_find_language_key_from_text($name, '', $context);
    if ($key) {
        return $key;
    }
    
    // Strategy 3: Try to construct key from slug
    $hint_based_keys = [];
    foreach ($module_hints as $hint) {
        if (is_string($hint) && $hint !== '') {
            $hint_lower = strtolower($hint);
            $hint_based_keys[] = $hint_lower . '_' . $slug;
            $hint_based_keys[] = $hint_lower . '_' . str_replace('-', '_', $slug);
        }
    }
    
    // Common patterns: dashboard → als_dashboard, settings → acs_settings
    $CI =& get_instance();
    $possible_keys = [
        'als_' . $slug,
        'acs_' . $slug,
        $slug,
        str_replace('-', '_', $slug),
        'clients_nav_' . $slug,
    ];
    
    $possible_keys = array_values(array_unique(array_merge($hint_based_keys, $possible_keys)));
    
    foreach ($possible_keys as $test_key) {
        // Check if key exists in language array
        if (isset($CI->lang->language[$test_key])) {
            return $test_key;
        }
    }
    
    // Strategy 4: Search in English files directly by slug pattern
    $english_keys = poly_load_all_english_language_keys();
    foreach ($possible_keys as $test_key) {
        if (isset($english_keys[$test_key])) {
            return $test_key;
        }
    }
    
    // If all fails, return original name (will be treated as static text)
    return $name;
}

/**
 * Extract potential module hints from menu item data.
 *
 * @param array $item
 * @return array
 */
function poly_extract_module_hints_from_menu_item($item)
{
    $hints = [];
    
    if (isset($item['module']) && is_string($item['module']) && $item['module'] !== '') {
        $hints[] = $item['module'];
    }
    
    if (isset($item['slug']) && is_string($item['slug']) && $item['slug'] !== '') {
        $slug = strtolower($item['slug']);
        
        // First, add the full slug as a hint (in case slug = module name exactly)
        $hints[] = $slug;
        
        // Then, try to extract module name by matching with active modules
        // This ensures we get full module names like "poly_demo" not just "poly"
        $CI = &get_instance();
        $active_modules = $CI->app_modules->get_activated();
        if (is_array($active_modules)) {
            // Sort by length (longest first) to match longer module names first
            $module_names = array_keys($active_modules);
            usort($module_names, function($a, $b) {
                return strlen($b) - strlen($a);
            });
            
            foreach ($module_names as $module_name) {
                $module_name_lower = strtolower($module_name);
                // Check if slug starts with module name
                if ($slug === $module_name_lower ||
                    strpos($slug, $module_name_lower . '_') === 0 ||
                    strpos($slug, $module_name_lower . '-') === 0) {
                    $hints[] = $module_name;
                    break; // Use the longest match
                }
            }
        }
        
        // Fallback: Extract first segment (for backward compatibility)
        if (strpos($slug, '_') !== false) {
            $hints[] = strtok($slug, '_');
        }
        if (strpos($slug, '-') !== false) {
            $hints[] = strtok(str_replace('_', '-', $slug), '-');
        }
    }
    
    if (isset($item['href']) && is_string($item['href']) && $item['href'] !== '') {
        $path = parse_url($item['href'], PHP_URL_PATH);
        if ($path) {
            $segments = array_values(array_filter(explode('/', strtolower($path))));
            
            $admin_index = array_search('admin', $segments, true);
            if ($admin_index !== false && isset($segments[$admin_index + 1])) {
                $hints[] = $segments[$admin_index + 1];
            }
            
            $clients_index = array_search('clients', $segments, true);
            if ($clients_index !== false && isset($segments[$clients_index + 1])) {
                $hints[] = $segments[$clients_index + 1];
            }
            
            if (!empty($segments)) {
                $hints[] = $segments[0];
            }
        }
    }
    
    $hints = array_filter(array_unique($hints));
    
    return array_values($hints);
}

/**
 * Detect module name from menu item
 * Tries multiple strategies to identify which module owns the menu item
 * 
 * @param array $item Menu item data
 * @return string|null Module name or null if cannot detect
 */
function poly_detect_module_name_from_menu_item($item)
{
    $CI = &get_instance();
    
    // Strategy 1: Check if module field exists in item
    if (isset($item['module']) && is_string($item['module']) && $item['module'] !== '') {
        $module_name = trim($item['module']);
        // Verify module exists (get() returns module data or false)
        $module = $CI->app_modules->get($module_name);
        if ($module !== false) {
            return $module_name;
        }
    }
    
    // Strategy 2: Extract from href (most reliable for PerfexCRM)
    if (isset($item['href']) && is_string($item['href']) && $item['href'] !== '') {
        $href = $item['href'];
        
        // Handle callable hrefs (closures)
        if (is_callable($href)) {
            // Cannot determine module from callable
            return null;
        }
        
        // Parse URL
        $path = parse_url($href, PHP_URL_PATH);
        if ($path) {
            $segments = array_values(array_filter(explode('/', strtolower($path))));
            
            // Pattern: /admin/{module_name}/...
            $admin_index = array_search('admin', $segments, true);
            if ($admin_index !== false && isset($segments[$admin_index + 1])) {
                $potential_module = $segments[$admin_index + 1];
                // Skip common non-module routes
                if (!in_array($potential_module, ['dashboard', 'settings', 'utilities', 'profile', 'logout', 'login', 'api'])) {
                    $module = $CI->app_modules->get($potential_module);
                    if ($module !== false) {
                        return $potential_module;
                    }
                }
            }
            
            // Pattern: /clients/{module_name}/...
            $clients_index = array_search('clients', $segments, true);
            if ($clients_index !== false && isset($segments[$clients_index + 1])) {
                $potential_module = $segments[$clients_index + 1];
                // Skip common non-module routes
                if (!in_array($potential_module, ['dashboard', 'profile', 'logout', 'login', 'projects', 'invoices', 'contracts', 'estimates', 'proposals', 'subscriptions', 'tickets', 'support'])) {
                    $module = $CI->app_modules->get($potential_module);
                    if ($module !== false) {
                        return $potential_module;
                    }
                }
            }
        }
    }
    
    // Strategy 3: Extract from slug - try to match with full module names first
    if (isset($item['slug']) && is_string($item['slug']) && $item['slug'] !== '') {
        $slug = strtolower($item['slug']);
        
        // First, try exact match with slug (e.g., slug = "poly_demo" should match module "poly_demo")
        $module = $CI->app_modules->get($slug);
        if ($module !== false) {
            return $slug;
        }
        
        // Second, try to match with all active modules by checking if slug starts with module name
        // This handles cases like slug = "poly_demo_dashboard" should match module "poly_demo"
        $active_modules = $CI->app_modules->get_activated();
        if (is_array($active_modules)) {
            // Sort by length (longest first) to match "poly_demo" before "poly"
            $module_names = array_keys($active_modules);
            usort($module_names, function($a, $b) {
                return strlen($b) - strlen($a);
            });
            
            foreach ($module_names as $module_name) {
                $module_name_lower = strtolower($module_name);
                // Check if slug starts with module name followed by separator
                if ($slug === $module_name_lower ||
                    strpos($slug, $module_name_lower . '_') === 0 ||
                    strpos($slug, $module_name_lower . '-') === 0) {
                    return $module_name;
                }
            }
        }
        
        // Fallback: Try first segment of slug (before _ or -) only if no better match found
        // This is less reliable but may catch some edge cases
        $parts = preg_split('/[_-]/', $slug);
        if (!empty($parts) && isset($parts[0])) {
            $potential_module = $parts[0];
            // Skip common prefixes that are not modules
            if (!in_array($potential_module, ['als', 'acs', 'clients', 'nav', 'admin', 'menu'])) {
                $module = $CI->app_modules->get($potential_module);
                if ($module !== false) {
                    return $potential_module;
                }
            }
        }
    }
    
    // Strategy 4: Check all active modules and see if any match the href pattern
    // This is a fallback for edge cases
    // IMPORTANT: Sort by length (longest first) to match "poly_demo" before "poly"
    if (isset($item['href']) && is_string($item['href']) && $item['href'] !== '') {
        $href = $item['href'];
        $path = parse_url($href, PHP_URL_PATH);
        
        if ($path) {
            $active_modules = $CI->app_modules->get_activated();
            if (is_array($active_modules)) {
                // Sort by length (longest first) to match longer module names first
                // This ensures "poly_demo" matches before "poly"
                $module_names = array_keys($active_modules);
                usort($module_names, function($a, $b) {
                    return strlen($b) - strlen($a);
                });
                
                foreach ($module_names as $module_name) {
                    // Check if href contains module name (with separators to avoid partial matches)
                    if (stripos($path, '/' . $module_name . '/') !== false || 
                        stripos($path, '/' . $module_name) === (strlen($path) - strlen('/' . $module_name))) {
                        return $module_name;
                    }
                }
            }
        }
    }
    
    // Cannot determine module - return null
    // This is OK for core menu items (dashboard, settings, etc.)
    return null;
}

/**
 * Check if menu sync is needed for a menu type
 * Uses cache to avoid unnecessary syncs
 * 
 * @param string $menu_type Menu type
 * @return bool True if sync needed, false otherwise
 */
function poly_needs_menu_sync($menu_type)
{
    static $sync_status_cache = [];
    
    // Check cache first
    if (isset($sync_status_cache[$menu_type])) {
        return $sync_status_cache[$menu_type];
    }
    
    $CI =& get_instance();
    
    // Check if table exists
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        $sync_status_cache[$menu_type] = false;
        return false;
    }
    
    // Check if any system menus exist for this menu type
    $CI->db->reset_query();
    $count = $CI->db->where('menu_type', $menu_type)
                    ->where('is_custom', 0)
                    ->count_all_results(db_prefix() . 'poly_utilities_custom_menus');
    
    // If no system menus exist, sync is needed
    $needs_sync = ($count === 0);
    
    // Cache the result for this request
    $sync_status_cache[$menu_type] = $needs_sync;
    
    return $needs_sync;
}

/**
 * Get all menus from database (system + custom merged)
 * 
 * OPTIMIZED WITH LAZY SYNC:
 * - Only syncs system menus when needed (first time or when missing)
 * - Avoids unnecessary syncs on every request
 * - Translation remains dynamic (language keys stored, translated on render)
 * 
 * @param string $menu_type Menu type
 * @param int $max_level Max level to fetch
 * @param bool $load_permissions Load permission data (for admin management, set to false to see all items)
 * @return array Merged menu items
 */
function poly_get_all_menus_from_db($menu_type, $max_level = 3, $load_permissions = true)
{
    $CI =& get_instance();

    if (function_exists('poly_utilities_is_custom_menu_enabled') && !poly_utilities_is_custom_menu_enabled()) {
        switch ($menu_type) {
            case 'sidebar':
                return $CI->app_menu->get_sidebar_menu_items();
            case 'setup':
                return $CI->app_menu->get_setup_menu_items();
            case 'clients':
                return $CI->app_menu->get_client_menu_items();
            default:
                return [];
        }
    }
    
    // Check if table exists (prevent errors during activation/deactivation)
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        return [];
    }
    
    // Reset query builder to avoid conflicts
    $CI->db->reset_query();
    
    // For clients menu: init default menus if empty
    if ($menu_type === 'clients' && function_exists('poly_init_default_clients_menu')) {
        poly_init_default_clients_menu();
    }
    
    // OPTIMIZED: Only sync when needed (lazy sync)
    if (poly_needs_menu_sync($menu_type)) {
        poly_sync_system_menus_to_db($menu_type);
    }
    
    // Reset query builder again before fetching
    $CI->db->reset_query();
    
    // Then fetch all from DB (both system and custom)
    $CI->db->select('*');
    $CI->db->from(db_prefix() . 'poly_utilities_custom_menus');
    $CI->db->where('menu_type', $menu_type);
    $CI->db->where('disabled', 0);
    $CI->db->where('level <=', $max_level);
    $CI->db->order_by('position', 'ASC');
    $CI->db->order_by('id', 'ASC'); // Secondary sort for consistency
    $query = $CI->db->get();
    
    if (!$query || $query->num_rows() === 0) {
        return [];
    }
    
    $items = $query->result_array();
    
    // Load permissions for each menu item (only if requested)
    if ($load_permissions) {
        $CI->load->model('poly_utilities/custom_menu_model');
        foreach ($items as &$item) {
            $permissions = $CI->custom_menu_model->get_menu_permissions($item['id']);
            $item['permissions'] = $permissions;
        }
        unset($item);
    }
    
    // Build hierarchical structure
    // Ensure helper is loaded before calling function
    if (!function_exists('poly_build_menu_tree')) {
        $module_name = defined('POLY_UTILITIES_MODULE_NAME') ? POLY_UTILITIES_MODULE_NAME : 'poly_utilities';
        
        try {
            // Try loading via CI loader
            $CI->load->helper($module_name . '/poly_utilities_menu_limited');
        } catch (Exception $e) {
            log_message('error', 'Poly Utilities: Failed to load poly_utilities_menu_limited helper: ' . $e->getMessage());
        }
        
        // If function still doesn't exist after loading, log error and return empty array
        if (!function_exists('poly_build_menu_tree')) {
            log_message('error', 'Poly Utilities: poly_build_menu_tree() function not found after loading helper');
            return [];
        }
    }
    
    return poly_build_menu_tree($items, null, $max_level, 1, $menu_type);
}

/**
 * Filter menu items based on user permissions (users/roles)
 * 
 * @param array $menu_items Menu items to filter
 * @param int $staff_id Staff user ID
 * @return array Filtered menu items
 */
function poly_filter_menu_items_by_permission($menu_items, $staff_id)
{
    $filtered_items = [];
    
    foreach ($menu_items as $item) {
        // Check if user has access to this menu item
        $has_access = poly_utilities_is_access_menu_item($item, $staff_id);
        
        if ($has_access) {
            // Additional check: if menu item is a poly_utilities route, verify module access
            if (isset($item['href']) && strpos($item['href'], 'poly_utilities') !== false) {
                // Check if it's a custom_menu route and user doesn't have access
                if (strpos($item['href'], 'poly_utilities/custom_menu') !== false) {
                    if (!staff_can_poly_utilities_custom_menu()) {
                        continue; // Hide Custom Menu if user doesn't have access
                    }
                }
                // For other poly_utilities routes, check module access
                elseif (!staff_can_poly_utilities()) {
                    continue; // Hide poly_utilities menu items if user doesn't have module access
                }
            }
            
            // User has access, include this item and its children (recursively filtered)
            if (!empty($item['children'])) {
                $item['children'] = poly_filter_menu_items_by_permission($item['children'], $staff_id);
            }
            $filtered_items[] = $item;
        }
    }
    
    return $filtered_items;
}

/**
 * Sync system menus to database
 * This ensures all menu items (system + custom) have database IDs
 * 
 * @param string $menu_type Menu type: 'sidebar', 'setup', 'clients'
 * @return bool Success status
 */
function poly_sync_system_menus_to_db($menu_type = 'sidebar')
{
    $CI =& get_instance();
    $CI->load->database();
    
    // Check if table exists (prevent errors during activation/deactivation)
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        return false;
    }
    
    // Reset query builder to avoid conflicts
    $CI->db->reset_query();
    
    // Get system menus from app_menu
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
    
    if (empty($system_menus)) {
        return true;
    }
    
    // Flatten and sync to database
    // Pass null for existing_menus_map - it will be loaded in first call
    poly_sync_menu_recursive($system_menus, $menu_type, null, 1);
    
    // CRITICAL: Reset query builder at the end to prevent leftover WHERE clauses
    // from affecting subsequent database operations (especially during module deactivation)
    $CI->db->reset_query();
    
    return true;
}

/**
 * Recursively sync menu items to database
 * 
 * OPTIMIZED WITH BATCH QUERIES:
 * - Loads all existing menus in one query instead of N queries
 * - Reduces database overhead significantly
 * - Translation remains dynamic (stores language keys, not translated text)
 * 
 * @param array $items Menu items
 * @param string $menu_type Menu type
 * @param int|null $parent_id Parent menu ID
 * @param int $level Current level
 * @param array $existing_menus_map Pre-loaded existing menus map (for performance)
 * @return void
 */
function poly_sync_menu_recursive($items, $menu_type, $parent_id = null, $level = 1, &$existing_menus_map = null)
{
    $CI =& get_instance();
    
    // Check if module_name column exists (for backward compatibility)
    // This check needs to be available throughout the function
    $custom_menus_table = db_prefix() . 'poly_utilities_custom_menus';
    static $has_module_name_column = null;
    if ($has_module_name_column === null) {
        $has_module_name_column = $CI->db->field_exists('module_name', $custom_menus_table);
    }
    
    // OPTIMIZED: Load all existing menus in one batch query (first call only)
    // PERFORMANCE: Load full menu data once to avoid N queries when comparing
    if ($existing_menus_map === null) {
        $CI->db->reset_query();
        
        // Include module_name in select to track which module owns each menu item (if column exists)
        $select_fields = 'slug, id, is_custom, name, href, icon, position, level, parent_id, badge_value, badge_color';
        if ($has_module_name_column) {
            $select_fields .= ', module_name';
        }
        
        $existing_menus = $CI->db->select($select_fields)
                                 ->where('menu_type', $menu_type)
                                 ->get($custom_menus_table)
                                 ->result_array();
        
        $existing_menus_map = [
            'system' => [], // is_custom = 0
            'custom' => []  // is_custom = 1
        ];
        
        foreach ($existing_menus as $menu) {
            $key = $menu['slug'];
            if ($menu['is_custom'] == 1) {
                $existing_menus_map['custom'][$key] = $menu;
            } else {
                $existing_menus_map['system'][$key] = $menu;
            }
        }
    }
    
    foreach ($items as $key => $item) {
        $slug = $item['slug'] ?? $key;
        
        // Skip items without slug
        if (empty($slug)) {
            continue;
        }
        
        // OPTIMIZED: Check in pre-loaded map instead of querying database
        $existing = isset($existing_menus_map['system'][$slug]) 
            ? (object)$existing_menus_map['system'][$slug] 
            : null;
        
        // Skip if custom menu with same slug exists
        if (isset($existing_menus_map['custom'][$slug])) {
            // Skip system sync for this slug - custom menu takes priority
            continue;
        }
        
        // Convert translated text back to language key
        // IMPORTANT: Store language key (e.g., "als_dashboard") not translated text
        // This ensures translation remains DYNAMIC - menu names change when language changes
        $menu_name = $item['name'] ?? '';
        $menu_context = [
            'slug' => $slug,
            'module_hints' => poly_extract_module_hints_from_menu_item($item),
        ];
        $language_key = poly_get_original_language_key($menu_name, $slug, $menu_context);
        
        // Detect module name for this menu item
        $detected_module_name = poly_detect_module_name_from_menu_item($item);
        
        if ($existing) {
            // PERFORMANCE OPTIMIZATION: Compare values before updating
            // Only update if there are actual changes to avoid unnecessary database writes
            $existing_menu = is_array($existing) ? $existing : (array)$existing;
            
            $new_href = $item['href'] ?? '#';
            $new_icon = $item['icon'] ?? '';
            $new_position = $item['position'] ?? 0;
            $new_badge_value = !empty($item['badge']) ? ($item['badge']['value'] ?? null) : null;
            $new_badge_color = !empty($item['badge']) ? ($item['badge']['color'] ?? null) : null;
            $parent_slug_value = $parent_id ? poly_get_parent_slug_by_id($parent_id) : 'root';
            
            // Check if update is needed (compare values from pre-loaded map)
            // Also check module_name to ensure it's updated if module changed (only if column exists)
            $needs_update = (
                ($existing_menu['name'] ?? '') !== $language_key ||
                ($existing_menu['href'] ?? '#') !== $new_href ||
                ($existing_menu['icon'] ?? '') !== $new_icon ||
                (int)($existing_menu['position'] ?? 0) !== (int)$new_position ||
                (int)($existing_menu['level'] ?? 1) !== (int)$level ||
                (int)($existing_menu['parent_id'] ?? null) !== (int)$parent_id ||
                ($existing_menu['badge_value'] ?? null) != $new_badge_value ||
                ($existing_menu['badge_color'] ?? null) != $new_badge_color
            );
            
            // Only compare module_name if column exists
            if ($has_module_name_column) {
                $existing_module_name = $existing_menu['module_name'] ?? null;
                $needs_update = $needs_update || ($existing_module_name !== $detected_module_name);
            }
            
            // Only update if there are actual changes (performance optimization)
            if ($needs_update) {
                $update_data = [
                    'name' => $language_key, // Language key (e.g., "als_dashboard"), not translated text
                    'href' => $new_href, // Update href in case route changed
                    'icon' => $new_icon, // Update icon in case it changed
                    'position' => $new_position, // Update position in case it changed
                    'level' => $level,
                    'parent_id' => $parent_id,
                    'parent_slug' => $parent_slug_value,
                    // ❌ DON'T override disabled - preserve user settings!
                    // 'disabled' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Handle badge
                $update_data['badge_value'] = $new_badge_value;
                $update_data['badge_color'] = $new_badge_color;
                
                // Update module_name if detected and column exists
                if ($has_module_name_column && $detected_module_name !== null) {
                    $update_data['module_name'] = $detected_module_name;
                }
                
                $CI->db->reset_query();
                $CI->db->where('id', $existing_menu['id']);
                $CI->db->update($custom_menus_table, $update_data);
                
                // Update existing_menus_map to reflect changes (for subsequent comparisons)
                $menu_update = [
                    'name' => $language_key,
                    'href' => $new_href,
                    'icon' => $new_icon,
                    'position' => $new_position,
                    'level' => $level,
                    'parent_id' => $parent_id,
                    'badge_value' => $new_badge_value,
                    'badge_color' => $new_badge_color
                ];
                
                // Only add module_name if column exists
                if ($has_module_name_column) {
                    $menu_update['module_name'] = $detected_module_name;
                }
                
                $existing_menus_map['system'][$slug] = array_merge($existing_menu, $menu_update);
            }
            
            $current_id = $existing_menu['id'];
        } else {
            // Insert new system menu
            $insert_data = [
                'menu_type' => $menu_type,
                'slug' => $slug,
                'name' => $language_key, // Store language key, not translated text
                'href' => $item['href'] ?? '#',
                'icon' => $item['icon'] ?? '',
                'type' => 'default',
                'position' => $item['position'] ?? 0,
                'level' => $level,
                'parent_id' => $parent_id,
                'parent_slug' => $parent_id ? poly_get_parent_slug_by_id($parent_id) : 'root',
                'disabled' => 0,
                'is_custom' => 0, // System menu
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle badge
            if (!empty($item['badge'])) {
                $insert_data['badge_value'] = $item['badge']['value'] ?? null;
                $insert_data['badge_color'] = $item['badge']['color'] ?? null;
            }
            
            // Add module_name if detected and column exists
            if ($has_module_name_column && $detected_module_name !== null) {
                $insert_data['module_name'] = $detected_module_name;
            }
            
            $CI->db->insert($custom_menus_table, $insert_data);
            $current_id = $CI->db->insert_id();
        }
        
        // Update existing_menus_map with newly inserted menu (include full data for future comparisons)
        if (!$existing && $current_id) {
            $menu_data = [
                'slug' => $slug,
                'id' => $current_id,
                'is_custom' => 0,
                'name' => $language_key,
                'href' => $item['href'] ?? '#',
                'icon' => $item['icon'] ?? '',
                'position' => $item['position'] ?? 0,
                'level' => $level,
                'parent_id' => $parent_id,
                'badge_value' => !empty($item['badge']) ? ($item['badge']['value'] ?? null) : null,
                'badge_color' => !empty($item['badge']) ? ($item['badge']['color'] ?? null) : null
            ];
            
            // Only add module_name if column exists
            if ($has_module_name_column) {
                $menu_data['module_name'] = $detected_module_name;
            }
            
            $existing_menus_map['system'][$slug] = $menu_data;
        }
        
        // Recursively sync children (pass existing_menus_map to avoid re-querying)
        if (!empty($item['children'])) {
            poly_sync_menu_recursive($item['children'], $menu_type, $current_id, $level + 1, $existing_menus_map);
        }
    }
}

/**
 * Get parent slug by parent ID
 * 
 * @param int $parent_id Parent ID
 * @return string Parent slug or 'root'
 */
function poly_get_parent_slug_by_id($parent_id)
{
    if (!$parent_id) {
        return 'root';
    }
    
    $CI =& get_instance();
    
    // Reset query builder to avoid conflicts
    $CI->db->reset_query();
    
    $parent = $CI->db->get_where(db_prefix() . 'poly_utilities_custom_menus', ['id' => $parent_id])->row();
    
    return $parent ? $parent->slug : 'root';
}

/**
 * Recursively delete children menu items
 * Helper function to delete all children of given parent IDs
 * 
 * @param object $CI CodeIgniter instance
 * @param array $parent_ids Array of parent menu IDs
 * @param string $menu_type Menu type
 * @return int Total number of children deleted
 */
function delete_menu_children_recursive($CI, $parent_ids, $menu_type)
{
    if (empty($parent_ids)) {
        return 0;
    }
    
    $total_deleted = 0;
    $max_iterations = 10; // Safety limit to prevent infinite loops
    $iteration = 0;
    
    while (!empty($parent_ids) && $iteration < $max_iterations) {
        $CI->db->reset_query();
        $children = $CI->db->select('id')
                           ->where('menu_type', $menu_type)
                           ->where('is_custom', 0)
                           ->where_in('parent_id', $parent_ids)
                           ->get(db_prefix() . 'poly_utilities_custom_menus')
                           ->result_array();
        
        if (empty($children)) {
            break; // No more children
        }
        
        $child_ids = array_column($children, 'id');
        
        // Delete these children
        $CI->db->reset_query();
        $CI->db->where_in('id', $child_ids);
        $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
        $deleted = $CI->db->affected_rows();
        $total_deleted += $deleted;
        
        // Continue with children of these children
        $parent_ids = $child_ids;
        $iteration++;
    }
    
    return $total_deleted;
}

/**
 * Clean up orphaned system menus (removed from app_menu)
 * Also removes menu items from deactivated modules
 * 
 * @param string $menu_type Menu type
 * @param string|null $deactivated_module_name Optional: specific module to cleanup (for performance)
 * @return int Number of items removed
 */
function poly_cleanup_orphaned_system_menus($menu_type, $deactivated_module_name = null)
{
    $CI =& get_instance();
    
    // Check if table exists (prevent errors during activation/deactivation)
    $custom_menus_table = db_prefix() . 'poly_utilities_custom_menus';
    if (!$CI->db->table_exists($custom_menus_table)) {
        return 0;
    }
    
    // Check if module_name column exists (for sites upgrading from older versions)
    $has_module_name_column = $CI->db->field_exists('module_name', $custom_menus_table);
    
    // Reset query builder to avoid conflicts
    $CI->db->reset_query();
    
    $total_deleted = 0;
    
    // Strategy 1: If specific module is provided, delete its menu items directly (optimized)
    // This includes BOTH root items and all children items of the deactivated module
    // COMPREHENSIVE APPROACH: Delete ALL items that match module by slug pattern, regardless of module_name
    if ($deactivated_module_name !== null && $deactivated_module_name !== '') {
        $module_lower = strtolower($deactivated_module_name);
        
        // Step 1: Get ALL menu items that belong to this module by slug pattern
        // This catches items with or without module_name set
        $CI->db->reset_query();
        
        // Build select fields based on column existence
        $select_fields = 'id, slug, parent_id';
        if ($has_module_name_column) {
            $select_fields .= ', module_name';
        }
        
        $query = $CI->db->select($select_fields)
                       ->where('menu_type', $menu_type)
                       ->where('is_custom', 0)
                       ->group_start()
                           ->or_where('slug', $deactivated_module_name) // Exact slug match
                           ->or_like('slug', $module_lower . '_', 'after') // Starts with module_
                           ->or_like('slug', $module_lower . '-', 'after'); // Starts with module-
        
        // Only add module_name condition if column exists
        if ($has_module_name_column) {
            $query->or_where('module_name', $deactivated_module_name);
        }
        
        $all_module_items = $query->group_end()
                                   ->get($custom_menus_table)
                                   ->result_array();
        
        $all_module_item_ids = array_column($all_module_items, 'id');
        
        // Step 2: Also check for items by href pattern (for root items especially)
        $CI->db->reset_query();
        $items_by_href = $CI->db->select('id')
                                ->where('menu_type', $menu_type)
                                ->where('is_custom', 0)
                                ->group_start()
                                    ->like('href', '/admin/' . $module_lower . '/', 'after')
                                    ->or_like('href', '/admin/' . $module_lower, 'both')
                                    ->or_like('href', '/clients/' . $module_lower . '/', 'after')
                                    ->or_like('href', '/clients/' . $module_lower, 'both')
                                    ->or_like('href', '/' . $module_lower . '/', 'after')
                                    ->or_like('href', '/' . $module_lower, 'both')
                                ->group_end()
                                ->get(db_prefix() . 'poly_utilities_custom_menus')
                                ->result_array();
        
        $items_by_href_ids = array_column($items_by_href, 'id');
        $all_module_item_ids = array_unique(array_merge($all_module_item_ids, $items_by_href_ids));
        
        // Step 3: Delete all children of these items FIRST (recursive cleanup)
        // This ensures we delete from bottom up to avoid orphaned children
        if (!empty($all_module_item_ids)) {
            $deleted_children = delete_menu_children_recursive($CI, $all_module_item_ids, $menu_type);
            $total_deleted += $deleted_children;
        }
        
        // Step 4: Now delete all identified items (including root items)
        if (!empty($all_module_item_ids)) {
            $CI->db->reset_query();
            $CI->db->where_in('id', $all_module_item_ids);
            $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
            $deleted_by_ids = $CI->db->affected_rows();
            $total_deleted += $deleted_by_ids;
        }
        
        // Step 5: Also delete by module_name (catch any remaining items) - only if column exists
        if ($has_module_name_column) {
            $CI->db->reset_query();
            $CI->db->where('menu_type', $menu_type);
            $CI->db->where('is_custom', 0);
            $CI->db->where('module_name', $deactivated_module_name);
            $CI->db->delete($custom_menus_table);
            $deleted_by_module = $CI->db->affected_rows();
            $total_deleted += $deleted_by_module;
        }
        
        // Step 6: Final comprehensive cleanup - delete by slug pattern (catches everything)
        // Delete all items where slug starts with module name (with any separator)
        $CI->db->reset_query();
        $CI->db->where('menu_type', $menu_type);
        $CI->db->where('is_custom', 0);
        $CI->db->group_start()
               ->where('slug', $deactivated_module_name) // Exact match
               ->or_like('slug', $module_lower . '_', 'after') // Starts with module_
               ->or_like('slug', $module_lower . '-', 'after') // Starts with module-
        ->group_end();
        $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
        $deleted_by_slug_pattern = $CI->db->affected_rows();
        $total_deleted += $deleted_by_slug_pattern;
        
        // Step 7: Delete root items by exact slug match (safety net)
        $CI->db->reset_query();
        $CI->db->where('menu_type', $menu_type);
        $CI->db->where('is_custom', 0);
        $CI->db->where('(parent_id IS NULL OR parent_id = 0)', null, false);
        $CI->db->where('slug', $deactivated_module_name);
        $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
        $deleted_root_exact = $CI->db->affected_rows();
        $total_deleted += $deleted_root_exact;
        
        // Step 8: Delete all items where slug starts with module name (comprehensive cleanup)
        // This catches any remaining items that might have been missed
        $CI->db->reset_query();
        $remaining_items = $CI->db->select('id, slug')
                                  ->where('menu_type', $menu_type)
                                  ->where('is_custom', 0)
                                  ->get(db_prefix() . 'poly_utilities_custom_menus')
                                  ->result_array();
        
        $remaining_ids_to_delete = [];
        foreach ($remaining_items as $item) {
            $slug = strtolower($item['slug'] ?? '');
            if (empty($slug)) {
                continue;
            }
            
            // Check if slug matches module name or starts with module name
            if ($slug === $module_lower ||
                strpos($slug, $module_lower . '_') === 0 ||
                strpos($slug, $module_lower . '-') === 0) {
                $remaining_ids_to_delete[] = $item['id'];
            }
        }
        
        if (!empty($remaining_ids_to_delete)) {
            // Delete children first
            $deleted_remaining_children = delete_menu_children_recursive($CI, $remaining_ids_to_delete, $menu_type);
            $total_deleted += $deleted_remaining_children;
            
            // Then delete the items
            $CI->db->reset_query();
            $CI->db->where_in('id', $remaining_ids_to_delete);
            $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
            $deleted_remaining = $CI->db->affected_rows();
            $total_deleted += $deleted_remaining;
        }
    }
    
    // Strategy 2: Clean up orphaned menus (not in current system menu)
    // Get current system menu slugs
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
    
    $current_slugs = poly_extract_all_slugs($system_menus);
    
    // Delete system menus not in current list
    if (!empty($current_slugs)) {
        $CI->db->reset_query();
        $CI->db->where('menu_type', $menu_type);
        $CI->db->where('is_custom', 0);
        $CI->db->where_not_in('slug', $current_slugs);
        $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
        $total_deleted += $CI->db->affected_rows();
    }
    
    // Strategy 3: Clean up menu items from deactivated modules (batch operation for performance)
    // Get all active modules (returns array with module names as keys)
    $active_modules = $CI->app_modules->get_activated();
    $active_modules = is_array($active_modules) ? array_keys($active_modules) : [];
    
    // Get all menu items with module_name set (only if column exists)
    $menus_with_modules = [];
    $custom_menus_table = db_prefix() . 'poly_utilities_custom_menus';
    if ($CI->db->field_exists('module_name', $custom_menus_table)) {
        $CI->db->reset_query();
        $menus_with_modules = $CI->db->select('DISTINCT module_name')
                                     ->where('menu_type', $menu_type)
                                     ->where('is_custom', 0)
                                     ->where('module_name IS NOT NULL')
                                     ->where('module_name !=', '')
                                     ->get($custom_menus_table)
                                     ->result_array();
    }
    
    $menu_module_names = array_column($menus_with_modules, 'module_name');
    $menu_module_names = array_filter(array_unique($menu_module_names));
    
    // Find modules that are in database but not active
    $inactive_modules = array_diff($menu_module_names, $active_modules);
    
    // Delete menu items from inactive modules (batch delete for performance)
    if (!empty($inactive_modules)) {
        $CI->db->reset_query();
        $CI->db->where('menu_type', $menu_type);
        $CI->db->where('is_custom', 0);
        $CI->db->where_in('module_name', array_values($inactive_modules));
        $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
        $total_deleted += $CI->db->affected_rows();
    }
    
    // CRITICAL: Reset query builder at the end to prevent leftover WHERE clauses
    // from affecting subsequent database operations (especially during module deactivation)
    $CI->db->reset_query();
    
    return $total_deleted;
}

/**
 * Extract all slugs from menu items recursively
 * 
 * @param array $items Menu items
 * @return array Array of slugs
 */
function poly_extract_all_slugs($items)
{
    $slugs = [];
    
    foreach ($items as $key => $item) {
        $slug = $item['slug'] ?? $key;
        if (!empty($slug)) {
            $slugs[] = $slug;
        }
        
        if (!empty($item['children'])) {
            $slugs = array_merge($slugs, poly_extract_all_slugs($item['children']));
        }
    }
    
    return $slugs;
}

/**
 * Force sync all menu types
 * Call this on module activation or settings update
 * 
 * IMPORTANT: This function will:
 * - INSERT new menu items from system menu that don't exist in database
 * - UPDATE existing menu items (name, href, icon, position, module_name) from system menu
 * - DELETE orphaned menu items that no longer exist in system menu
 * - DELETE menu items from deactivated modules
 * 
 * This ensures database always reflects current system menu state,
 * even when developers add/remove/change routes in code.
 * 
 * @param bool $force_update Force update even if menu items already exist (default: true)
 * @param string|null $deactivated_module_name Optional: specific module that was deactivated (for optimized cleanup)
 * @return array Status for each menu type
 */
function poly_force_sync_all_menus($force_update = true, $deactivated_module_name = null)
{
    $CI =& get_instance();
    
    // Check if table exists (prevent errors during activation/deactivation)
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        return [];
    }
    
    $results = [];
    
    foreach (['sidebar', 'setup', 'clients'] as $menu_type) {
        // Reset query builder before each menu type
        $CI->db->reset_query();
        
        try {
            // Sync system menus to database
            // This will INSERT new items and UPDATE existing items (including module_name)
            poly_sync_system_menus_to_db($menu_type);
            
            // Clean up orphaned system menus (removed from code or from deactivated modules)
            $cleaned = poly_cleanup_orphaned_system_menus($menu_type, $deactivated_module_name);
            
            $results[$menu_type] = [
                'success' => true,
                'cleaned' => $cleaned
            ];
        } catch (Exception $e) {
            $results[$menu_type] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Reset query builder after each menu type to prevent state leakage
        $CI->db->reset_query();
    }
    
    // CRITICAL: Final reset to ensure no leftover query state affects subsequent operations
    // This is especially important when called during module deactivation
    $CI->db->reset_query();
    
    return $results;
}

/**
 * Force sync menu items for a specific module
 * Useful when a module adds/removes/changes routes after initial activation
 * 
 * @param string $module_name Module system name (e.g., 'poly_utilities')
 * @return array Status for each menu type
 */
function poly_force_sync_module_menus($module_name)
{
    $CI =& get_instance();
    
    // Check if table exists
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        return [];
    }
    
    // Check if module is active
    if (!$CI->app_modules->is_active($module_name)) {
        return [
            'error' => 'Module is not active',
            'module' => $module_name
        ];
    }
    
    // Force sync all menus (will detect and sync new routes from the module)
    return poly_force_sync_all_menus(true);
}

/**
 * Detect and sync new menu items that exist in system menu but not in database
 * This is useful when developers add new routes without reactivating module
 * 
 * @param string $menu_type Menu type: 'sidebar', 'setup', 'clients'
 * @return array Array of newly inserted menu items with their slugs
 */
function poly_detect_and_sync_new_menu_items($menu_type = 'sidebar')
{
    $CI =& get_instance();
    
    // Check if table exists
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        return [];
    }
    
    // Get system menus
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
    
    if (empty($system_menus)) {
        return [];
    }
    
    // Get all slugs from system menu
    $system_slugs = poly_extract_all_slugs($system_menus);
    
    // Get all slugs from database (system menus only)
    $CI->db->reset_query();
    $db_menus = $CI->db->select('slug')
                      ->where('menu_type', $menu_type)
                      ->where('is_custom', 0)
                      ->get(db_prefix() . 'poly_utilities_custom_menus')
                      ->result_array();
    
    $db_slugs = array_column($db_menus, 'slug');
    
    // Find missing slugs (in system but not in database)
    $missing_slugs = array_diff($system_slugs, $db_slugs);
    
    if (empty($missing_slugs)) {
        return []; // No new items
    }
    
    // Sync to add missing items
    poly_sync_system_menus_to_db($menu_type);
    
    return [
        'menu_type' => $menu_type,
        'missing_slugs' => array_values($missing_slugs),
        'count' => count($missing_slugs)
    ];
}

/**
 * Fix duplicate positions in menu items
 *  Ensures unique sequential positions (1, 2, 3, 4...)
 * 
 * @param string $menu_type Menu type
 * @param int|null $parent_id Parent ID (null for root level)
 * @return int Number of items fixed
 */
function poly_fix_duplicate_positions($menu_type, $parent_id = null)
{
    $CI =& get_instance();
    
    // Reset query builder to avoid conflicts
    $CI->db->reset_query();
    
    // Get all items at the same level
    $CI->db->select('id, slug, name, position');
    $CI->db->from(db_prefix() . 'poly_utilities_custom_menus');
    $CI->db->where('menu_type', $menu_type);
    $CI->db->where('disabled', 0);
    
    if ($parent_id === null) {
        $CI->db->where('parent_id IS NULL');
    } else {
        $CI->db->where('parent_id', $parent_id);
    }
    
    $CI->db->order_by('position', 'ASC');
    $CI->db->order_by('id', 'ASC'); // Secondary sort for consistency
    $query = $CI->db->get();
    
    if (!$query || $query->num_rows() === 0) {
        return 0;
    }
    
    $items = $query->result_array();
    $fixed_count = 0;
    
    //  Reassign positions sequentially
    foreach ($items as $index => $item) {
        $new_position = $index + 1;
        
        if ($item['position'] != $new_position) {
            $CI->db->where('id', $item['id']);
            $CI->db->update(db_prefix() . 'poly_utilities_custom_menus', [
                'position' => $new_position
            ]);
            
            $fixed_count++;
        }
    }
    
    return $fixed_count;
}

/**
 * Fix all duplicate positions across all menu types and levels
 * 
 * @return array Summary of fixes
 */
function poly_fix_all_duplicate_positions()
{
    $summary = [];
    
    // Fix root level for all menu types
    $menu_types = ['sidebar', 'setup', 'clients'];
    
    foreach ($menu_types as $menu_type) {
        $fixed = poly_fix_duplicate_positions($menu_type, null);
        $summary[$menu_type . '_root'] = $fixed;
        
        // Fix nested levels (level 2, 3)
        $CI =& get_instance();
        $CI->db->select('DISTINCT parent_id');
        $CI->db->from(db_prefix() . 'poly_utilities_custom_menus');
        $CI->db->where('menu_type', $menu_type);
        $CI->db->where('parent_id IS NOT NULL');
        $CI->db->where('disabled', 0);
        $query = $CI->db->get();
        
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $parent) {
                $fixed = poly_fix_duplicate_positions($menu_type, $parent['parent_id']);
                $summary[$menu_type . '_parent_' . $parent['parent_id']] = $fixed;
            }
        }
    }
    
    return $summary;
}

/**
 * Initialize default clients menu items if database is empty
 * Called on module activation or when clients menu is empty
 * 
 * @return bool Success status
 */
function poly_init_default_clients_menu()
{
    $CI =& get_instance();
    $CI->load->database();
    
    // Check if table exists (prevent errors during activation)
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        return false;
    }
    
    // Reset query builder to avoid conflicts
    $CI->db->reset_query();
    
    // Check if clients menu already has items
    $existing_count = $CI->db->where('menu_type', 'clients')
                             ->from(db_prefix() . 'poly_utilities_custom_menus')
                             ->count_all_results();
    
    if ($existing_count > 0) {
        // Already initialized
        return true;
    }
    
    // Default clients menu items from PerfexCRM
    // Store language keys directly (not translated text)
    // This allows automatic translation based on current language
    $default_clients_menus = [
        ['slug' => 'projects', 'name' => 'clients_nav_projects', 'href' => site_url('clients/projects'), 'position' => 1],
        ['slug' => 'invoices', 'name' => 'clients_nav_invoices', 'href' => site_url('clients/invoices'), 'position' => 2],
        ['slug' => 'contracts', 'name' => 'clients_nav_contracts', 'href' => site_url('clients/contracts'), 'position' => 3],
        ['slug' => 'estimates', 'name' => 'clients_nav_estimates', 'href' => site_url('clients/estimates'), 'position' => 4],
        ['slug' => 'proposals', 'name' => 'clients_nav_proposals', 'href' => site_url('clients/proposals'), 'position' => 5],
        ['slug' => 'subscriptions', 'name' => 'subscriptions', 'href' => site_url('clients/subscriptions'), 'position' => 6],
        ['slug' => 'support', 'name' => 'clients_nav_support', 'href' => site_url('clients/tickets'), 'position' => 7]
    ];
    
    // Insert default menus
    foreach ($default_clients_menus as $menu) {
        // Convert translated text to language key (if _l() was called before)
        // This handles both cases: language key or translated text
        $menu_name = $menu['name'];
        $language_key = poly_get_original_language_key($menu_name, $menu['slug']);
        
        $insert_data = [
            'menu_type' => 'clients',
            'slug' => $menu['slug'],
            'name' => $language_key, // Store language key, not translated text
            'href' => $menu['href'],
            'position' => $menu['position'],
            'level' => 1,
            'parent_id' => null,
            'parent_slug' => 'root',
            'disabled' => 0,
            'is_custom' => 0, // System menu
            'type' => 'default',
            'target' => '_self',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $CI->db->insert(db_prefix() . 'poly_utilities_custom_menus', $insert_data);
    }
    
    return true;
}
