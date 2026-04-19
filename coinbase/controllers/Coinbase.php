<?php

use CoinbaseCommerce\ApiClient;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Coinbase_gateway $coinbase_gateway
 */
class Coinbase extends App_Controller
{

    /**
     * @param  string  $invoiceId
     * @param  string  $hash
     * @return void
     */
    public function callback($invoiceId, $hash)
    {
        check_invoice_restrictions($invoiceId, $hash);
        set_alert("success", "The Payment is being Processed, you will receive an email if successful");
        redirect(site_url("invoice/$invoiceId/$hash"));
    }

    /**
     * @param  string  $invoiceId
     * @param  string  $hash
     * @return void
     */
    public function cancel($invoiceId, $hash)
    {
        check_invoice_restrictions($invoiceId, $hash);
        set_alert("danger", "Payment Cancelled");
        redirect(site_url("invoice/$invoiceId/$hash"));
    }

    public function webhook()
    {
        ApiClient::init($this->coinbase_gateway->get_api_key());
        $events = \CoinbaseCommerce\Resources\Event::getAll();
        /** @var \CoinbaseCommerce\Resources\Event $e */
        $e = $events[0];
        $e->getAttribute('data');
        $payload = trim(file_get_contents('php://input'));
        $headers = getallheaders();
        $signatureHeader =isset($headers['x-cc-webhook-signature']) ? $headers['x-cc-webhook-signature'] : (isset($headers['X-Cc-Webhook-Signature']) ? $headers['X-Cc-Webhook-Signature'] : null);
        try {
            if ($signatureHeader === null) {
                throw new Exception("No webhook signature: sent headers: <code>" .collect($headers)->toJson().'</code>');
            }

            $event = $this->coinbase_gateway->get_webhook_data($payload, $signatureHeader);
            $this->coinbase_gateway->assertValidWebhookData($event);

            /** @var \CoinbaseCommerce\Resources\Event $charge */
            $charge = $event->data;
            $metadata = $charge->getAttribute('metadata');
            check_invoice_restrictions($metadata['invoice_id'], $metadata['invoice_hash']);
            if ($this->coinbase_gateway->paymentExist($metadata['transactionid'])) {
                http_response_code(200);
                die;
            }

            $this->coinbase_gateway->recordPayment($metadata);
            http_response_code(200);
        } catch (Exception|Throwable $exception) {
            http_response_code(400);
            log_activity("Coinbase Webhook error: {$exception->getMessage()}");
        }
    }
}
