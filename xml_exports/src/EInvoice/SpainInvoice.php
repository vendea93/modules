<?php

namespace Techy4m\XmlExports\EInvoice;

use josemmo\Facturae\Facturae;
use josemmo\Facturae\FacturaeCentre;
use josemmo\Facturae\FacturaeItem;
use josemmo\Facturae\FacturaeParty;
use josemmo\Facturae\FacturaePayment;
use Techy4m\XmlExports\Data\Invoice;
use Techy4m\XmlExports\Enums\SPainTaxType;
use Techy4m\XmlExports\UploadManager;

class SpainInvoice implements EInvoice
{
    public function generate(Invoice $eInvoice): string
    {
        $fac = new Facturae();

        $fac->setNumber(get_option('xml_export_spain_series'), format_invoice_number($eInvoice->id));
        $fac->setIssueDate($eInvoice->date);

        $fac->setBuyer(new FacturaeParty([
            'taxNumber' => $eInvoice->client->vat,
            'name' => $eInvoice->client->getCompany(),
            'address' => $eInvoice->getAddress(),
            'postCode' => $eInvoice->getZip(),
            'town' => $eInvoice->getCity(),
            'province' => $eInvoice->getState(),
            // Detect DNI format: 8 digits + 1 letter
            'isLegalEntity' => $eInvoice->client->vat && !preg_match('/^[0-9]{8}[A-Za-z]$/', $eInvoice->client->vat),
            'centres' => $this->getPublicAdministrationDetails($eInvoice),
            'firstSurname' => $eInvoice->client->getPrimaryContactFirstName(),
            'lastSurname' => $eInvoice->client->getPrimaryContactLastName(),
        ]));


        $firstName = get_option('xml_export_spain_seller_first_name') ?: null;
        $lastName = get_option('xml_export_spain_seller_last_name')?: null;
        if (get_option('xml_export_spain_use_sale_agent_as_seller') == '1') {
            $firstName = $eInvoice->sale_agent->get('firstname', $firstName);
            $lastName = $eInvoice->sale_agent->get('lastname', $lastName);
        }

        $fac->setSeller(new FacturaeParty([
            'taxNumber' => get_option('company_vat'),
            'name' => get_option('invoice_company_name'),
            'address' => get_option('invoice_company_address'),
            'postCode' => get_option('invoice_company_postal_code'),
            'town' => get_option('invoice_company_city'),
            'province' => get_option('company_state'),
            'isLegalEntity' => get_option('company_vat') && !preg_match('/^[0-9]{8}[A-Za-z]$/', get_option('company_vat')),
            'firstSurname' => $firstName,
            'lastSurname' => $lastName,
        ]));

        if (get_option('xml_export_spain_iban') && get_option('xml_export_spain_bic')) {
            $paymentMethod = new FacturaePayment([
                'method' => FacturaePayment::TYPE_TRANSFER,
                'iban' => get_option('xml_export_spain_iban'), // IBAN for payments
                'bic' => get_option('xml_export_spain_bic'), // Bank BIC/SWIFT Code
            ]);
            if ($eInvoice->duedate) {
                $paymentMethod->dueDate = $eInvoice->duedate;
            }
            $fac->addPayment($paymentMethod);
        }

        foreach ($eInvoice->items as $item) {
            $taxes = [];
            foreach ($this->getInvoiceItemTaxes($item['id']) as $tax) {
                $taxes[SPainTaxType::fromName($tax['taxname'])] = $tax['taxrate'];
            }
            $fac->addItem(new FacturaeItem(
                [
                    'name' => $item['description'],
                    'description' => $item['long_description'],
                    'quantity' => $item['qty'],
                    'unitPriceWithoutTax' => $item['rate'],
                    'taxes' => $taxes
                ]

            ));
        }

        if (!empty(get_option('xml_export_spain_certificate'))) {
            $privKey = null;
            if (get_option('xml_export_spain_private_key')) {
                UploadManager::getUploadedFilePath('xml_export_spain_private_key');
            }

            $fac->sign(
                UploadManager::getUploadedFilePath('xml_export_spain_certificate'),
                $privKey,
                get_option('xml_export_spain_certificate_password')
            );
        }

        return $fac->export();
    }

