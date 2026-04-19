<?php
defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('reputation_init',REPUTATION_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', REPUTATION_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', REPUTATION_MODULE_NAME.'_predeactivate');
/**
     * [new_html_entity_decode description]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
if (!function_exists('new_html_entity_decode')) {
    
    function new_html_entity_decode($str){
        return html_entity_decode($str ?? '');
    }
}

/**
 * { acc_check_csrf_protection }
 *
 * @return     string  (  )
 */
if (!function_exists('acc_check_csrf_protection')) {
function acc_check_csrf_protection()
{
    if(config_item('csrf_protection')){
        return 'true';
    }
    return 'false';
}
}

/**
 * Determines whether the specified identifier is empty vendor company.
 *
 * @param      <type>   $id     The identifier
 *
 * @return     boolean  True if the specified identifier is empty vendor company, False otherwise.
 */
if (!function_exists('acc_is_empty_vendor_company')) {
function acc_is_empty_vendor_company($id)
{
    $CI = & get_instance();
    $CI->db->select('company');
    $CI->db->from(db_prefix() . 'pur_vendor');
    $CI->db->where('userid', $id);
    $row = $CI->db->get()->row();
    if ($row) {
        if ($row->company == '') {
            return true;
        }

        return false;
    }

    return true;
}
}

/**
 * get status modules wh
 * @param  string $module_name 
 * @return boolean             
 */
if(!function_exists('acc_get_status_modules')){
    function acc_get_status_modules($module_name){
        $CI             = &get_instance();

        $sql = 'select * from '.db_prefix().'modules where module_name = "'.$module_name.'" AND active =1 ';
        $module = $CI->db->query($sql)->row();
        if($module){
            return true;
        }else{
            return false;
        }
    }
}

/**
 * [rep_get_facebook_config]
 * @return [array]
 */
function rep_get_facebook_config(){
    $config = [
          'app_id' => get_option('rep_facebook_app_id'),
          'app_secret' => get_option('rep_facebook_app_secret'),
          'default_graph_version' => get_option('rep_facebook_graph_version'),
        ];

    return $config;
}

/**
 * Gets the base workspace identifier.
 *
 * @param        $account_id  The account identifier
 */
function rep_get_base_workspace_id(){

    $staff_id = get_staff_user_id();

    $CI   = & get_instance();
    $CI->db->where('staffid', $staff_id);
    $staff = $CI->db->get(db_prefix().'staff')->row();
    if($staff && is_numeric($staff->rep_base_project_id) && $staff->rep_base_project_id > 0){

        $CI->db->where('id', $staff->rep_base_project_id);
        $workspace = $CI->db->get(db_prefix().'rep_projects')->row();
        if($workspace){
            return $staff->rep_base_project_id;
        }
    }
    return 0;
}


/**
 * [rep_get_tiktok_config]
 * @return [array]
 */
function rep_get_tiktok_config(){
    $config = [
          'client_key' => get_option('rep_tiktok_client_key'),
          'client_secret' => get_option('rep_tiktok_client_secret'),
          'api_domain' => 'https://open.tiktokapis.com/v2',
        ];

    return $config;
}

/**
 * [rep_get_instagram_config]
 * @return [type]
 */
function rep_get_instagram_config(){
    $config = [
          'app_id' => get_option('rep_instagram_app_id'),
          'app_secret' => get_option('rep_instagram_app_secret'),
          'default_graph_version' => get_option('rep_instagram_graph_version'),
          'api_domain' => 'https://graph.instagram.com/',
        ];

    return $config;
}

/**
 * [rep_get_twitter_config]
 * @return [array]
 */
function rep_get_twitter_config(){
    $config = [
          'client_id' => get_option('rep_twitter_client_id'),
          'client_secret' => get_option('rep_twitter_client_secret'),
          'api_domain' => 'https://api.twitter.com',
        ];

    return $config;
}

/**
 * [rep_get_youtube_config]
 * @return [array]
 */
function rep_get_youtube_config(){

    $config = [
          'client_id' => get_option('rep_youtube_client_id'),
          'client_secret' => get_option('rep_youtube_client_secret'),
          'api_domain' => 'https://www.googleapis.com/youtube/v3',
        ];

    return $config;
}


/**
 * [rep_get_account_ids_by_base_workspace]
 * @param  [string]  $type      
 * @param  boolean $return_ids
 * @return [array]             
 */
function rep_get_account_ids_by_base_workspace($type, $return_ids = false){
    $CI   = & get_instance();
    $workspace_id = rep_get_base_workspace_id();
    $CI->db->where('project_id', $workspace_id);
    $CI->db->where('type', $type);
    $CI->db->where('active', 1);
    $accounts = $CI->db->get(db_prefix().'rep_accounts')->result_array();

    if($accounts){
        if($return_ids){
            $account_ids = [0];
            foreach ($accounts as $key => $value) {
                $account_ids[] = $value['id'];
            }

            return implode(',', $account_ids);
        }
    }
    
    return $accounts;
}

