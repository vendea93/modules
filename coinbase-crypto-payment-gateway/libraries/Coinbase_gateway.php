<?php

use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Exceptions\InvalidResponseException;
use CoinbaseCommerce\Resources\Charge;
use CoinbaseCommerce\Webhook;

defined('BASEPATH') or exit('No direct script access allowed');

class Coinbase_gateway extends App_gateway
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('coinbase');

        /**
         * Gateway name
         */
        $this->setName('Coinbase Payment');

        /**
         * Add gateway settings
         */
        $this->setSettings([
            [
                'name' => 'api_key',
                'encrypted' => true,
                'label' => 'API KEY',
                'type' => 'input',
            ],
            [
                'name' => 'webhook_secret',
                'label' => 'SECRET KEY',
                'encrypted' => true,
                'type' => 'input'
            ],
            [
                'name' => 'currencies',
                'label' => 'settings_paymentmethod_currencies',
                'default_value' => 'USD'
            ],

        ]);

        hooks()->add_action('before_render_payment_gateway_settings', 'coinbase_gateway_webhook_notice');
    }

    /**
     * Each time a customer click PAY NOW button on the invoice HTML area, the script will process the payment via this function.
     * You can show forms here, redirect to gateway website, redirect to Codeigniter controller etc..
     * @param  array  $data  - Contains the total amount to pay and the invoice information
     * @return mixed
     */
    public function process_payment($data)
    {
        if (is_client_logged_in()) {
            $contact = $this->ci->clients_model->get_contact(get_contact_user_id());
            $name = "$contact->firstname $contact->lastname";
        } else {
            $name = $data['invoice']->client->company;
        }

        $chargeData = [
            'name' => get_option('companyname'),
            'description' => 'Payment For Invoice ' . format_invoice_number($data['invoiceid']),
            'local_price' => [
                'amount' => $data['amount'],
                'currency' => $data['invoice']->currency_name
            ],
            'pricing_type' => 'fixed_price',
            'metadata' => [
                'client_id' => $data['invoice']->clientid,
                'amount' => $data['amount'],
                'transactionid' => time() . '-' . $data['invoiceid'],
                'invoice_id' => $data['invoiceid'],
                'invoice_hash' => $data['hash'],
            ],
            'redirect_url' => site_url("coinbase/callback/{$data['invoiceid']}/{$data['hash']}"),
            'cancel_url' => site_url("coinbase/cancel/{$data['invoiceid']}/{$data['hash']}")
        ];

        try {
            ApiClient::init($this->get_api_key());
            /** @var Charge $charge */
            $charge = Charge::create($chargeData);
            redirect($charge->getAttribute('hosted_url'));
        } catch (Exception $exception) {
            set_alert('danger', $exception->getMessage());
        }
    }

    /**
     * Gets API key
     * @return string
     */

    public function get_api_key(): string
    {
        return $this->decryptSetting('api_key');
    }

    /**
     * @param  string  $payload
     * @param  string  $signatureHeader
     * @return \CoinbaseCommerce\Resources\Event|null
     * @throws InvalidResponseException
     */
    public function get_webhook_data(string $payload, string $signatureHeader): ?\CoinbaseCommerce\Resources\Event
    {
        return Webhook::buildEvent($payload, $signatureHeader, $this->get_webhook_secret());
    }

    /**
     * Gets Webhook secret
     * @return string
     */

    public function get_webhook_secret(): string
    {
        return $this->decryptSetting('webhook_secret');
    }

    /**
     * @param  string  $transactionId
     * @return bool
     */
    public function paymentExist(string $transactionId): bool
    {
        return $this->ci->db->where('transactionid', $transactionId)->get(db_prefix() . 'invoicepaymentrecords')->num_rows() > 0;
    }

    /**
     * @param  \CoinbaseCommerce\Resources\Event|null  $event
     * @return void
     * @throws Throwable
     */
    public function assertValidWebhookData(?\CoinbaseCommerce\Resources\Event $event)
    {
        throw_if($event === null, new Exception("Invalid Webhook: Does not contain any event"));
        /** @var \CoinbaseCommerce\Resources\Event $charge */
        $charge = $event->data;
        $metadata = $charge->getAttribute('metadata');

        throw_if($event->data === null, new Exception("Invalid Webhook: Does not contain data"));
        throw_if($event->getAttribute('type') !== 'charge:confirmed', new Exception("Webhook Type is not charge:confirmed, event: " . $event->getAttribute('type')));
        throw_if(empty($metadata), new Exception("No metadata in webhook received"));
        throw_if($metadata['invoice_id'] !== explode('-', $metadata['transactionid'])[1],
            new Exception("Invalid transaction Id: {$metadata['transactionid']}"));
    }

    /**
     * @param  array  $data
     * @return void
     */
    public function recordPayment(array $data)
    {
        $this->addPayment([
            'amount' => $data['amount'],
            'invoiceid' => $data['invoice_id'],
            'paymentmethod' => 'crypto',
            'transactionid' => $data['transactionid'],
        ]);
    }
}
