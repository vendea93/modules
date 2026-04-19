<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Coinbase Payment
Description: Coinbase Payment Gateway for Perfex CRM
Version: 1.0.0
Requires at least: 2.9.3
Author: Techy4m
Author URI: https://codecanyon.net/user/techy4m
*/
require(__DIR__ . '/vendor/autoload.php');

$CI = &get_instance();

register_payment_gateway('coinbase_gateway', 'coinbase');
hooks()->add_filter('module_coinbase_action_links', 'coinbase_module_action_links');

/**
 * adds help module button on modules page
 * @param  array  $actions  current actions
 * @return array
 */
function coinbase_module_action_links(array $actions): array
{
    $actions[] = "<a href='" . admin_url("settings?group=payment_gateways") . "'>" . _l("settings") . "</a>";
    $actions[] = "<a href='https://www.boxvibe.com/support?envato_item_id=26621431' target='_blank'>" . _l('help') . "</a>";
    return $actions;
}

function coinbase_gateway_webhook_notice()
{
    echo "<div class='alert alert-warning mtop10'>";
    echo "Ensure you have set Webhook notification endpoint for your installation in your Coinbase commerce dashboard <a href='https://beta.commerce.coinbase.com/settings/notifications'>here</a>";
    echo "</div>";
    echo "<div class='alert alert-info mtop10'>";
    echo "The webhook Endpoint is ( " . site_url("coinbase/webhook") . " )";
    echo "</div>";

    echo "<h5>Get your API Key from your Coinbase commerce dashboard <a href='https://beta.commerce.coinbase.com/settings/security'>Settings->Security</a>";
    echo "<h5>Get your Webhook Secret from your Coinbase commerce dashboard <a href='https://beta.commerce.coinbase.com/settings/notifications'>Settings->Notifications</a>";
}

if (! function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @param  mixed  $condition
     * @param  \Throwable|string  $exception
     * @param  array  ...$parameters
     * @return mixed
     *
     * @throws \Throwable
     */
    function throw_if($condition, $exception, ...$parameters)
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return $condition;
    }
}
