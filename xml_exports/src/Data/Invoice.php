<?php

namespace Techy4m\XmlExports\Data;

use Illuminate\Support\Fluent;

class Invoice
{
    public readonly int $id;
    public readonly string $hash;
    public readonly int $number;
    public readonly string $number_format;
    public readonly int $clientid;
    public readonly int $currency;
    public string $currency_name;
    public readonly int $currencyid;
    public readonly string $date;
    public readonly ?string $duedate;
    public readonly float $subtotal;
    public readonly float $total;
    public readonly float $total_tax;
    public readonly int $adjustment;
    public readonly float $discount_percent;
    public readonly float $discount_total;
    public readonly string $discount_type;
    public readonly string $terms;
    public Fluent $sale_agent;
    public readonly ?string $billing_street;
    public readonly ?string $billing_city;
    public readonly ?string $billing_state;
    public readonly ?string $billing_zip;
    public readonly ?string $billing_country;
    public readonly ?string $shipping_street;
    public readonly ?string $shipping_city;
    public readonly ?string $shipping_state;
    public readonly ?string $shipping_zip;
    public readonly ?string $shipping_country;
    public readonly int $show_shipping_on_invoice;
    public readonly string $include_shipping;
    public readonly string $clientnote;
    public readonly string $adminnote;
    public readonly string $allowed_payment_modes;
    /** @var array<int, array{
     * 'description': string,
     * 'long_description': string,
     * 'qty': float,
     * 'rate':float,
     * 'unit': string,
     * 'id': int,
     * 'item_order': int,
     * }> */
    public readonly array $items;
    public readonly float $total_left_to_pay;
    public readonly ?string $project_id;
    public readonly array $payments;
    public readonly string $prefix;
    public readonly Client $client;

    public function __construct(object $invoice)
    {
        $this->id = $invoice->id;
        $this->hash = $invoice->hash;
        $this->number = $invoice->number;
        $this->number_format = $invoice->number_format;
        $this->clientid = $invoice->clientid;
        $this->currency = $invoice->currency;
        $this->currency_name = $invoice->currency_name;
        $this->currencyid = $invoice->currencyid;
        $this->date = $invoice->date;
        $this->duedate = $invoice->duedate;
        $this->subtotal = $invoice->subtotal;
        $this->total = $invoice->total;
        $this->total_tax = $invoice->total_tax;
        $this->adjustment = $invoice->adjustment;
        $this->discount_percent = $invoice->discount_percent;
        $this->discount_total = $invoice->discount_total;
        $this->discount_type = $invoice->discount_type;
        $this->terms = $invoice->terms;
        $this->billing_street = $invoice->billing_street;
        $this->billing_city = $invoice->billing_city;
        $this->billing_state = $invoice->billing_state;
        $this->billing_zip = $invoice->billing_zip;
        $this->billing_country = $invoice->billing_country;
        $this->shipping_street = $invoice->shipping_street;
        $this->shipping_city = $invoice->shipping_city;
        $this->shipping_state = $invoice->shipping_state;
        $this->shipping_zip = $invoice->shipping_zip;
        $this->shipping_country = $invoice->shipping_country;
        $this->show_shipping_on_invoice = $invoice->show_shipping_on_invoice;
        $this->include_shipping = $invoice->include_shipping;
        $this->clientnote = $invoice->clientnote;
        $this->adminnote = $invoice->adminnote;
        $this->allowed_payment_modes = $invoice->allowed_payment_modes;
        $this->items = $invoice->items;
        $this->total_left_to_pay = $invoice->total_left_to_pay;
        $this->project_id = $invoice->project_id ?? null;
        $this->client = new Client($invoice->client);
        $this->payments = $invoice->payments;
        $this->prefix = $invoice->prefix;

        if (!$this->currency_name) {
            if ($this->currency > 0) {
                $this->currency_name = get_currency($this->currency)?->name;
            } else {
                $currency = get_base_currency();
                $this->currency_name = $currency->name;
            }
        }
        if ($invoice->sale_agent != 0 && $invoice->sale_agent !== null) {
            $staff = get_staff($invoice->sale_agent);
            if ($staff) {
                $this->sale_agent = new Fluent($staff);
                $this->sale_agent->set('name', $staff->firstname . ' ' . $staff->lastname);
            }
        }
    }

    public function getAddress()
    {
        if ($this->hasInvoiceAddress()) {
            return $this->billing_street;
        }
        return $this->client->getStreet();
    }

    private function hasInvoiceAddress(): bool
    {
        return !empty($this->billing_city);
    }

    public function getCity()
    {
        if ($this->hasInvoiceAddress()) {
            return $this->billing_city;
        }
        return $this->client->getCity();
    }

    public function getState()
    {
        if ($this->hasInvoiceAddress()) {
            return $this->billing_state;
        }
        return $this->client->getState();
    }

    public function getZip()
    {
        if ($this->hasInvoiceAddress()) {
            return $this->billing_zip;
        }
        return $this->client->getZip();
    }

    public function getCountry()
    {
        if ($this->hasInvoiceAddress()) {
            return $this->billing_country;
        }
        return $this->client->getCountry();
    }
}