<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Form_sync_license
{
    const ENVATO_API_URL = 'https://api.envato.com/v3/market/author/sale';
    const ENVATO_TOKEN = '4PdTCiwIlVXy7BmpeqKIVL8PzlTkugM6';
    const ENVATO_ITEM_ID = '61372175';
    const CHECK_INTERVAL_DAYS = 4;
    
    protected $CI;
    
    public function __construct()
    {
        $this->CI = &get_instance();
    }
    
    private function _opt($key)
    {
        $x = [
            'valid' => 'fs_lv_2024',
            'last_check' => 'fs_lc_2024',
            'purchase_code' => 'fs_pc_2024',
            'buyer' => 'fs_lb_2024',
            'type' => 'fs_lt_2024',
            'sold_at' => 'fs_lsa_2024',
            'supported_until' => 'fs_lsu_2024',
            'validated_at' => 'fs_lva_2024'
        ];
        if (!isset($x[$key])) {
            log_message('error', '[FormSync] Invalid option key requested: ' . $key);
            return '';
        }
        return $x[$key];
    }
    
    private function _migrate()
    {
        try {
            $old = [
                'form_sync_license_valid' => $this->_opt('valid'),
                'form_sync_license_last_check' => $this->_opt('last_check'),
                'form_sync_purchase_code' => $this->_opt('purchase_code'),
                'form_sync_license_buyer' => $this->_opt('buyer'),
                'form_sync_license_type' => $this->_opt('type'),
                'form_sync_license_sold_at' => $this->_opt('sold_at'),
                'form_sync_license_supported_until' => $this->_opt('supported_until'),
                'form_sync_license_validated_at' => $this->_opt('validated_at')
            ];
            foreach ($old as $o => $n) {
                if (empty($n)) {
                    continue;
                }
                $v = get_option($o);
                if (!empty($v) || $v === '0' || $v === '1') {
                    update_option($n, $v);
                    delete_option($o);
                }
            }
        } catch (Exception $e) {
            log_message('error', '[FormSync] Migration error: ' . $e->getMessage());
        }
    }
    
    public function validatePurchaseCode($purchase_code)
    {
        $this->_migrate();
        $purchase_code = trim($purchase_code);
        
        if (empty($purchase_code)) {
            return ['success' => false, 'message' => _l('form_sync_license_code_required'), 'data' => null];
        }
        
        if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $purchase_code)) {
            return ['success' => false, 'message' => _l('form_sync_license_invalid_format'), 'data' => null];
        }
        
        $api_result = $this->callEnvatoApi($purchase_code);
        
        if (!$api_result['success']) {
            return $api_result;
        }
        
        $sale_data = $api_result['data'];
        
        if (!isset($sale_data['item']['id'])) {
            return ['success' => false, 'message' => _l('form_sync_license_invalid_response'), 'data' => null];
        }
        
        $item_id = (string) $sale_data['item']['id'];
        if ($item_id !== self::ENVATO_ITEM_ID) {
            log_message('warning', '[FormSync] Purchase code validated but for different item. Expected: ' . self::ENVATO_ITEM_ID . ', Got: ' . $item_id);
            return ['success' => false, 'message' => _l('form_sync_license_wrong_item'), 'data' => null];
        }
        
        $license_data = [
            'purchase_code' => $purchase_code,
            'item_id' => $item_id,
            'item_name' => isset($sale_data['item']['name']) ? $sale_data['item']['name'] : 'FormSync',
            'buyer' => isset($sale_data['buyer']) ? $sale_data['buyer'] : 'Unknown',
            'license_type' => isset($sale_data['license']) ? $sale_data['license'] : 'Regular License',
            'sold_at' => isset($sale_data['sold_at']) ? $sale_data['sold_at'] : null,
            'supported_until' => isset($sale_data['supported_until']) ? $sale_data['supported_until'] : null,
            'validated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->_x2($license_data);
        
        return ['success' => true, 'message' => _l('form_sync_license_valid'), 'data' => $license_data];
    }
    
    private function callEnvatoApi($purchase_code)
    {
        $url = self::ENVATO_API_URL . '?code=' . urlencode($purchase_code);
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . self::ENVATO_TOKEN,
                'User-Agent: FormSync Module License Validator'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false) {
            log_message('error', '[FormSync] Envato API cURL error: ' . $curl_error);
            return ['success' => false, 'message' => _l('form_sync_license_connection_error'), 'data' => null];
        }
        
        $data = json_decode($response, true);
        
        if ($http_code === 404) {
            return ['success' => false, 'message' => _l('form_sync_license_not_found'), 'data' => null];
        }
        
        if ($http_code === 403) {
            log_message('error', '[FormSync] Envato API forbidden - token may be invalid');
            return ['success' => false, 'message' => _l('form_sync_license_api_error'), 'data' => null];
        }
        
        if ($http_code === 429) {
            return ['success' => false, 'message' => _l('form_sync_license_rate_limited'), 'data' => null];
        }
        
        if ($http_code !== 200) {
            log_message('error', '[FormSync] Envato API unexpected status: ' . $http_code . ' - Response: ' . $response);
            return ['success' => false, 'message' => _l('form_sync_license_api_error'), 'data' => null];
        }
        
        if (!is_array($data)) {
            log_message('error', '[FormSync] Envato API invalid JSON response');
            return ['success' => false, 'message' => _l('form_sync_license_invalid_response'), 'data' => null];
        }
        
        return ['success' => true, 'message' => 'Valid', 'data' => $data];
    }
    
    private function _x2($license_data)
    {
        update_option($this->_opt('purchase_code'), $license_data['purchase_code']);
        update_option($this->_opt('valid'), '1');
        update_option($this->_opt('buyer'), $license_data['buyer']);
        update_option($this->_opt('type'), $license_data['license_type']);
        update_option($this->_opt('sold_at'), $license_data['sold_at']);
        update_option($this->_opt('supported_until'), $license_data['supported_until']);
        update_option($this->_opt('validated_at'), $license_data['validated_at']);
        $check_date = date('Y-m-d H:i:s', time() + (self::CHECK_INTERVAL_DAYS * 86400));
        update_option($this->_opt('last_check'), $check_date);
        log_message('info', '[FormSync] License validated successfully for buyer: ' . $license_data['buyer']);
    }
    
    public function isLicenseValid()
    {
        try {
            $this->_migrate();
            $opt_name = $this->_opt('valid');
            if (empty($opt_name)) {
                return false;
            }
            return get_option($opt_name) === '1';
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error in isLicenseValid: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getLicenseDetails()
    {
        $this->_migrate();
        if (!$this->isLicenseValid()) {
            return null;
        }
        
        return [
            'purchase_code' => get_option($this->_opt('purchase_code')),
            'buyer' => get_option($this->_opt('buyer')),
            'license_type' => get_option($this->_opt('type')),
            'sold_at' => get_option($this->_opt('sold_at')),
            'supported_until' => get_option($this->_opt('supported_until')),
            'validated_at' => get_option($this->_opt('validated_at'))
        ];
    }
    
    public function clearLicenseData()
    {
        $this->_migrate();
        delete_option($this->_opt('purchase_code'));
        update_option($this->_opt('valid'), '0');
        delete_option($this->_opt('buyer'));
        delete_option($this->_opt('type'));
        delete_option($this->_opt('sold_at'));
        delete_option($this->_opt('supported_until'));
        delete_option($this->_opt('validated_at'));
        delete_option($this->_opt('last_check'));
        log_message('info', '[FormSync] License data cleared');
    }
    
    public function maskPurchaseCode($code)
    {
        if (strlen($code) < 12) {
            return str_repeat('*', strlen($code));
        }
        return substr($code, 0, 4) . str_repeat('*', strlen($code) - 8) . substr($code, -4);
    }
    
    public function isSupportActive()
    {
        $this->_migrate();
        $supported_until = get_option($this->_opt('supported_until'));
        if (empty($supported_until)) {
            return null;
        }
        return strtotime($supported_until) > time();
    }
    
    public function performPeriodicCheck()
    {
        $this->_migrate();
        $this->_x1();
    }
    
    private function _x1()
    {
        $x3_result = $this->_x3();
        if (!$x3_result) {
            return;
        }
        
        $x4_result = $this->_x4();
        if (!$x4_result) {
            return;
        }
        
        $x5 = get_option($this->_opt('purchase_code'));
        
        if (empty($x5)) {
            $this->_x7();
            return;
        }
        
        $x8 = $this->_x9($x5);
        
        if ($x8['_x10']) {
            $this->_x11();
        } elseif ($x8['_x12']) {
            $this->_x7();
        }
    }
    
    private function _x3()
    {
        return get_option($this->_opt('valid')) === '1';
    }
    
    private function _x4()
    {
        $x13 = get_option($this->_opt('last_check'));
        
        if (empty($x13)) {
            $this->_x11();
            return false;
        }
        
        $x14 = self::CHECK_INTERVAL_DAYS * 86400;
        $x15 = strtotime($x13);
        $x16 = time() - $x15;
        
        return $x16 >= $x14;
    }
    
    private function _x9($x5)
    {
        $x17 = self::ENVATO_API_URL . '?code=' . urlencode($x5);
        
        $x18 = curl_init();
        
        curl_setopt_array($x18, [
            CURLOPT_URL => $x17,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . self::ENVATO_TOKEN,
                'User-Agent: FormSync Module License Validator'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $x19 = curl_exec($x18);
        $x20 = curl_getinfo($x18, CURLINFO_HTTP_CODE);
        
        curl_close($x18);
        
        if ($x19 === false) {
            return ['_x10' => false, '_x12' => false];
        }
        
        if ($x20 === 404) {
            return ['_x10' => false, '_x12' => true];
        }
        
        if ($x20 === 403 || $x20 === 429) {
            return ['_x10' => false, '_x12' => false];
        }
        
        if ($x20 !== 200) {
            return ['_x10' => false, '_x12' => false];
        }
        
        $x21 = json_decode($x19, true);
        
        if (!is_array($x21) || !isset($x21['item']['id'])) {
            return ['_x10' => false, '_x12' => false];
        }
        
        $x22 = (string) $x21['item']['id'];
        if ($x22 !== self::ENVATO_ITEM_ID) {
            return ['_x10' => false, '_x12' => true];
        }
        
        return ['_x10' => true, '_x12' => false];
    }
    
    private function _x11()
    {
        update_option($this->_opt('last_check'), date('Y-m-d H:i:s'));
    }
    
    private function _x7()
    {
        update_option($this->_opt('valid'), '0');
        delete_option($this->_opt('buyer'));
        delete_option($this->_opt('type'));
        delete_option($this->_opt('sold_at'));
        delete_option($this->_opt('supported_until'));
        delete_option($this->_opt('validated_at'));
        delete_option($this->_opt('last_check'));
    }
    
    public function revalidateStoredCode()
    {
        $stored_code = get_option($this->_opt('purchase_code'));
        if (empty($stored_code)) {
            return ['success' => false, 'message' => _l('form_sync_license_no_stored_code'), 'data' => null];
        }
        return $this->validatePurchaseCode($stored_code);
    }
    
    public function getStoredPurchaseCode()
    {
        try {
            $this->_migrate();
            $opt_name = $this->_opt('purchase_code');
            if (empty($opt_name)) {
                return '';
            }
            $code = get_option($opt_name);
            return !empty($code) ? $code : '';
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error in getStoredPurchaseCode: ' . $e->getMessage());
            return '';
        }
    }
    
    public function getLastCheckDate()
    {
        $this->_migrate();
        return get_option($this->_opt('last_check')) ?: null;
    }
    
    public function getNextCheckDate()
    {
        $this->_migrate();
        $last_check = $this->getLastCheckDate();
        if (empty($last_check)) {
            return null;
        }
        $next_check_time = strtotime($last_check) + (self::CHECK_INTERVAL_DAYS * 86400);
        return date('Y-m-d H:i:s', $next_check_time);
    }
}
