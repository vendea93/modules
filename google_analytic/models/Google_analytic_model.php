<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * This class describes a Social analytic model.
 */

class Google_analytic_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * [update_setting]
     * @param  [array] $data
     * @return [boolean]      
     */
    public function update_setting($data){
        $affectedRows = 0;

        foreach ($data as $key => $value) {
            $this->db->where('name', $key);
            $this->db->update(db_prefix() . 'options', [
                    'value' => $value,
                ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            return true;
        }
        return false;
    }

    /**
     * Add new workspace
     * @param mixed $data All $_POST data
     * @return boolean
     */
    public function add_workspace($data)
    {
        $data['addedfrom'] = get_staff_user_id();
        $data['dateadded'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'ga_workspaces', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    /**
     * update workspace
     * @param array $data
     * @param  integer $id 
     * @return boolean
     */
    public function update_workspace($data, $id){
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'ga_workspaces', $data);
       
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * delete workspace
     * @param integer $id
     * @return boolean
     */
    public function delete_workspace($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ga_workspaces');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * [set_default_workspace]
     * @param [integer] $id workspace id
     */
    public function set_default_workspace($id){
        $staffid = get_staff_user_id();
        $this->db->where('staffid', $staffid);
        $this->db->update(db_prefix().'staff', ['ga_base_workspace_id' => $id]);
        
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Get workspace
     * @param  mixed $id workspace id (Optional)
     * @return mixed     object or array
     */
    public function get_workspace($id = '', $type = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'ga_workspaces')->row();
        }

        if ($type != '') {
            $this->db->where('type', $type);
        }

        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'ga_workspaces')->result_array();
    }

    /**
     * Get customers contacts
     * @param  mixed $customer_id
     * @param  array $where       perform where query
     * @param  array $whereIn     perform whereIn query
     * @return array
     */
    public function get_contacts($customer_id = '', $where = [], $whereIn = [])
    {
        $this->db->where(db_prefix() . 'contacts.active', 1);
        $this->db->where($where);
        if ($customer_id != '') {
            $this->db->where('userid', $customer_id);
        }

        foreach ($whereIn as $key => $values) {
            if (is_string($key) && is_array($values)) {
                $this->db->where_in($key, $values);
            }
        }

        $this->db->order_by('is_primary', 'DESC');
        $this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix() . 'contacts.userid','left');

        return $this->db->get(db_prefix() . 'contacts')->result_array();
    }

    /**
     * Add workspace_member
     * @param mixed $data All $_POST data
     * @return boolean
     */
    public function add_workspace_member($data)
    {
        $data_insert = [];

        $this->db->where('type', $data['type']);
        $this->db->where('workspace_id', $data['workspace_id']);
        $ga_workspace_members = $this->db->get(db_prefix() . 'ga_workspace_members')->result_array();
        $ga_workspace_members_list = [];
        foreach ($ga_workspace_members as $member) {
            $ga_workspace_members_list[] = $member['member_id'];
        }

        foreach ($data['members'] as $member_id) {
            if(in_array($member_id, $ga_workspace_members_list)){
                continue;
            }

            $node = [];
            $node['addedfrom'] = get_staff_user_id();
            $node['dateadded'] = date('Y-m-d H:i:s');
            $node['workspace_id'] = $data['workspace_id'];
            $node['type'] = $data['type'];
            $node['member_id'] = $member_id;

            $data_insert[] = $node;
        }
        
        if(count($data_insert) > 0){
            $affectedRows = $this->db->insert_batch(db_prefix().'ga_workspace_members', $data_insert);
                
            if ($affectedRows > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * delete workspace member
     * @param integer $id
     * @return boolean
     */
    public function delete_workspace_member($id)
    {
        $this->db->where('id', $id);
        $member = $this->db->get(db_prefix() . 'ga_workspace_members')->row();

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ga_workspace_members');
        if ($this->db->affected_rows() > 0) {
            if($member->type == 'staff'){
                $this->db->where('staffid', $member->member_id);
                $this->db->where('ga_base_workspace_id', $member->workspace_id);
                $this->db->update(db_prefix() . 'staff', ['ga_base_workspace_id' => 0]);
            }

            return true;
        }
        return false;
    }

    /**
     * add accounts
     * @param array $data
     * @param  integer $id 
     * @return boolean
     */
    public function add_account($data){

        $data['workspace_id'] = ga_get_base_workspace_id();
        $data['active'] = 1;
        $data['addedfrom'] = get_staff_user_id();
        $data['dateadded'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix().'ga_accounts', $data);
        
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return true;
        }

        return false;
    }

    /**
     * update accounts
     * @param array $data
     * @param  integer $id 
     * @return boolean
     */
    public function update_account($data, $id){
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'ga_accounts', $data);
       
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * delete accounts
     * @param integer $id
     * @return boolean
     */
    public function delete_account($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'ga_accounts');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get accounts
     * @param  mixed $id accounts id (Optional)
     * @return mixed     object or array
     */
    public function get_accounts($id = '', $type = '')
    {
        $workspace_id = ga_get_base_workspace_id();
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'ga_accounts')->row();
        }

        $this->db->where('workspace_id', $workspace_id);
        if ($type != '') {
            $this->db->where('type', $type);
        }

            $this->db->where('active', 1);
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'ga_accounts')->result_array();
    }

    /**
     * [account_connect_save]
     * @param  [array] $data       
     * @param  [integer] $account_id account id
     * @return [boolean]             
     */
    public function account_connect_save($data, $account_id)
    {
        $this->db->where('id', $account_id);
        $this->db->update(db_prefix().'ga_accounts', 
            $data
        );

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * [get_google_analytic_table_data]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_google_analytic_table_data($data_filter){
        
        $account = $this->get_accounts($data_filter['account_id']);

        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setAccessToken($account->access_token);
        
        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $token_result = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            $data_update = [
                'access_token' => $token_result['access_token'],
                'expires_in' => time() + $token_result['expires_in'],
            ];

            $this->account_connect_save($data_update, $account->id);
        }

        $analytics = new Google_Service_AnalyticsData($client);

        $dimensions = $this->get_dimensions($data_filter);
        $metrics = [];
        foreach ($data_filter['metrics'] as $k => $metric) {
            if($k >= 10){
                break;
            }
            $metrics[] = ['name' => $metric];
        }

        $reportRequest_arr = [
                'dateRanges' => [ 
                    [
                        'startDate' => to_sql_date($data_filter['from_date']), 
                        'endDate' => to_sql_date($data_filter['to_date'])
                    ], 
                ], 
                "dimensions" => $dimensions['dimensions'],
                'metrics' => $metrics, 
            ];

        if(isset($dimensions['dimensionFilter'])){
            $reportRequest_arr["dimensionFilter"] = $dimensions['dimensionFilter'];
        }

        $reportRequest = new Google_Service_AnalyticsData_RunReportRequest($reportRequest_arr);
        $response = $analytics->properties->runReport('properties/'.$account->page_id, $reportRequest);

        
        $metricHeaders = $response->getMetricHeaders();
        $table_data = [];
        foreach ($response->getRows() as $row) { 
            $rows = [];

            foreach ($row->getDimensionValues() as $dimensionValue) {
                if($dimensionValue->getValue() != ''){
                    $rows[] = $dimensionValue->getValue();
                }else{
                    $rows[] = _l('not_set');
                }
            }

            foreach ($row->getMetricValues() as $k => $metricValue) {
                $value = $metricValue->getValue();
                switch ($metricHeaders[$k]['type']) {
                    case 'TYPE_SECONDS':
                        $value = $this->formatTime($value);
                        break;
                    case 'TYPE_INTEGER':
                        $value = number_format($value);
                        break;
                    case 'TYPE_FLOAT':
                        if (!(strpos($metricHeaders[$k]['name'], 'Rate') === false)) {
                            $value = $this->formatToPercentage($value);
                        }else{
                            $value = number_format($value, 2);
                        }
                        break;
                    case 'TYPE_CURRENCY':
                        $value = app_format_money($value, '');;
                        break;
                    
                    default:
                        break;
                }
                $rows[] = $value;
            } 

            $table_data[] = $rows;
        }    

        return ['table_data' => $table_data, 'dimensionName' => $dimensions['dimensionName']];
    }

    /**
     * [formatTime]
     * @param  [integer] $seconds
     * @return [string]         
     */
    function formatTime($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $result = "";
        if ($hours > 0) {
            $result .= "{$hours}h";
        }

        if ($minutes > 0) {
            $result .= ($result ? " " : "") . "{$minutes}m";
        }

        if ($remainingSeconds > 0 || $result == "") {
            $result .= ($result ? " " : "") . "{$remainingSeconds}s";
        }

        return $result;
    }

    /**
     * [formatToPercentage]
     * @param  [float] $decimal
     * @return [string]         
     */
    function formatToPercentage($decimal) {
        return number_format($decimal * 100, 2) . '%';
    }

    /**
     * [get_google_analytic_data ]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_google_analytic_data($data_filter){
        $account = $this->get_accounts($data_filter['account_id']);

        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setAccessToken($account->access_token);
        
        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $token_result = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            $data_update = [
                'access_token' => $token_result['access_token'],
                'expires_in' => time() + $token_result['expires_in'],
            ];

            $this->account_connect_save($data_update, $account->id);
        }

        $analytics = new Google_Service_AnalyticsData($client);

        $dimensions = $this->get_dimensions($data_filter);
        $metrics = [];
        foreach ($data_filter['metrics'] as $k => $metric) {
            if($k >= 10){
                break;
            }
            $metrics[] = ['name' => $metric];
        }

        $reportRequest_arr = [
                'dateRanges' => [ 
                    [
                        'startDate' => to_sql_date($data_filter['from_date']), 
                        'endDate' => to_sql_date($data_filter['to_date'])
                    ], 
                ], 
                "dimensions" => $dimensions['dimensions'],
                'metrics' => $metrics, 
            ];

        if(isset($dimensions['dimensionFilter'])){
            $reportRequest_arr["dimensionFilter"] = $dimensions['dimensionFilter'];
        }

        $reportRequest = new Google_Service_AnalyticsData_RunReportRequest($reportRequest_arr);
        $response = $analytics->properties->runReport('properties/'.$account->page_id, $reportRequest);
        
        return $response;
    }

    /**
     * [get_dimensions ]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_dimensions($data_filter){
        $dimensions = [
            'dimensions' => [], 
            'dimensionName' => '', 
        ];

        if ($data_filter['ga_tab_active'] == 'acquisition') {

            switch ($data_filter['ga_sub_tab_active']) {
                case 'organic_search':
                    $dimensions['dimensionName'] = _l('keyword');
                    $dimensions['dimensions'][] = ['name' => 'sessionGoogleAdsKeyword'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Organic Search',
                        ],
                    ];
                    break;
                case 'paid_search':
                    $dimensions['dimensionName'] = _l('keyword');
                    $dimensions['dimensions'][] = ['name' => 'sessionGoogleAdsKeyword'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Paid Search',
                        ],
                    ];
                    break;
                case 'direct':
                    $dimensions['dimensionName'] = _l('page');
                    $dimensions['dimensions'][] = ['name' => 'pagePath'];
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Direct',
                        ],
                    ];
                    break;
                case 'social':
                    $dimensions['dimensionName'] = _l('sessionSource');
                    $dimensions['dimensions'][] = ['name' => 'sessionSource'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Organic Social',
                        ],
                    ];
                    break;
                case 'referral':
                    $dimensions['dimensionName'] = _l('sessionSource');
                    $dimensions['dimensions'][] = ['name' => 'sessionSource'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Referral',
                        ],
                    ];
                    break;
                case 'display':
                    $dimensions['dimensionName'] = _l('sessionSource');
                    $dimensions['dimensions'][] = ['name' => 'sessionSource'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Display',
                        ],
                    ];
                    break;
                case 'email':
                    $dimensions['dimensionName'] = _l('sessionSource');
                    $dimensions['dimensions'][] = ['name' => 'sessionSource'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Email',
                        ],
                    ];
                    break;
                case 'video':
                    $dimensions['dimensionName'] = _l('sessionSource');
                    $dimensions['dimensions'][] = ['name' => 'sessionSource'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Video',
                        ],
                    ];
                    break;
                case 'paid_social':
                    $dimensions['dimensionName'] = _l('keyword');
                    $dimensions['dimensions'][] = ['name' => 'sessionGoogleAdsKeyword'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'Paid Social',
                        ],
                    ];
                    break;
                default:
                    $dimensions['dimensionName'] = _l('channel');
                    $dimensions['dimensions'][] = ['name' => 'sessionDefaultChannelGroup'];

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
            }

        }elseif($data_filter['ga_tab_active'] == 'audience'){
            switch ($data_filter['ga_sub_tab_active']) {
                case 'location':
                    $dimensions['dimensions'][] = ['name' => 'country'];
                    $dimensions['dimensionName'] = _l('country');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'country',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];

                    break;
                case 'language':
                    $dimensions['dimensions'][] = ['name' => 'language'];
                    $dimensions['dimensionName'] = _l('language');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'language',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                case 'age':
                    $dimensions['dimensions'][] = ['name' => 'userAgeBracket'];
                    $dimensions['dimensionName'] = _l('userAgeBracket');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'userAgeBracket',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                case 'gender':
                    $dimensions['dimensions'][] = ['name' => 'userGender'];
                    $dimensions['dimensionName'] = _l('userGender');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'userGender',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];

                    break;
                case 'devices':
                    $dimensions['dimensions'][] = ['name' => 'mobileDeviceModel'];
                    $dimensions['dimensionName'] = _l('mobileDeviceModel');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'mobileDeviceModel',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];

                    break;
                case 'browser':
                    $dimensions['dimensions'][] = ['name' => 'browser'];
                    $dimensions['dimensionName'] = _l('browser');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'browser',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];

                    break;
                case 'operating_system':
                    $dimensions['dimensions'][] = ['name' => 'operatingSystem'];
                    $dimensions['dimensionName'] = _l('operatingSystem');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'operatingSystem',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];

                    break;
                case 'interests':
                    $dimensions['dimensions'][] = ['name' => 'brandingInterest'];
                    $dimensions['dimensionName'] = _l('interests');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'brandingInterest',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                case 'new_vs_returning':
                    $dimensions['dimensions'][] = ['name' => 'newVsReturning'];
                    $dimensions['dimensionName'] = _l('ga_new_vs_returning');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'newVsReturning',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                default:
                    $dimensions['dimensions'][] = ['name' => 'country'];
                    $dimensions['dimensionName'] = _l('country');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'country',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
            }
        }elseif($data_filter['ga_tab_active'] == 'conversions'){
            switch ($data_filter['ga_sub_tab_active']) {
                case 'campaign':
                    $dimensions['dimensions'][] = ['name' => 'sessionCampaignName'];
                    $dimensions['dimensionName'] = _l('campaign');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'sessionCampaignName',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                case 'ecommerce':
                    $dimensions['dimensions'][] = ['name' => 'itemName'];
                    $dimensions['dimensionName'] = _l('itemName');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'itemName',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                default:
                    $dimensions['dimensions'][] = ['name' => 'sessionCampaignName'];
                    $dimensions['dimensionName'] = _l('campaign');

                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'sessionCampaignName',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
            }
        }elseif($data_filter['ga_tab_active'] == 'pages'){
            switch ($data_filter['ga_sub_tab_active']) {
                case 'all':
                    $dimensions['dimensions'][] = ['name' => 'pagePath'];
                    $dimensions['dimensionName'] = _l('page');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'pagePath',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                case 'landing_pages':
                    $dimensions['dimensions'][] = ['name' => 'landingPage'];
                    $dimensions['dimensionName'] = _l('landingPage');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'landingPage',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                case 'path':
                    $dimensions['dimensions'][] = ['name' => 'unifiedPagePathScreen'];
                    $dimensions['dimensionName'] = _l('unifiedPagePathScreen');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'unifiedPagePathScreen',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                case 'title':
                    $dimensions['dimensions'][] = ['name' => 'unifiedScreenClass'];
                    $dimensions['dimensionName'] = _l('unifiedScreenClass');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'unifiedScreenClass',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                case 'content_group':
                    $dimensions['dimensions'][] = ['name' => 'contentGroup'];
                    $dimensions['dimensionName'] = _l('contentGroup');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'contentGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
                default:
                    $dimensions['dimensions'][] = ['name' => 'unifiedPagePathScreen'];
                    $dimensions['dimensionName'] = _l('page');
                    $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'unifiedPagePathScreen',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
                    break;
            }
        }elseif($data_filter['ga_tab_active'] == 'events'){
            $dimensions['dimensions'][] = ['name' => 'eventName'];
            $dimensions['dimensionName'] = _l('eventName');

            $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'eventName',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
        }else{
            $dimensions['dimensionName'] = _l('channel');
            $dimensions['dimensions'][] = ['name' => 'sessionDefaultChannelGroup'];
            $dimensions['dimensionFilter'] = [];
                    $dimensions['dimensionFilter']['notExpression'] = [];
                    $dimensions['dimensionFilter']['notExpression']['filter'] = [
                        'fieldName' => 'sessionDefaultChannelGroup',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => 'not set',
                        ],
                    ];
        }

        return $dimensions;
    }

    /**
     * [get_google_analytic_top_stats ]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_google_analytic_top_stats($data_filter){
        
        $account = $this->get_accounts($data_filter['account_id']);

        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setAccessToken($account->access_token);
        
        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $token_result = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            $data_update = [
                'access_token' => $token_result['access_token'],
                'expires_in' => time() + $token_result['expires_in'],
            ];

            $this->account_connect_save($data_update, $account->id);
        }

        $analytics = new Google_Service_AnalyticsData($client);
        $dimensions = $this->get_dimensions($data_filter);

        $data_return = [];
        $metrics = [];
        foreach ($data_filter['metrics'] as $k => $metric) {
            if($k >= 10){
                break;
            }
            $metrics[] = ['name' => $metric];
            $data_return[$metric] = 0;
        }


        $reportRequest_arr = [
                'dateRanges' => [ 
                    [
                        'startDate' => to_sql_date($data_filter['from_date']), 
                        'endDate' => to_sql_date($data_filter['to_date'])
                    ], 
                ], 
                'metrics' => $metrics, 
                'metricAggregations' => ['TOTAL']
            ];

        if($data_filter['ga_tab_active'] == 'acquisition' || $data_filter['ga_tab_active'] == 'pages'){
            $reportRequest_arr["dimensions"] = [
                    ['name' => 'date']
                ];
        }else{
            $reportRequest_arr["dimensions"] = $dimensions['dimensions'];
        }

        if(isset($dimensions['dimensionFilter'])){
            $reportRequest_arr["dimensionFilter"] = $dimensions['dimensionFilter'];
        }
        $reportRequest = new Google_Service_AnalyticsData_RunReportRequest($reportRequest_arr);

        $response = $analytics->properties->runReport('properties/'.$account->page_id, $reportRequest);
        $metricHeaders = $response->getMetricHeaders();
        $table_data = [];
        foreach ($response->getTotals() as $row) { 
            foreach ($row->getMetricValues() as $k => $metricValue) {
                $value = $metricValue->getValue();
                $data_return[$metricHeaders[$k]['name']] += $value;
            } 
        }   

        foreach ($metricHeaders as $k => $value) {
            switch ($metricHeaders[$k]['type']) {
                case 'TYPE_SECONDS':
                    $data_return[$value['name']] = $this->formatTime($data_return[$value['name']]);
                    break;
                case 'TYPE_INTEGER':
                    $data_return[$value['name']] = number_format($data_return[$value['name']]);
                    break;
                case 'TYPE_FLOAT':
                    if (!(strpos($metricHeaders[$k]['name'], 'Rate') === false)) {
                        $data_return[$value['name']] = $this->formatToPercentage($data_return[$value['name']]);
                    }else{
                        $data_return[$value['name']] = number_format($data_return[$value['name']], 2);
                    }
                    break;
                case 'TYPE_CURRENCY':
                    $data_return[$value['name']] = app_format_money($data_return[$value['name']], '');;
                    break;
                
                default:
                    break;
            }
        }

        return $data_return;
    }

    /**
     * [convertDate]
     * @param  [string] $date
     * @return [string]      
     */
    public function convertDate($date) {
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);

        return "{$year}-{$month}-{$day}";
    }

    /**
     * [get_google_analytic_chart ]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_google_analytic_chart($data_filter){
        $account = $this->get_accounts($data_filter['account_id']);

        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setAccessToken($account->access_token);
        
        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $token_result = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            $data_update = [
                'access_token' => $token_result['access_token'],
                'expires_in' => time() + $token_result['expires_in'],
            ];

            $this->account_connect_save($data_update, $account->id);
        }

        $dimensions = $this->get_dimensions($data_filter);
        
        $reportRequest_arr = [
                'orderBys' => [
                    'dimension' => [
                        'dimensionName' => 'date',
                    ],
                ],
                'dateRanges' => [ 
                    [
                        'startDate' => to_sql_date($data_filter['from_date']), 
                        'endDate' => to_sql_date($data_filter['to_date'])
                    ], 
                ], 
                "dimensions" => [
                    ['name' => 'date']
                ],
                'metrics' => [
                    ['name' => $data_filter['activeStat']]
                ], 
            ];

        if(isset($dimensions['dimensionFilter'])){
            $reportRequest_arr["dimensionFilter"] = $dimensions['dimensionFilter'];
        }

        $analytics = new Google_Service_AnalyticsData($client);

        $reportRequest = new Google_Service_AnalyticsData_RunReportRequest($reportRequest_arr);

        $response = $analytics->properties->runReport('properties/'.$account->page_id, $reportRequest);
        $metricHeaders = $response->getMetricHeaders();
        
        $categories = [];
        $data_date = [];

        $type = 'TYPE_INTEGER';
        foreach ($response->getRows() as $row) {
            $date = $row->getDimensionValues()[0]->getValue();
            $value = $row->getMetricValues()[0]->getValue();

            $date = $this->convertDate($date);

            if(!isset($data_date[$date])){
                $categories[] = date('d M', strtotime($date));
            }

                $data_date[$date] = round($value);
            switch ($metricHeaders[0]['type']) {
                case 'TYPE_FLOAT':
                    if (!(strpos($metricHeaders[0]['name'], 'Rate') === false)) {
                        $data_date[$date] = round($value*100, 2);
                    }
                    break;
                case 'TYPE_SECONDS':
                    $type = 'TYPE_SECONDS';
                    break;
                
                default:
                    break;
            }
        } 


        $data_return = [
            'data' => [
                ['name' => _l('ga_sessions'), 'data' => array_values($data_date)],
            ],
            'categories' => $categories,
            'name' => _l($data_filter['activeStat']),
            'type' => $type
        ];

        return $data_return;
    }

    /**
     * [get_google_analytic_pie_chart ]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_google_analytic_pie_chart($data_filter){
        
        $account = $this->get_accounts($data_filter['account_id']);

        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setAccessToken($account->access_token);
        
        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $token_result = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            $data_update = [
                'access_token' => $token_result['access_token'],
                'expires_in' => time() + $token_result['expires_in'],
            ];

            $this->account_connect_save($data_update, $account->id);
        }

        $analytics = new Google_Service_AnalyticsData($client);

        $dimensions = $this->get_dimensions($data_filter);
        
        $reportRequest_arr = [
                'dateRanges' => [ 
                    [
                        'startDate' => to_sql_date($data_filter['from_date']), 
                        'endDate' => to_sql_date($data_filter['to_date'])
                    ], 
                ], 
                "dimensions" => $dimensions['dimensions'],
                'metrics' => [
                    ['name' => $data_filter['activeStat']]
                ], 
            ];

        if(isset($dimensions['dimensionFilter'])){
            $reportRequest_arr["dimensionFilter"] = $dimensions['dimensionFilter'];

        }

        $reportRequest = new Google_Service_AnalyticsData_RunReportRequest($reportRequest_arr);

        $response = $analytics->properties->runReport('properties/'.$account->page_id, $reportRequest);
        
        $dimensionHeaders = $response->getDimensionHeaders();
        $metricHeaders = $response->getMetricHeaders();

        $data_arr = [];
        foreach ($response->getRows() as $row) { 
            $value = $row->getMetricValues()[0]->getValue();
            $dimension = $row->getDimensionValues()[0]->getValue();

            $data_arr[$dimension] = $value;
        }
        $type = 'TYPE_INTEGER';
        foreach ($data_arr as $k => $value) {
            switch ($metricHeaders[0]['type']) {
                case 'TYPE_SECONDS':
                    $data_arr[$k] = $data_arr[$k];
                    $type = 'TYPE_SECONDS';
                    break;
                case 'TYPE_INTEGER':
                    $data_arr[$k] = round($data_arr[$k]);
                    break;
                case 'TYPE_FLOAT':
                    if (!(strpos($metricHeaders[0]['name'], 'Rate') === false)) {
                        $data_arr[$k] = round($data_arr[$k]*100, 2);
                    }else{
                        $data_arr[$k] = round($data_arr[$k], 2);
                    }
                    break;
                case 'TYPE_CURRENCY':
                    $data_arr[$k] = round($data_arr[$k], 2);;
                    break;
                
                default:
                    break;
            }
        } 

        $data_return = ['data' => [], 'name' => _l($data_filter['activeStat']), 'type' => $type];
        foreach ($data_arr as $name => $value) {
            $data_return['data'][] = ['name' => _l($name != '' ? $name : 'not_set'), 'y' => (float)$value];
        }

        return $data_return;
    }


    /**
     * [get_google_analytic_column_chart ]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_google_analytic_column_chart($data_filter){
        
        $account = $this->get_accounts($data_filter['account_id']);

        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setAccessToken($account->access_token);
        
        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $token_result = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            $data_update = [
                'access_token' => $token_result['access_token'],
                'expires_in' => time() + $token_result['expires_in'],
            ];

            $this->account_connect_save($data_update, $account->id);
        }

        $analytics = new Google_Service_AnalyticsData($client);

        $dimensions = $this->get_dimensions($data_filter);
        
        $reportRequest_arr = [
                'dateRanges' => [ 
                    [
                        'startDate' => to_sql_date($data_filter['from_date']), 
                        'endDate' => to_sql_date($data_filter['to_date'])
                    ], 
                ], 
                "dimensions" => $dimensions['dimensions'],
                'metrics' => [
                    ['name' => $data_filter['activeStat']]
                ], 
            ];

        if(isset($dimensions['dimensionFilter'])){
            $reportRequest_arr["dimensionFilter"] = $dimensions['dimensionFilter'];

        }

        $reportRequest = new Google_Service_AnalyticsData_RunReportRequest($reportRequest_arr);

        $response = $analytics->properties->runReport('properties/'.$account->page_id, $reportRequest);
        
        $dimensionHeaders = $response->getDimensionHeaders();
        $metricHeaders = $response->getMetricHeaders();

        $data_arr = [];
        foreach ($response->getRows() as $row) { 
            $value = $row->getMetricValues()[0]->getValue();
            $dimension = $row->getDimensionValues()[0]->getValue();

            $data_arr[$dimension] = $value;
        }

        $type = 'TYPE_INTEGER';

        foreach ($data_arr as $k => $value) {
            switch ($metricHeaders[0]['type']) {
                case 'TYPE_SECONDS':
                    $type = 'TYPE_SECONDS';
                    $data_arr[$k] = $data_arr[$k];
                    break;
                case 'TYPE_INTEGER':
                    $data_arr[$k] = round($data_arr[$k]);
                    break;
                case 'TYPE_FLOAT':
                    if (!(strpos($metricHeaders[0]['name'], 'Rate') === false)) {
                        $data_arr[$k] = round($data_arr[$k]*100, 2);
                    }else{
                        $data_arr[$k] = round($data_arr[$k], 2);
                    }
                    break;
                case 'TYPE_CURRENCY':
                    $data_arr[$k] = round($data_arr[$k], 2);;
                    break;
                
                default:
                    break;
            }
        } 

        $header = [];
        $total = [];
        $i = 0;
        foreach ($data_arr as $name => $value) {
            $header[] = _l($name != '' ? $name : 'not_set');
            $total[] = (float)$value;
            $i++;
            if($data_filter['ga_tab_active'] == 'pages' && $i >= 5){
                break;
            }
        }

        return ['header' => $header, 'data_total' => $total, 'name' => _l($data_filter['activeStat']), 'type' => $type];
    }


    /**
     * [update_analytic_metrics ]
     * @param  [array] $data_filter
     * @return [boolean]             
     */
    public function update_analytic_metrics($data_filter){


        $analytic_metrics = explode(',', ga_get_staff_metrics());

        $selectedMetrics = $data_filter['metrics'];
        $totalMetrics = $data_filter['total_metrics'];

        $data_update = $selectedMetrics;
        foreach ($analytic_metrics as $key => $value) {
            if(!in_array($value, $data_update) && !in_array($value, $totalMetrics)){
                $data_update[] = $value;
            }
        }

        $staff_id = get_staff_user_id();
        $this->db->where('staffid', $staff_id);
        $this->db->update(db_prefix().'staff', ['ga_analytic_metrics' => implode(',', $data_update)]);

        return true;
    }

    /**
     * [update_analytic_metrics_client ]
     * @param  [array] $data_filter
     * @return [boolean]             
     */
    public function update_analytic_metrics_client($data_filter){
        $analytic_metrics = explode(',', ga_get_contact_metrics());

        $selectedMetrics = $data_filter['metrics'];
        $totalMetrics = $data_filter['total_metrics'];

        $data_update = $selectedMetrics;
        foreach ($analytic_metrics as $key => $value) {
            if(!in_array($value, $data_update) && !in_array($value, $totalMetrics)){
                $data_update[] = $value;
            }
        }

        $contact_id = get_contact_user_id();
        $this->db->where('id', $contact_id);
        $this->db->update(db_prefix().'contacts', ['ga_analytic_metrics' => implode(',', $data_update)]);

        return true;
    }

    /**
     * [set_contact_default_workspace]
     * @param [type] $id workspace id
     * @return [boolean]             
     */
    public function set_contact_default_workspace($id){
        $contact_id = get_contact_user_id();
        $this->db->where('id', $contact_id);
        $this->db->update(db_prefix().'contacts', ['ga_base_workspace_id' => $id]);
        
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * change accoun active
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_account_active($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'ga_accounts', [
            'active' => $status,
        ]);
    }

    /**
     * [get_google_analytic_map_chart]
     * @param  [array] $data_filter
     * @return [array]             
     */
    public function get_google_analytic_map_chart($data_filter){
        
        $account = $this->get_accounts($data_filter['account_id']);

        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setAccessToken($account->access_token);
        
        $expires_in = date('Y-m-d H:i:s', $account->expires_in);
        if(time() > $account->expires_in){
            $token_result = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

            $data_update = [
                'access_token' => $token_result['access_token'],
                'expires_in' => time() + $token_result['expires_in'],
            ];

            $this->account_connect_save($data_update, $account->id);
        }

        $analytics = new Google_Service_AnalyticsData($client);

        $dimensions = $this->get_dimensions($data_filter);
        
        $reportRequest_arr = [
                'dateRanges' => [ 
                    [
                        'startDate' => to_sql_date($data_filter['from_date']), 
                        'endDate' => to_sql_date($data_filter['to_date'])
                    ], 
                ], 
                "dimensions" => [
                    ['name' => 'countryId']
                ],
                'metrics' => [
                    ['name' => $data_filter['activeStat']]
                ], 
            ];

        if(isset($dimensions['dimensionFilter'])){
            $reportRequest_arr["dimensionFilter"] = $dimensions['dimensionFilter'];

        }

        $reportRequest = new Google_Service_AnalyticsData_RunReportRequest($reportRequest_arr);

        $response = $analytics->properties->runReport('properties/'.$account->page_id, $reportRequest);
        
        $dimensionHeaders = $response->getDimensionHeaders();
        $metricHeaders = $response->getMetricHeaders();

        $data_arr = [];
        foreach ($response->getRows() as $row) { 
            $value = $row->getMetricValues()[0]->getValue();
            $dimension = $row->getDimensionValues()[0]->getValue();

            $data_arr[$dimension] = $value;
        }
        $type = 'TYPE_INTEGER';
        foreach ($data_arr as $k => $value) {
            switch ($metricHeaders[0]['type']) {
                case 'TYPE_SECONDS':
                    $data_arr[$k] = $data_arr[$k];
                    $type = 'TYPE_SECONDS';
                    break;
                case 'TYPE_INTEGER':
                    $data_arr[$k] = round($data_arr[$k]);
                    break;
                case 'TYPE_FLOAT':
                    if (!(strpos($metricHeaders[0]['name'], 'Rate') === false)) {
                        $data_arr[$k] = round($data_arr[$k]*100, 2);
                    }else{
                        $data_arr[$k] = round($data_arr[$k], 2);
                    }
                    break;
                case 'TYPE_CURRENCY':
                    $data_arr[$k] = round($data_arr[$k], 2);;
                    break;
                
                default:
                    break;
            }
        } 

        $data_return = ['data' => 'code;value\n', 'name' => _l($data_filter['activeStat']), 'type' => $type];
        foreach ($data_arr as $name => $value) {
            $data_return['data'] .= $name.';'.$value.'\n';
        }

        return $data_return;
    }
}