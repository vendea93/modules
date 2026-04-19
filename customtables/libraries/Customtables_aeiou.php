<?php

defined('BASEPATH') || exit('No direct script access allowed');
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../third_party/node.php';
use Firebase\JWT\JWT as customtables_JWT;
use Firebase\JWT\Key as customtables_Key;
use WpOrg\Requests\Requests as customtables_Requests;

class Customtables_aeiou {
    public static function getPurchaseData($code) {
        $givemecode = customtables_Requests::get(CT_GIVE_ME_CODE)->body;
        $bearer = get_instance()->session->has_userdata('bearer') ? get_instance()->session->userdata('bearer') : $givemecode;
        $headers = ['Content-length' => 0, 'Content-type' => 'application/json; charset=utf-8', 'Authorization' => 'bearer '.$bearer];
        $verify_url = 'https://api.envato.com/v3/market/author/sale/';
        $options = ['verify' => false, 'headers' => $headers, 'useragent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'];
        $response = customtables_Requests::get($verify_url.'?code='.$code, $headers, $options);

        return ($response->success) ? json_decode($response->body) : false;
    }

    public static function verifyPurchase($code) {
        $verify_obj = self::getPurchaseData($code);

        return ((false === $verify_obj) || !is_object($verify_obj) || isset($verify_obj->error) || !isset($verify_obj->sold_at) || ('' == $verify_obj->supported_until)) ? $verify_obj : null;
    }

    public function validatePurchase($module_name) {
        $module = get_instance()->app_modules->get($module_name);
        $verified = false;
        $verification_id = get_option($module_name.'_verification_id');

        if (!empty($verification_id)) {
            $verification_id = base64_decode($verification_id);
        }

        $id_data = explode('|', $verification_id);
        $token = get_option($module_name.'_product_token');

        if (4 == count($id_data)) {
            $verified = !empty($token);
            $data = customtables_JWT::decode($token, new customtables_Key($id_data[3], 'HS512'));
            $verified = !empty($data)
                && basename((string) $module['headers']['uri']) == $data->item_id
                && $data->item_id == $id_data[0]
                && $data->buyer == $id_data[2]
                && $data->purchase_code == $id_data[3];

            $seconds = $data->check_interval ?? 0;
            $last_verification = (int) get_option($module_name.'_last_verification');

            if (!empty($seconds) && time() > ($last_verification + $seconds)) {
                $verified = false;
                try {
                    $validate_url = preg_replace('#(http[s]?://[^/]+(?:/[^/]+)*/admin/[^/]+)(?:/.*)?#', '$1', current_full_url());
                    $headers = ['Accept' => 'application/json', 'Authorization' => $token];
                    $request = customtables_Requests::post(CT_VAL_PROD_POINT, $headers, json_encode(['verification_id' => $verification_id, 'item_id' => basename((string) $module['headers']['uri']), 'activated_domain' => $validate_url]));
                    $result = json_decode($request->body);
                    $verified = (200 == $request->status_code && !empty($result->valid));
                } catch (Exception) {
                    $verified = true;
                }

                update_option($module_name.'_last_verification', time());
            }

            if (empty($token) || !$verified) {
                $last_verification = (int) get_option($module_name.'_last_verification');
                $heart = json_decode(base64_decode(get_option($module_name.'_heartbeat')));
                $verified = (!empty($heart) && ($last_verification + (168 * (3000 + 600))) > time()) ?? false;
            }

            if (!$verified) {
                $ctOptions = get_activated_table_list();
                write_file(TEMP_FOLDER . $ctOptions['custom_table'] . '.lic', '');
                get_instance()->app_modules->deactivate($module_name);
            }

            return $verified;
        }
        return null;
    }
}