function get_google_new_country($ceid = ''){
    $list = [
      [
        "gl" => "US",
        "hl" => "en-US",
        "ceid" => "US:en-US",
        "country_name" => "United States"
      ],
      [
        "gl" => "GB",
        "hl" => "en-GB",
        "ceid" => "GB:en-GB",
        "country_name" => "United Kingdom"
      ],
      [
        "gl" => "AU",
        "hl" => "en-AU",
        "ceid" => "AU:en-AU",
        "country_name" => "Australia"
      ],
      [
        "gl" => "CA",
        "hl" => "en-CA",
        "ceid" => "CA:en-CA",
        "country_name" => "Canada"
      ],
      [
        "gl" => "IN",
        "hl" => "en-IN",
        "ceid" => "IN:en-IN",
        "country_name" => "India"
      ],
      [
        "gl" => "VN",
        "hl" => "vi",
        "ceid" => "VN:vi",
        "country_name" => "Vietnam"
      ],
      [
        "gl" => "JP",
        "hl" => "ja",
        "ceid" => "JP:ja",
        "country_name" => "Japan"
      ],
      [
        "gl" => "KR",
        "hl" => "ko",
        "ceid" => "KR:ko",
        "country_name" => "South Korea"
      ],
      [
        "gl" => "CN",
        "hl" => "zh-CN",
        "ceid" => "CN:zh-CN",
        "country_name" => "China"
      ],
      [
        "gl" => "TW",
        "hl" => "zh-TW",
        "ceid" => "TW:zh-TW",
        "country_name" => "Taiwan"
      ],
      [
        "gl" => "FR",
        "hl" => "fr",
        "ceid" => "FR:fr",
        "country_name" => "France"
      ],
      [
        "gl" => "DE",
        "hl" => "de",
        "ceid" => "DE:de",
        "country_name" => "Germany"
      ],
      [
        "gl" => "IT",
        "hl" => "it",
        "ceid" => "IT:it",
        "country_name" => "Italy"
      ],
      [
        "gl" => "ES",
        "hl" => "es",
        "ceid" => "ES:es",
        "country_name" => "Spain"
      ],
      [
        "gl" => "RU",
        "hl" => "ru",
        "ceid" => "RU:ru",
        "country_name" => "Russia"
      ],
      [
        "gl" => "BR",
        "hl" => "pt-BR",
        "ceid" => "BR:pt-BR",
        "country_name" => "Brazil"
      ],
      [
        "gl" => "AR",
        "hl" => "es-419",
        "ceid" => "AR:es-419",
        "country_name" => "Argentina"
      ],
      [
        "gl" => "ID",
        "hl" => "id",
        "ceid" => "ID:id",
        "country_name" => "Indonesia"
      ],
      [
        "gl" => "TH",
        "hl" => "th",
        "ceid" => "TH:th",
        "country_name" => "Thailand"
      ],
      [
        "gl" => "SA",
        "hl" => "ar",
        "ceid" => "SA:ar",
        "country_name" => "Saudi Arabia"
      ]
    ];

    if($ceid != ''){
        foreach ($list as $item) {
            if ($item['ceid'] === $ceid) {
                return $item;
            }
        }

        return [
            "gl" => "US",
            "hl" => "en-US",
            "ceid" => "US:en-US",
            "country_name" => "United States"
          ];
    }

    return $list;
}

/**
 * Function will render tags as html version to show to the user
 * @param  string $tags
 * @return string
 */
function rep_render_tags($tags)
{
    $tags_html = '';

    if (!is_array($tags)) {
       $tags = empty($tags) ? [] : explode(',', $tags);
    }

    $tags = array_filter($tags, function ($value) {
        return $value !== '';
    });

    if (count($tags) > 0) {
        $CI = &get_instance();

        $tags_html .= '<div class="tags-labels">';
        $i   = 0;
        $len = count($tags);
        foreach ($tags as $tag) {
            $tag_id  = 0;
            $tag_row = $CI->app_object_cache->get('tag-id-by-name-' . $tag);
            if (!$tag_row) {
                $tag_row = get_tag_by_name($tag);

                if ($tag_row) {
                    $CI->app_object_cache->add('tag-id-by-name-' . $tag, $tag_row->id);
                }
            }

            if ($tag_row) {
                $tag_id = is_object($tag_row) ? $tag_row->id : $tag_row;
            }

            $tags_html .= '<span class="label label-tag tag-id-' . $tag_id . '">' . e($tag) . '</span>';
            $i++;
        }
        $tags_html .= '</div>';
    }

    return $tags_html;
}

/**
 * Determines whether the specified identifier is empty vendor company.
 *
 * @param      <type>   $id     The identifier
 *
 * @return     boolean  True if the specified identifier is empty vendor company, False otherwise.
 */
function rep_is_empty_vendor_company($id)
{
    $CI = & get_instance();
    $CI->db->select('company');
    $CI->db->from(db_prefix() . 'pur_vendor');
    $CI->db->where('userid', $id);
    $row = $CI->db->get()->row();
    if ($row) {
        if ($row->company == '') {
            return true;
        }

        return false;
    }

    return true;
}

/**
 * Determines if vendor admin.
 *
 * @param      string   $id        The identifier
 * @param      string   $staff_id  The staff identifier
 *
 * @return     integer  True if vendor admin, False otherwise.
 */
function rep_is_vendor_admin($id, $staff_id = '')
{
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $CI       = &get_instance();
    $cache    = $CI->app_object_cache->get($id . '-is-vendor-admin-' . $staff_id);

    if ($cache) {
        return $cache['retval'];
    }

    $total = total_rows(db_prefix() . 'pur_vendor_admin', [
        'vendor_id' => $id,
        'staff_id'    => $staff_id,
    ]);

    $retval = $total > 0 ? true : false;
    $CI->app_object_cache->add($id . '-is-vendor-admin-' . $staff_id, ['retval' => $retval]);

    return $retval;
}


/**
 * Gets the vendor company name.
 *
 * @param      string   $userid                 The userid
 * @param      boolean  $prevent_empty_company  The prevent empty company
 *
 * @return     string   The vendor company name.
 */
function rep_get_vendor_company_name($userid, $prevent_empty_company = false)
{
    if ($userid !== '') {
        $_userid = $userid;
    }
    $CI = & get_instance();

    $client = $CI->db->select('company')
    ->where('userid', $_userid)
    ->from(db_prefix() . 'pur_vendor')
    ->get()
    ->row();
    if ($client) {
        return $client->company;
    }

    return '';
}
