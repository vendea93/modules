<?php

namespace Techy4m\XmlExports\Data;

class Client
{
    private \CI_Controller $ci;
    public readonly int $id;
    public readonly string $company;
    public readonly string $vat;
    public readonly string $phonenumber;
    public readonly string $country;
    public readonly string $city;
    public readonly string $zip;
    public readonly string $state;
    public readonly string $address;
    public readonly string $website;
    public readonly string $active;
    public readonly string $billing_street;
    public readonly string $billing_city;
    public readonly string $billing_state;
    public readonly string $billing_zip;
    public readonly string $billing_country;
    public readonly string $shipping_street;
    public readonly string $shipping_city;
    public readonly string $shipping_state;
    public readonly string $shipping_zip;
    public readonly string $shipping_country;
    public readonly string $longitude;
    public readonly string $latitude;
    public readonly string $show_primary_contact;
    public readonly int $userid;

    private object $primaryContactCache;

    public function __construct(object $client)
    {
        $this->id = (int)$client->userid;
        $this->company = (string)$client->company;
        $this->vat = (string)$client->vat;
        $this->phonenumber = (string)$client->phonenumber;
        $this->country = (string)$client->country;
        $this->city = (string)$client->city;
        $this->zip = (string)$client->zip;
        $this->state = (string)$client->state;
        $this->address = (string)$client->address;
        $this->website = (string)$client->website;
        $this->active = (string)$client->active;
        $this->billing_street = (string)$client->billing_street;
        $this->billing_city = (string)$client->billing_city;
        $this->billing_state = (string)$client->billing_state;
        $this->billing_zip = (string)$client->billing_zip;
        $this->billing_country = (string)$client->billing_country;
        $this->shipping_street = (string)$client->shipping_street;
        $this->shipping_city = (string)$client->shipping_city;
        $this->shipping_state = (string)$client->shipping_state;
        $this->shipping_zip = (string)$client->shipping_zip;
        $this->shipping_country = (string)$client->shipping_country;
        $this->longitude = (string)$client->longitude;
        $this->latitude = (string)$client->latitude;
        $this->show_primary_contact = (string)$client->show_primary_contact;
        $this->userid = (int)$client->userid;

        $this->ci = &get_instance();
        $primaryContactId = get_primary_contact_user_id($this->userid);
        if ($primaryContactId && ($primaryContact = $this->ci->clients_model->get_contact($primaryContactId))) {
            $this->primaryContactCache = $primaryContact;
        }
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return ($this->company ?: get_company_name($this->id)) ?? '';
    }

    public function hasBillingAddress(): bool
    {
        return !empty($this->billing_street);
    }

    public function getStreet(): string
    {
        if ($this->hasBillingAddress()) {
            return $this->billing_street;
        }
        return $this->address;
    }
    public function getCity(): string
    {
        if ($this->hasBillingAddress()) {
            return $this->billing_city;
        }
        return $this->city;
    }
    public function getState(): string
    {
        if ($this->hasBillingAddress()) {
            return $this->billing_state;
        }
        return $this->state;
    }
    public function getZip(): string
    {
        if ($this->hasBillingAddress()) {
            return $this->billing_zip;
        }
        return $this->zip;
    }
    public function getCountry(): string
    {
        if ($this->hasBillingAddress()) {
            return $this->billing_country;
        }
        return $this->country;
    }

    public function getPrimaryContactFirstName(): string|null
    {
        return $this->primaryContactCache->firstname ?? null;
    }

    public function getPrimaryContactLastName(): string|null
    {
        return $this->primaryContactCache->lastname ?? null;
    }
}