    public function getPublicAdministrationDetails(Invoice $eInvoice): array
    {
        $centres = [];

        if (get_option('xml_export_oficina_contable_code_field') && get_option('xml_export_oficina_contable_name_field')) {
            $centres[] = new FacturaeCentre([
                'role' => FacturaeCentre::ROLE_CONTABLE,
                'code' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_oficina_contable_code_field'), 'customers'),
                'name' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_oficina_contable_name_field'), 'customers'),
                'address' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_oficina_contable_address_field'), 'customers'),
                'postCode' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_oficina_contable_postCode_field'), 'customers'),
                'town' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_oficina_contable_town_field'), 'customers'),
                'province' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_oficina_contable_province_field'), 'customers'),
            ]);
        }

        if (get_option('xml_export_organo_gestor_code_field') && get_option('xml_export_organo_gestor_name_field')) {
            $centres[] = new FacturaeCentre([
                'role' => FacturaeCentre::ROLE_GESTOR,
                'code' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_organo_gestor_code_field'), 'customers'),
                'name' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_organo_gestor_name_field'), 'customers'),
                'address' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_organo_gestor_address_field'), 'customers'),
                'postCode' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_organo_gestor_postCode_field'), 'customers'),
                'town' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_organo_gestor_town_field'), 'customers'),
                'province' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_organo_gestor_province_field'), 'customers'),
            ]);
        }

        if (get_option('xml_export_unidad_tramitadora_code_field') && get_option('xml_export_unidad_tramitadora_name_field')) {
            $centres[] = new FacturaeCentre([
                'role' => FacturaeCentre::ROLE_TRAMITADOR,
                'code' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_unidad_tramitadora_code_field'), 'customers'),
                'name' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_unidad_tramitadora_name_field'), 'customers'),
                'address' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_unidad_tramitadora_address_field'), 'customers'),
                'postCode' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_unidad_tramitadora_postCode_field'), 'customers'),
                'town' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_unidad_tramitadora_town_field'), 'customers'),
                'province' => get_custom_field_value($eInvoice->client->userid, get_option('xml_export_unidad_tramitadora_province_field'), 'customers'),
            ]);
        }


        if (get_option('settings_xml_organo_proponente_code_field') && get_option('settings_xml_organo_proponente_name_field')) {
            $centres[] = new FacturaeCentre([
                'role' => FacturaeCentre::ROLE_PROPONENTE,
                'code' => get_custom_field_value($eInvoice->client->userid, get_option('settings_xml_organo_proponente_code_field'), 'customers'),
                'name' => get_custom_field_value($eInvoice->client->userid, get_option('settings_xml_organo_proponente_name_field'), 'customers'),
                'address' => get_custom_field_value($eInvoice->client->userid, get_option('settings_xml_organo_proponente_address_field'), 'customers'),
                'postCode' => get_custom_field_value($eInvoice->client->userid, get_option('settings_xml_organo_proponente_postCode_field'), 'customers'),
                'town' => get_custom_field_value($eInvoice->client->userid, get_option('settings_xml_organo_proponente_town_field'), 'customers'),
                'province' => get_custom_field_value($eInvoice->client->userid, get_option('settings_xml_organo_proponente_province_field'), 'customers'),
            ]);
        }
        return $centres;
    }

    public function getInvoiceItemTaxes($itemid): array
    {
        $CI = &get_instance();
        $CI->db->where('itemid', $itemid);
        $CI->db->where('rel_type', 'invoice');
        return $CI->db->get(db_prefix() . 'item_tax')->result_array();
    }
}