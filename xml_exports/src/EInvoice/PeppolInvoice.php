<?php

namespace Techy4m\XmlExports\EInvoice;
defined('BASEPATH') or exit('No direct script access allowed');

use Carbon\Carbon;
use Einvoicing\AllowanceOrCharge;
use Einvoicing\Identifier;
use Einvoicing\Invoice;
use Einvoicing\InvoiceLine;
use Einvoicing\Party;
use Einvoicing\Payments\Payment;
use Einvoicing\Payments\Transfer;
use Einvoicing\Presets;
use Einvoicing\Writers\UblWriter;
use Techy4m\XmlExports\Data\Client;
use Techy4m\XmlExports\Data\Invoice as InvoiceData;

class PeppolInvoice implements EInvoice
{
    private static UblWriter $writer;
    private InvoiceData $invoice;
    private Client $client;

    public function __construct()
    {
        if (!isset(self::$writer)) {
            self::$writer = new UblWriter();
        }
    }

    public function generate(InvoiceData $eInvoice): string
    {
        $this->invoice = $eInvoice;
        $this->client = $eInvoice->client;

        $xml = new Invoice($this->getPreset());
        $xml->setNumber(format_invoice_number($this->invoice->id))
            ->setIssueDate(Carbon::parse($this->invoice->date)->toDate())
            ->setDueDate(Carbon::parse($this->invoice->duedate)->toDate());

        if (trim($this->invoice->clientnote) !== '') {
            $xml->addNote($this->invoice->clientnote);
        }


        if (get_option('xml_export_peppol_account_number')) {
            $payment = new Payment();
            $payment->setMeansCode('4');
            $payment->addTransfer(
                (new Transfer())
                    ->setAccountId(get_option('xml_export_peppol_account_number'))
                    ->setAccountName(get_option('xml_export_peppol_account_name'))
                    ->setProvider(get_option('xml_export_peppol_bank_name'))
            );
            $xml->setPayment($payment);
        }

        if (!empty($this->invoice->discount_type) && $this->invoice->discount_total > 0) {
            $discount = new AllowanceOrCharge();
            $discount->setReason(_l('invoice_discount'));
            if ((int)$this->invoice->discount_percent === 0) {
                $discount->markAsFixedAmount();
                $discount->setAmount($this->invoice->discount_total);
            } else {
                $discount->markAsPercentage();
                $discount->setAmount($this->invoice->discount_percent);
            }

            $xml->addAllowance($discount);
        }

        if ($this->invoice->adjustment > 0) {
            $adjustment = new AllowanceOrCharge();
            $adjustment->setReason(_l('invoice_adjustment'));
            $adjustment->markAsFixedAmount();
            $adjustment->setAmount($this->invoice->adjustment);
            $xml->addAllowance($adjustment);
        }


        $xml->setCurrency($this->invoice->currency_name);
        $xml->setPaidAmount(collect($this->invoice->payments)->sum('amount'));

        $xml->setSeller($this->getSeller());
        $xml->setBuyer($this->getBuyer());

        $reference = get_custom_field_value($this->invoice->id, get_option('xml_export_invoice_buyer_reference_field'), 'invoice');
        $xml->setBuyerReference($reference);

        foreach (collect($this->invoice->items)->sortBy('item_order') as $item) {
            $xml->addLine($this->prepareLineItem($item));
        }


        return self::$writer->export($xml);
    }

    public function getPreset(): string
    {
        $default = Presets\Peppol::class;
        if (get_option('invoice_company_country_code') == 'RO') {
            return Presets\CiusRo::class;
        }

        return $default;
    }

    protected function getSeller(): Party
    {
        $seller = new Party();
        $seller
            ->setElectronicAddress(
                new Identifier(get_option('xml_export_electronic_address'), get_option('xml_export_electronic_address_scheme'))
            )
            ->setCompanyId(
                new Identifier(get_option('xml_export_company_id'), get_option('xml_export_company_id_scheme'))
            )
            ->setName(get_option('invoice_company_name'))
            ->setVatNumber(get_option('company_vat'))
            ->setAddress(explode(',', get_option('invoice_company_address') ?? ''))
            ->setCity(get_option('invoice_company_city'))
            ->setSubdivision(get_option('company_state'))
            ->setPostalCode(get_option('invoice_company_postal_code'))
            ->setCountry(get_option('invoice_company_country_code'));

        return $seller;
    }

    protected function getBuyer(): Party
    {
        $eAddress = get_custom_field_value($this->client->userid, get_option('xml_export_customer_electronic_address_field'), 'customers');
        $eScheme = get_custom_field_value($this->client->userid, get_option('xml_export_customer_electronic_address_scheme_field'), 'customers');
        $buyer = new Party();
        $buyer
            ->setElectronicAddress(new Identifier(trim($eAddress), trim($eScheme)))
            ->setName($this->client->company)
            ->setContactPhone($this->client->phonenumber)
            ->setPostalCode($this->invoice->billing_zip)
            ->setCity($this->invoice->billing_city)
            ->setAddress(explode(',', $this->invoice->billing_street))
            ->setSubdivision($this->invoice->billing_state)
            ->setVatNumber($this->client->vat ?: null);
        $country = get_country($this->invoice->billing_country);
        if ($country) {
            $buyer->setCountry($country->iso2);
        }

        if ($this->client->show_primary_contact == '1') {
            $fullName = get_contact_full_name(get_primary_contact_user_id($this->client->userid));
            if (!empty($fullName)) {
                $buyer->setName(get_contact_full_name(get_primary_contact_user_id($this->client->userid)));
            }
        }

        return $buyer;
    }

    /**
     * @param $item array{
     *      'description': string,
     *      'long_description': string,
     *     'qty': float,
     *     'rate':float,
     *     'unit': string,
     *     'id': int,
     * }
     * @return InvoiceLine
     */
    protected function prepareLineItem(array $item): InvoiceLine
    {
        $line = new InvoiceLine();
        $line->setName($item['description'])
            ->setDescription($item['long_description'])
            ->setPrice($item['rate'])
            ->setQuantity($item['qty']);

        $tax = collect(get_invoice_item_taxes($item['id']))->first();
        if ($tax !== null && $tax['taxrate'] > 0) {
            $line->setVatRate($tax['taxrate']);
        }

        return $line;
    }
}
