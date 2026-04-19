<?php

namespace Techy4m\XmlExports\EInvoice;

use Carbon\Carbon;
use Einvoicing\AllowanceOrCharge;
use Einvoicing\Invoice;
use Einvoicing\InvoiceLine;
use Einvoicing\Party;
use Einvoicing\Presets\CiusRo;
use Einvoicing\Writers\UblWriter;
use Techy4m\XmlExports\Data\Invoice as InvoiceData;

class RomanianEInvoice implements EInvoice
{
    public function generate(InvoiceData $eInvoice): string
    {
        $xml = new Invoice(CiusRo::class);
        $xml->setNumber(format_invoice_number($eInvoice->id))
            ->setIssueDate(Carbon::parse($eInvoice->date)->toDate())
            ->setDueDate(Carbon::parse($eInvoice->duedate)->toDate())
            ->setCurrency('RON');

        if (!empty($eInvoice->discount_type) && $eInvoice->discount_total > 0) {
            $discount = new AllowanceOrCharge();
            $discount->setReason(_l('invoice_discount'));
            if ((int)$eInvoice->discount_percent === 0) {
                $discount->markAsFixedAmount();
                $discount->setAmount($eInvoice->discount_total);
            } else {
                $discount->markAsPercentage();
                $discount->setAmount($eInvoice->discount_percent);
            }

            $xml->addAllowance($discount);
        }

        if ($eInvoice->adjustment > 0) {
            $adjustment = new AllowanceOrCharge();
            $adjustment->setReason(_l('invoice_adjustment'));
            $adjustment->markAsFixedAmount();
            $adjustment->setAmount($eInvoice->adjustment);
            $xml->addAllowance($adjustment);
        }

        $xml->setSeller(
            (new Party())
                ->setName(get_option('invoice_company_name'))
                ->setPostalCode(get_option('invoice_company_postal_code'))
                ->setAddress(explode(',', get_option('invoice_company_address') ?? ''))
                ->setCity(get_option('invoice_company_city'))
                ->setSubdivision(get_option('company_state'))
                ->setCountry('RO')
                ->setVatNumber(get_option('company_vat'))
        );


        $xml->setBuyer(
            (new Party())
                ->setName($eInvoice->client->company)
                ->setPostalCode($eInvoice->getZip())
                ->setCity($eInvoice->getCity())
                ->setAddress(explode(',', $eInvoice->getAddress()))
                ->setSubdivision($eInvoice->getState())
                ->setCountry('RO')
                ->setVatNumber($eInvoice->client->vat)
        );

        foreach (collect($eInvoice->items)->sortBy('item_order') as $item) {
            $tax = collect(get_invoice_item_taxes($item['id']))
                ->filter(fn($tax) => $tax !== null && $tax['taxrate'] > 0)
                ->first();

            $xml->addLine(
                (new InvoiceLine())
                    ->setName($item['description'])
                    ->setQuantity($item['qty'])
                    ->setPrice($item['rate'])
                    ->setVatRate($tax['taxrate'])
            );
        }
        return (new UblWriter())->export($xml);
    }
}
