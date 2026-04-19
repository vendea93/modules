<?php

use Saloon\Exceptions\SaloonException;
use Saloon\Http\Response;

defined('BASEPATH') or exit('No direct script access allowed');

class Sms_{$prefix} extends App_sms
{
    private string ${$prefix}_key;
	private string ${$prefix}_service;
	private string ${$prefix}_whatsapp;
	private string ${$prefix}_device;
	private string ${$prefix}_gateway;
    private string ${$prefix}_sim;

    public function __construct()
    {
        parent::__construct();

        $this->{$prefix}_key = $this->get_option("{$prefix}", "{$prefix}_key");
        $this->{$prefix}_service = $this->get_option("{$prefix}", "{$prefix}_service");
        $this->{$prefix}_whatsapp = $this->get_option("{$prefix}", "{$prefix}_whatsapp");
        $this->{$prefix}_device = $this->get_option("{$prefix}", "{$prefix}_device");
        $this->{$prefix}_gateway = $this->get_option("{$prefix}", "{$prefix}_gateway");
        $this->{$prefix}_sim = $this->get_option("{$prefix}", "{$prefix}_sim");

        $this->add_gateway("{$prefix}", [
            "name" => "{$name}",
            "options" => [
                [
                    "name" => "{$prefix}_key",
                    "label" => "API Key (<a href=\"{$site_url}/dashboard/tools/keys\" target=\"_blank\">Create API Key</a>)",
                    "info" => "
                    <p>Your API key, please make sure that everything is correct and required permissions are granted: <strong>sms_send</strong>, <strong>wa_send</strong></p>
                    <hr class=\"hr-15\" />"
                ],   
                [
                    "name" => "{$prefix}_service",
                    "field_type" => "radio",
                    "default_value" => 1,
                    "label" => "Sending Service",
                    "options" => [
                        ["label" => "SMS", "value" => 1],
                        ["label" => "WhatsApp", "value" => 2]
                    ],
                    "info" => "
                    <p>Select the sending service, please make sure that the api key has the following permissions: <strong>send_sms</strong>, <strong>wa_send</strong></p>
                    <hr class=\"hr-15\" />"
                ],      
                [
                    "name" => "{$prefix}_whatsapp",
                    "label" => "WhatsApp Account ID",
                    "info" => "
                    <p>For WhatsApp service only. WhatsApp account ID you want to use for sending.</p>
                    <hr class=\"hr-15\" />"
                ], 
                [
                    "name" => "{$prefix}_device",
                    "label" => "Device Unique ID",
                    "info" => "
                    <p>For SMS service only. Linked device unique ID, please only enter this field if you are sending using one of your devices.</p>
                    <hr class=\"hr-15\" />"
                ],        
                [
                    "name" => "{$prefix}_gateway",
                    "label" => "Gateway Unique ID",
                    "info" => "
                    <p>For SMS service only. Partner device unique ID or gateway ID, please only enter this field if you are sending using a partner device or third party gateway.</p>
                    <hr class=\"hr-15\" />"
                ],    
                [
                    "name" => "{$prefix}_sim",
                    "field_type" => "radio",
                    "default_value" => 1,
                    "label" => "SIM Slot",
                    "options" => [
                        ["label" => "SIM 1", "value" => 1],
                        ["label" => "SIM 2", "value" => 2]
                    ],
                    "info" => "
                    <p>For SMS service only. Select the sim slot you want to use for sending the messages. This is not used for partner devices and third party gateways.</p>
                    <hr class=\"hr-15\" />"
                ]
            ],
        ]);
    }

    public function send($number, $message): bool
    {
        if(empty($this->{$prefix}_service) || $this->{$prefix}_service < 2):
            if(!empty($this->{$prefix}_device)):
                $mode = "devices";
            else:
                $mode = "credits";
            endif;

            if($mode == "devices"):
                $form = [
                    "secret" => $this->{$prefix}_key,
                    "mode" => "devices",
                    "device" => $this->{$prefix}_device,
                    "phone" => $number,
                    "message" => $message,
                    "sim" => $this->{$prefix}_sim < 2 ? 1 : 2
                ];
            else:
                $form = [
                    "secret" => $this->{$prefix}_key,
                    "mode" => "credits",
                    "gateway" => $this->{$prefix}_gateway,
                    "phone" => $number,
                    "message" => $message
                ];
            endif;

            $apiurl = "{$site_url}/api/send/sms";
        else:
            $form = [
                "secret" => $this->{$prefix}_key,
                "account" => $this->{$prefix}_whatsapp,
                "type" => "text",
                "recipient" => $number,
                "message" => $message
            ];

            $apiurl = "{$site_url}/api/send/whatsapp";
        endif;

        try {
            $send = json_decode($this->client->request(
                "POST",
                $apiurl,
                [
                    "form_params" => $form,
                    "allow_redirects" => true,
                    "http_errors" => false,
                ]
            )->getBody()->getContents(), true);

            if($send["status"] == 200):
                $this->logSuccess($number, $message);
                return true;
            else:
            {literal}
                $this->set_error("Message was not sent!<br>Message: {$send["message"]}");
                return false;
            endif;
        } catch(SaloonException $e){
            $this->set_error("Message was not sent!<br>Error: {$e->getMessage()}");
            return false;
            {/literal}
        }
    }
}
