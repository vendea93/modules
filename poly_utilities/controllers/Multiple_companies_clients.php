<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Multiple Companies Clients Controller (Client Side)
 * Handles client-side operations for company switching
 */
class Multiple_companies_clients extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Switch authentication to different company
     * @param int $clientID Client/Company ID to switch to
     */
    public function change_auth($clientID = 0)
    {
        if (empty($_SESSION['all_clients'])) {
            redirect(site_url());
            return;
        }

        foreach ($_SESSION['all_clients'] as $client) {
            if ($client->userid == $clientID) {
                // Assign active customer information
                $_SESSION['client_user_id']  = $client->userid;
                $_SESSION['contact_user_id'] = $client->id;
                break;
            }
        }

        redirect(site_url());
    }
}

