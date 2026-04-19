<?php

namespace Techy4m\XmlExports\EInvoice;

use DOMDocument;
use DOMElement;
use DOMException;
use Techy4m\XmlExports\Data\Invoice;

class GermanXRechnung implements EInvoice
{
    /**
     * @throws DOMException
     */
    public function generate(Invoice $eInvoice): string
    {
        // 1) Prepare line items (hard-coded or dynamic).
        $lineItems = $this->prepareLineItems($eInvoice);

        // 2) Compute all net, tax, and gross totals from the line items.
        [
            'netTotal' => $netTotal,
            'taxTotal' => $taxTotal,
            'grossTotal' => $grossTotal,
            'netByRate' => $netByRate,
            'taxByRate' => $taxByRate,
        ] = $this->computeTotals($lineItems);

        // 3) Create the DOM and root invoice element.
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $invoice = $this->createInvoiceRoot($dom);

        // 4) Build out the invoice body with sub-methods.
        $this->buildInvoice($eInvoice, $dom, $invoice, $lineItems, $netTotal, $taxTotal, $grossTotal, $netByRate, $taxByRate);

        // 5) Return the generated XML string.
        return $dom->saveXML();
    }

    /**
     * Encapsulates the data for the line items.
     * Modify this array or load from a DB to adapt for real usage.
     */
    private function prepareLineItems(Invoice $invoice): array
    {
        $items = [];
        foreach ($invoice->items as $item) {
            $tax = collect(get_invoice_item_taxes($item['id']))
                ->filter(fn($tax) => $tax !== null && $tax['taxrate'] > 0)
                ->first();
            $items[] = [
                'name' => $item['description'],
                'quantity' => $item['qty'],
                'unitCode' => 'C62',
                'price' => $item['rate'],
                'taxRate' => $tax['taxrate'] ?? 0,
                'order' => $item['item_order'],
            ];
        }
        return $items;
    }

    private function computeTotals(array $lineItems): array
    {
        // We'll store sums keyed by taxRate, in case you have multiple rates
        $netSumsByRate = [];
        $taxSumsByRate = [];

        foreach ($lineItems as $item) {
            $lineNet = $item['quantity'] * $item['price'];   // NET line extension
            $lineTax = $lineNet * ($item['taxRate'] / 100);  // line tax

            // Accumulate sums by rate
            $rateKey = (string)$item['taxRate'];
            if (!isset($netSumsByRate[$rateKey])) {
                $netSumsByRate[$rateKey] = 0.0;
                $taxSumsByRate[$rateKey] = 0.0;
            }
            $netSumsByRate[$rateKey] += $lineNet;
            $taxSumsByRate[$rateKey] += $lineTax;
        }

        // Totals across all rates
        $netTotal = array_sum($netSumsByRate);
        $taxTotal = array_sum($taxSumsByRate);
        $grossTotal = $netTotal + $taxTotal;

        return [
            'netTotal' => $netTotal,
            'taxTotal' => $taxTotal,
            'grossTotal' => $grossTotal,
            'netByRate' => $netSumsByRate,
            'taxByRate' => $taxSumsByRate,
        ];
    }

    /**
     * Create the root element <ubl:Invoice> with the UBL namespace
     * and the required "ubl", "cac", "cbc" namespaces.
     */
    private function createInvoiceRoot(DOMDocument $dom): DOMElement
    {
        // Create the root element <ubl:Invoice> with the UBL namespace
        $invoice = $dom->createElementNS(
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
            'ubl:Invoice'
        );

        // Manually add the three namespaces
        $invoice->setAttribute('xmlns:ubl', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        $dom->appendChild($invoice);
        return $invoice;
    }

    /**
     * Build out all parts of the invoice using sub-methods,
     * preserving the original code & structure.
     * @throws DOMException
     */
    private function buildInvoice(
        Invoice     $eInvoice,
        DOMDocument $dom,
        DOMElement  $invoice,
        array       $lineItems,
        float       $netTotal,
        float       $taxTotal,
        float       $grossTotal,
        array       $netByRate,
        array       $taxByRate
    ): void
    {

        // Add basic invoice fields
        $this->addBasicInvoiceFields($eInvoice, $dom, $invoice);

        // Add Accounting Supplier Party
        $this->addAccountingSupplierParty($dom, $invoice, $eInvoice);

        // Add Accounting Customer Party
        $this->addAccountingCustomerParty($eInvoice, $dom, $invoice);


        // Add Payment Means
        $this->addPaymentMeans($dom, $invoice, $eInvoice);

        // Add TaxTotal
        $this->addTaxTotal(
            $dom,
            $invoice,
            $netByRate,
            $taxByRate,
            $taxTotal,
            $eInvoice->currency_name // e.g. "EUR"
        );

        // Add LegalMonetaryTotal
        $this->addLegalMonetaryTotal(
            $dom,
            $invoice,
            $netTotal,
            $taxTotal,
            $grossTotal,
            $eInvoice->currency_name
        );
        // Add line items
        foreach ($lineItems as $lineItem) {
            $this->addInvoiceLine($dom, $invoice, $lineItem);
        }
    }

    /**
     * @throws DOMException
     */
    private function addBasicInvoiceFields(Invoice $eInvoice, DOMDocument $dom, DOMElement $invoice): void
    {
        $invoice->appendChild($dom->createElement(
            'cbc:CustomizationID',
            'urn:cen.eu:en16931:2017#compliant#urn:xeinkauf.de:kosit:xrechnung_3.0'
        ));
        // 2) <cbc:ProfileID>
        $invoice->appendChild($dom->createElement(
            'cbc:ProfileID',
            'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0'
        ));
        // 3) <cbc:ID>
        $number = format_invoice_number($eInvoice->id);
        $invoice->appendChild($dom->createElement('cbc:ID', $number));
        // 4) <cbc:IssueDate>
        $invoice->appendChild($dom->createElement('cbc:IssueDate', $eInvoice->date));
        // 5) <cbc:DueDate>
        $invoice->appendChild($dom->createElement('cbc:DueDate', $eInvoice->duedate));
        // 6) <cbc:InvoiceTypeCode>
        $invoice->appendChild($dom->createElement('cbc:InvoiceTypeCode', '380'));
        // 7) <cbc:DocumentCurrencyCode>
        $invoice->appendChild($dom->createElement('cbc:DocumentCurrencyCode', $eInvoice->currency_name));
        // 8) <cbc:BuyerReference>

        $reference = null;
        if (get_option('xml_export_invoice_buyer_reference_field') !== '') {
            $reference = get_custom_field_value($eInvoice->id, get_option('xml_export_invoice_buyer_reference_field'), 'invoice');
        }
        $invoice->appendChild($dom->createElement('cbc:BuyerReference', $reference ?: $number));
    }

    /**
     * @throws DOMException
     */
    private function addAccountingSupplierParty(DOMDocument $dom, DOMElement $invoice, Invoice $eInvoice): void
    {
        $accSupplierParty = $dom->createElement('cac:AccountingSupplierParty');
        $invoice->appendChild($accSupplierParty);

        $party = $dom->createElement('cac:Party');
        $accSupplierParty->appendChild($party);

        $endpointID = $dom->createElement('cbc:EndpointID', get_option('company_vat'));
        $endpointID->setAttribute('schemeID', '9930');
        $party->appendChild($endpointID);

        // <cac:PostalAddress>
        $postalAddress = $dom->createElement('cac:PostalAddress');
        $party->appendChild($postalAddress);

        $postalAddress->appendChild($dom->createElement('cbc:StreetName', e(get_option('invoice_company_address'))));
        $postalAddress->appendChild($dom->createElement('cbc:CityName', get_option('invoice_company_city')));
        $postalAddress->appendChild($dom->createElement('cbc:PostalZone', get_option('invoice_company_postal_code')));

        $country = $dom->createElement('cac:Country');
        $country->appendChild($dom->createElement('cbc:IdentificationCode', 'DE'));
        $postalAddress->appendChild($country);

        // <cac:PartyTaxScheme>
        $partyTaxScheme = $dom->createElement('cac:PartyTaxScheme');
        $party->appendChild($partyTaxScheme);

        $partyTaxScheme->appendChild($dom->createElement('cbc:CompanyID', get_option('company_vat')));

        $taxScheme = $dom->createElement('cac:TaxScheme');
        $taxScheme->appendChild($dom->createElement('cbc:ID', 'VAT'));
        $partyTaxScheme->appendChild($taxScheme);

        // <cac:PartyLegalEntity>
        $partyLegalEntity = $dom->createElement('cac:PartyLegalEntity');
        $party->appendChild($partyLegalEntity);

        $partyLegalEntity->appendChild($dom->createElement('cbc:RegistrationName', e(get_option('company_name'))));
        $partyLegalEntity->appendChild($dom->createElement('cbc:CompanyID', get_option('company_vat')));

        // <cac:Contact>
         $contact = $dom->createElement('cac:Contact');
         $party->appendChild($contact);

        $fallBackName = get_option('xml_export_germany_seller_contact_person');
        $fallbackPhone = get_option('xml_export_germany_seller_contact_phone');
        $fallbackEmail = get_option('xml_export_germany_seller_contact_email');
        if (get_option('xml_export_germany_use_sale_agent_as_seller') == '1') {
             $contact->appendChild($dom->createElement('cbc:Name', $eInvoice->sale_agent->get('name', $fallBackName)));
             $contact->appendChild($dom->createElement('cbc:Telephone', $eInvoice->sale_agent->get('phone', $fallbackPhone)));
             $contact->appendChild($dom->createElement('cbc:ElectronicMail', $eInvoice->sale_agent->get('email', $fallbackEmail)));
         } else {
             $contact->appendChild($dom->createElement('cbc:Name', $fallBackName));
             $contact->appendChild($dom->createElement('cbc:Telephone', $fallbackPhone));
             $contact->appendChild($dom->createElement('cbc:ElectronicMail', $fallbackEmail));
         }
    }

    /**
     * @throws DOMException
     */
    private function addAccountingCustomerParty(Invoice $eInvoice, DOMDocument $dom, DOMElement $invoice): void
    {
        $accCustomerParty = $dom->createElement('cac:AccountingCustomerParty');
        $invoice->appendChild($accCustomerParty);

        $partyCust = $dom->createElement('cac:Party');
        $accCustomerParty->appendChild($partyCust);

        // <cbc:EndpointID schemeID="9930">
        $endpointID2 = $dom->createElement('cbc:EndpointID', $eInvoice->client->vat);
        $endpointID2->setAttribute('schemeID', '9930');
        $partyCust->appendChild($endpointID2);

        // <cac:PostalAddress>
        $postalAddress2 = $dom->createElement('cac:PostalAddress');
        $partyCust->appendChild($postalAddress2);

        $postalAddress2->appendChild($dom->createElement('cbc:StreetName', e($eInvoice->getAddress())));
        $postalAddress2->appendChild($dom->createElement('cbc:CityName', $eInvoice->getCity()));
        $postalAddress2->appendChild($dom->createElement('cbc:PostalZone', $eInvoice->getZip()));
        $postalAddress2->appendChild($dom->createElement('cbc:CountrySubentity', $eInvoice->getState()));

        $country2 = $dom->createElement('cac:Country');
        $country2->appendChild($dom->createElement('cbc:IdentificationCode', 'DE'));
        $postalAddress2->appendChild($country2);

        // <cac:PartyLegalEntity>
        $partyLegalEntity2 = $dom->createElement('cac:PartyLegalEntity');
        $partyCust->appendChild($partyLegalEntity2);

        $partyLegalEntity2->appendChild($dom->createElement('cbc:RegistrationName', e($eInvoice->client->getCompany())));
        $partyLegalEntity2->appendChild($dom->createElement('cbc:CompanyID', $eInvoice->client->vat));
    }

    private function addPaymentMeans(DOMDocument $dom, DOMElement $invoice, Invoice $eInvoice): void
    {
        $paymentMeans = $dom->createElement('cac:PaymentMeans');
        $invoice->appendChild($paymentMeans);

        // PaymentMeansCode "ZZZ" or "1" or "48"
        $paymentMeans->appendChild($dom->createElement('cbc:PaymentMeansCode', '1'));

        // InstructionNote
//        $paymentMeans->appendChild($dom->createElement('cbc:InstructionNote', site_url('invoice/' . $eInvoice->id . '/' . $eInvoice->hash)));
    }

    /**
     * Builds <cac:TaxTotal> with the correct sums from computeTotals().
     */
    private function addTaxTotal(
        DOMDocument $dom,
        DOMElement  $invoice,
        array       $netByRate,
        array       $taxByRate,
        float       $taxTotal,     // sum of all rates
        string      $currency      // e.g. "EUR"
    ): void
    {
        // The parent <cac:TaxTotal> element
        $taxTotalNode = $dom->createElement('cac:TaxTotal');
        $invoice->appendChild($taxTotalNode);

        // The sum of all tax amounts across all rates
        $taxTotalNode->appendChild($dom->createElement('cbc:TaxAmount',
            number_format($taxTotal, 2, '.', '')
        ))->setAttribute('currencyID', $currency);

        /**
         * For each distinct rate, add one <cac:TaxSubtotal>.
         * If you only ever have one tax rate, just do it once.
         */
        foreach ($netByRate as $rateKey => $netSum) {
            $taxSum = $taxByRate[$rateKey];
            $rate = floatval($rateKey);  // e.g. 19.00

            $taxSubtotal = $dom->createElement('cac:TaxSubtotal');
            $taxTotalNode->appendChild($taxSubtotal);

            // cbc:TaxableAmount
            $taxSubtotal->appendChild($dom->createElement('cbc:TaxableAmount',
                number_format($netSum, 2, '.', '')
            ))->setAttribute('currencyID', $currency);

            // cbc:TaxAmount
            $taxSubtotal->appendChild($dom->createElement('cbc:TaxAmount',
                number_format($taxSum, 2, '.', '')
            ))->setAttribute('currencyID', $currency);

            // <cac:TaxCategory>
            $taxCategory = $dom->createElement('cac:TaxCategory');
            $taxSubtotal->appendChild($taxCategory);

            // "S" = Standard rated, with the percent from $rate
            $taxCategory->appendChild($dom->createElement('cbc:ID', 'S'));
            $taxCategory->appendChild($dom->createElement('cbc:Percent',
                number_format($rate, 2, '.', '')
            ));

            $taxScheme2 = $dom->createElement('cac:TaxScheme');
            $taxScheme2->appendChild($dom->createElement('cbc:ID', 'VAT'));
            $taxCategory->appendChild($taxScheme2);
        }
    }

    /**
     * Builds <cac:LegalMonetaryTotal> with a computed net, tax, and gross.
     * @throws DOMException
     */
    /**
     * Builds <cac:LegalMonetaryTotal> using netTotal, taxTotal, and grossTotal
     * from computeTotals(), ensuring we satisfy BR-CO-15 and BR-CO-10.
     */
    private function addLegalMonetaryTotal(
        DOMDocument $dom,
        DOMElement  $invoice,
        float       $netTotal,   // sum of line net amounts
        float       $taxTotal,   // sum of line taxes
        float       $grossTotal, // net + tax
        string      $currency
    ): void
    {
        $legalMonetaryTotal = $dom->createElement('cac:LegalMonetaryTotal');
        $invoice->appendChild($legalMonetaryTotal);

        // [BR-CO-10]: Must match SUM of <cbc:LineExtensionAmount> in the <cac:InvoiceLine> nodes
        $legalMonetaryTotal->appendChild($dom->createElement('cbc:LineExtensionAmount',
            number_format($netTotal, 2, '.', '')
        ))->setAttribute('currencyID', $currency);

        // The net (VAT excluded)
        $legalMonetaryTotal->appendChild($dom->createElement('cbc:TaxExclusiveAmount',
            number_format($netTotal, 2, '.', '')
        ))->setAttribute('currencyID', $currency);

        // [BR-CO-15]:
        // TaxInclusive = netTotal + taxTotal
        $legalMonetaryTotal->appendChild($dom->createElement('cbc:TaxInclusiveAmount',
            number_format($grossTotal, 2, '.', '')
        ))->setAttribute('currencyID', $currency);

        // If you have a discount at the document level, set it here (else "0.00")
        // For now, assume no additional allowance:
        $legalMonetaryTotal->appendChild($dom->createElement('cbc:AllowanceTotalAmount', '0.00'))
            ->setAttribute('currencyID', $currency);

        // Optional: <cbc:ChargeTotalAmount>, <cbc:PrepaidAmount>, <cbc:PayableRoundingAmount>...
        // ...

        // Finally, <cbc:PayableAmount>: typically same as grossTotal unless you
        // have other allowances or charges.
        $legalMonetaryTotal->appendChild($dom->createElement('cbc:PayableAmount',
            number_format($grossTotal, 2, '.', '')
        ))->setAttribute('currencyID', $currency);
    }


    /**
     * Original first invoice line. Values injected from computeTotals().
     * @throws DOMException
     */
    private function addInvoiceLine(DOMDocument $dom, DOMElement $invoice, array $lineItem): void
    {
        // ----------------------------------------------------------------------------------
        //  <cac:InvoiceLine>
        // ----------------------------------------------------------------------------------
        $invoiceLine1 = $dom->createElement('cac:InvoiceLine');
        $invoice->appendChild($invoiceLine1);

        $invoiceLine1->appendChild($dom->createElement('cbc:ID', $lineItem['order']));

        $invoicedQuantity = $dom->createElement('cbc:InvoicedQuantity', number_format($lineItem['quantity'], 6, '.', ''));
        $invoicedQuantity->setAttribute('unitCode', $lineItem['unitCode']);
        $invoiceLine1->appendChild($invoicedQuantity);

        $lineExtensionAmount = $dom->createElement('cbc:LineExtensionAmount', number_format($lineItem['price'] * $lineItem['quantity'], 2, '.', ''));
        $lineExtensionAmount->setAttribute('currencyID', 'EUR');
        $invoiceLine1->appendChild($lineExtensionAmount);

        $item1 = $dom->createElement('cac:Item');
        $invoiceLine1->appendChild($item1);

        $item1->appendChild($dom->createElement('cbc:Name', $lineItem['name']));

        $classifiedTaxCategory = $dom->createElement('cac:ClassifiedTaxCategory');
        $item1->appendChild($classifiedTaxCategory);

        $classifiedTaxCategory->appendChild($dom->createElement('cbc:ID', 'S'));
        $classifiedTaxCategory->appendChild($dom->createElement('cbc:Percent', number_format($lineItem['taxRate'], 2, '.', '')));

        $taxScheme3 = $dom->createElement('cac:TaxScheme');
        $taxScheme3->appendChild($dom->createElement('cbc:ID', 'VAT'));
        $classifiedTaxCategory->appendChild($taxScheme3);

        $price1 = $dom->createElement('cac:Price');
        $invoiceLine1->appendChild($price1);

        $priceAmount1 = $dom->createElement('cbc:PriceAmount', number_format($lineItem['price'], 6, '.', ''));
        $priceAmount1->setAttribute('currencyID', 'EUR');
        $price1->appendChild($priceAmount1);
    }
}
