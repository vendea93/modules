<?php

declare(strict_types=1);

namespace Techy4m\XmlExports\EInvoice;

use DOMDocument;
use DOMException;
use Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Techy4m\XmlExports\Data\Invoice;
use Techy4m\XmlExports\UploadManager;

class ItalianInvoice implements EInvoice
{
    private array $data = [];
    private \DOMDocument $dom;

    public function __construct()
    {
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;
    }

    /**
     * Generate the final invoice XML (and optionally sign it).
     */
    public function generate(Invoice $eInvoice): string
    {
        // 1. Transmission data


        $recipientCode = '0000000';
        if (get_option('xml_export_customer_electronic_address_field')) {
            $recipientCode = get_custom_field_value($eInvoice->client->userid, get_option('xml_export_customer_recipient_code_field'), 'customers');
        }

        // Using "0000000" as a placeholder recipient code if not set elsewhere
        $this->setTransmissionDetails(
            format_invoice_number($eInvoice->number),
            get_option('xml_export_italy_transmission_format'), // e.g. "FPR12"
            $recipientCode
        );

        // 2. Seller
        $this->setSellerDetails();

        // 3. Buyer
        $this->setBuyerDetails(
            'IT',
            $eInvoice->client->vat,
            $eInvoice->client->company,
            $eInvoice->getAddress(),
            $eInvoice->getZip(),
            $eInvoice->getCity(),
            $eInvoice->getState()
        );

        // 4. Invoice details
        $this->setInvoiceDetails(
            format_invoice_number($eInvoice->number),
            $eInvoice->date,
            $eInvoice->currency_name
        );

        // 5. Items
        foreach ($eInvoice->items as $item) {
            $taxRate = 0;
            foreach (get_invoice_item_taxes($item['id']) as $tax) {
                $taxRate += $tax['taxrate'];
            }
            $this->addItem(
                $item['description'],
                $item['qty'] ?: 1,
                (float) $item['rate'],
                $taxRate
            );
        }

        // 6. Build the XML structure
        $this->generateXML();

        // 7. Optionally sign the XML
        if (!empty(get_option('xml_export_italy_certificate'))) {
            $privKey = null;
            if (get_option('xml_export_italy_private_key')) {
                UploadManager::getUploadedFilePath('xml_export_italy_private_key');
            }

            $this->signXML(
                UploadManager::getUploadedFilePath('xml_export_italy_certificate'),
                $privKey,
                get_option('xml_export_italy_certificate_password')
            );
        }

        return $this->getString();
    }

    public function setTransmissionDetails(
        string $progressive,
        string $format,
        string $recipientCode,
        ?string $pecRecipient = null
    ): void {
        $this->data['transmission'] = [
            'progressive'    => $progressive,
            'format'         => $format,       // e.g. "FPR12"
            'recipientCode'  => $recipientCode,
            'pecRecipient'   => $pecRecipient,
        ];
    }

    public function setSellerDetails(): void
    {
        // Fill seller info from get_option calls
        $this->data['seller'] = [
            'countryCode' => 'IT',
            'vatNumber'   => get_option('company_vat'),
            'companyName' => get_option('invoice_company_name'),
            'address'     => get_option('invoice_company_address'),
            'zip'         => get_option('invoice_company_postal_code'),
            'city'        => get_option('invoice_company_city'),
            'province'    => get_option('company_state'),
            // Additional fields if needed...
        ];
    }

    public function setBuyerDetails(
        string $countryCode,
        string $vatNumber,
        string $companyName,
        string $address,
        string $zip,
        string $city,
        string $province
    ): void {
        $this->data['buyer'] = [
            'countryCode' => $countryCode,
            'vatNumber'   => $vatNumber,
            'companyName' => $companyName,
            'address'     => $address,
            'zip'         => $zip,
            'city'        => $city,
            'province'    => $province,
        ];
    }

    public function setInvoiceDetails(
        string $number,
        string $date,
        string $currency
    ): void {
        $this->data['invoice'] = [
            'number'   => $number,
            'date'     => $date,
            'currency' => $currency,
        ];
    }

    public function addItem(
        string $description,
        float|string  $quantity,
        float  $unitPrice,
        float|string  $taxRate
    ): void {
        $lineTotal = $quantity * $unitPrice;
        $taxAmount = $lineTotal * ($taxRate / 100);

        $this->data['items'][] = [
            'description' => $description,
            'quantity'    => $quantity,
            'unitPrice'   => $unitPrice,
            'lineTotal'   => $lineTotal,
            'taxRate'     => $taxRate,
            'taxAmount'   => $taxAmount,
        ];

        if (!isset($this->data['summary'])) {
            $this->data['summary'] = [];
        }

        if (!isset($this->data['summary'][$taxRate])) {
            $this->data['summary'][$taxRate] = [
                'taxRate'   => $taxRate,
                'taxBase'   => 0,
                'taxAmount' => 0,
            ];
        }

        $this->data['summary'][$taxRate]['taxBase']   += $lineTotal;
        $this->data['summary'][$taxRate]['taxAmount'] += $taxAmount;
    }

    /**
     * Build the XML structure, following the FPR12 template
     * with the 'p:' prefix on the root element.
     *
     * @throws DOMException
     */
    public function generateXML(): void
    {
        // Common constants
        $namespace = 'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2';
        $schemaLoc = 'http://www.fatturapa.gov.it/export/fatturazione/sdi/fatturapa/v1.2/Schema_del_file_xml_FatturaPA_versione_1.2.xsd';

        // 1. Create root with prefix p, e.g. <p:FatturaElettronica versione="FPR12">
        $root = $this->dom->createElementNS($namespace, 'p:FatturaElettronica');
        // "versione" from e.g. "FPR12"
        $root->setAttribute('versione', $this->data['transmission']['format'] ?? 'FPR12');

        // 2. Add other namespaces: ds, p, xsi, plus schemaLocation
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:ds',
            'http://www.w3.org/2000/09/xmldsig#'
        );
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:p',
            $namespace
        );
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );

        // Must have a space before the XSD file path
        $root->setAttribute(
            'xsi:schemaLocation',
            "$namespace $schemaLoc"
        );

        $this->dom->appendChild($root);

        // 2. FatturaElettronicaHeader
        $header = $this->dom->createElement('FatturaElettronicaHeader');
        $root->appendChild($header);

        // DatiTrasmissione
        $datiTrasmissione = $this->dom->createElement('DatiTrasmissione');
        $header->appendChild($datiTrasmissione);

        // <IdTrasmittente> - if you want to store these in your code, do so in setTransmissionDetails
        // Hardcoding for example
        $idTrasmittente = $this->dom->createElement('IdTrasmittente');
        $idTrasmittente->appendChild($this->dom->createElement('IdPaese', 'IT'));
        $idTrasmittente->appendChild($this->dom->createElement('IdCodice', '01234567890'));
        $datiTrasmissione->appendChild($idTrasmittente);

        $datiTrasmissione->appendChild($this->dom->createElement('ProgressivoInvio', $this->data['transmission']['progressive'] ?? '00001'));
        $datiTrasmissione->appendChild($this->dom->createElement('FormatoTrasmissione', $this->data['transmission']['format'] ?? 'FPR12'));
        $datiTrasmissione->appendChild($this->dom->createElement('CodiceDestinatario', $this->data['transmission']['recipientCode']));

        // Optionally add <PECDestinatario> if set
        if (!empty($this->data['transmission']['pecRecipient'])) {
            $datiTrasmissione->appendChild(
                $this->dom->createElement('PECDestinatario', $this->data['transmission']['pecRecipient'])
            );
        }

        // If you want an empty <ContattiTrasmittente/>, do:
        // $datiTrasmissione->appendChild($this->dom->createElement('ContattiTrasmittente'));

        // CedentePrestatore
        $cedentePrestatore = $this->dom->createElement('CedentePrestatore');
        $header->appendChild($cedentePrestatore);

        // -> DatiAnagrafici
        $datiAnagraficiCP = $this->dom->createElement('DatiAnagrafici');
        $cedentePrestatore->appendChild($datiAnagraficiCP);

        $idFiscaleIVA_CP = $this->dom->createElement('IdFiscaleIVA');
        $idFiscaleIVA_CP->appendChild($this->dom->createElement('IdPaese', $this->data['seller']['countryCode'] ?? 'IT'));
        $idFiscaleIVA_CP->appendChild($this->dom->createElement('IdCodice', $this->data['seller']['vatNumber'] ?? '01234567890'));
        $datiAnagraficiCP->appendChild($idFiscaleIVA_CP);

        $anagraficaCP = $this->dom->createElement('Anagrafica');
        $anagraficaCP->appendChild($this->dom->createElement('Denominazione', $this->data['seller']['companyName'] ?? 'My Company Srl'));
        $datiAnagraficiCP->appendChild($anagraficaCP);

        // Hardcode or use a stored field for RegimeFiscale
        $datiAnagraficiCP->appendChild($this->dom->createElement('RegimeFiscale', 'RF01'));

        // -> Sede
        $sedeCP = $this->dom->createElement('Sede');
        $sedeCP->appendChild($this->dom->createElement('Indirizzo', $this->data['seller']['address'] ?? 'Via Roma 10'));
        $sedeCP->appendChild($this->dom->createElement('CAP', $this->data['seller']['zip'] ?? '00199'));
        $sedeCP->appendChild($this->dom->createElement('Comune', $this->data['seller']['city'] ?? 'Roma'));
        $sedeCP->appendChild($this->dom->createElement('Provincia', $this->data['seller']['province'] ?? 'RM'));
        $sedeCP->appendChild($this->dom->createElement('Nazione', $this->data['seller']['countryCode'] ?? 'IT'));
        $cedentePrestatore->appendChild($sedeCP);

        // CessionarioCommittente
        $cessionarioCommittente = $this->dom->createElement('CessionarioCommittente');
        $header->appendChild($cessionarioCommittente);

        $datiAnagraficiCC = $this->dom->createElement('DatiAnagrafici');
        $cessionarioCommittente->appendChild($datiAnagraficiCC);

        // For an Italian buyer we typically do IdFiscaleIVA or CodiceFiscale
        $idFiscaleIVA_CC = $this->dom->createElement('IdFiscaleIVA');
        $idFiscaleIVA_CC->appendChild($this->dom->createElement('IdPaese', $this->data['buyer']['countryCode'] ?? 'IT'));
        $idFiscaleIVA_CC->appendChild($this->dom->createElement('IdCodice', $this->data['buyer']['vatNumber'] ?? '09876543210'));
        $datiAnagraficiCC->appendChild($idFiscaleIVA_CC);

        $anagraficaCC = $this->dom->createElement('Anagrafica');
        $anagraficaCC->appendChild($this->dom->createElement('Denominazione', $this->data['buyer']['companyName'] ?? 'BuyerName SpA'));
        $datiAnagraficiCC->appendChild($anagraficaCC);

        // -> Sede
        $sedeCC = $this->dom->createElement('Sede');
        $sedeCC->appendChild($this->dom->createElement('Indirizzo', $this->data['buyer']['address'] ?? 'Piazza Duomo 1'));
        $sedeCC->appendChild($this->dom->createElement('CAP', $this->data['buyer']['zip'] ?? '20121'));
        $sedeCC->appendChild($this->dom->createElement('Comune', $this->data['buyer']['city'] ?? 'Milano'));
        $sedeCC->appendChild($this->dom->createElement('Provincia', $this->data['buyer']['province'] ?? 'MI'));
        $sedeCC->appendChild($this->dom->createElement('Nazione', $this->data['buyer']['countryCode'] ?? 'IT'));
        $cessionarioCommittente->appendChild($sedeCC);

        // 3. FatturaElettronicaBody
        $body = $this->dom->createElement('FatturaElettronicaBody');
        $root->appendChild($body);

        // DatiGenerali
        $datiGenerali = $this->dom->createElement('DatiGenerali');
        $body->appendChild($datiGenerali);

        $datiGeneraliDocumento = $this->dom->createElement('DatiGeneraliDocumento');
        $datiGeneraliDocumento->appendChild($this->dom->createElement('TipoDocumento', 'TD01'));
        $datiGeneraliDocumento->appendChild($this->dom->createElement('Divisa', $this->data['invoice']['currency'] ?? 'EUR'));
        $datiGeneraliDocumento->appendChild($this->dom->createElement('Data', $this->data['invoice']['date'] ?? '2024-12-27'));
        $datiGeneraliDocumento->appendChild($this->dom->createElement('Numero', $this->data['invoice']['number'] ?? 'INV-000016'));
        // Optionally add more fields like <Causale>, etc. if needed
        $datiGenerali->appendChild($datiGeneraliDocumento);

        // DatiBeniServizi
        $datiBeniServizi = $this->dom->createElement('DatiBeniServizi');
        $body->appendChild($datiBeniServizi);

        // Items
        $lineNumber = 1;
        foreach (($this->data['items'] ?? []) as $item) {
            $dettaglioLinee = $this->dom->createElement('DettaglioLinee');

            $dettaglioLinee->appendChild($this->dom->createElement('NumeroLinea', (string) $lineNumber++));
            $dettaglioLinee->appendChild($this->dom->createElement('Descrizione', $item['description']));
            $dettaglioLinee->appendChild($this->dom->createElement('Quantita', (string) $item['quantity']));
            $dettaglioLinee->appendChild($this->dom->createElement('PrezzoUnitario', number_format($item['unitPrice'], 2, '.', '')));
            $dettaglioLinee->appendChild($this->dom->createElement('PrezzoTotale', number_format($item['lineTotal'], 2, '.', '')));
            $dettaglioLinee->appendChild($this->dom->createElement('AliquotaIVA', number_format($item['taxRate'], 2, '.', '')));

            $datiBeniServizi->appendChild($dettaglioLinee);
        }

        // Summaries -> multiple <DatiRiepilogo> blocks
        // If you have only one summary, we can nest them directly
        foreach (($this->data['summary'] ?? []) as $summary) {
            $datiRiepilogo = $this->dom->createElement('DatiRiepilogo');
            $datiRiepilogo->appendChild($this->dom->createElement('AliquotaIVA', number_format($summary['taxRate'], 2, '.', '')));
            $datiRiepilogo->appendChild($this->dom->createElement('ImponibileImporto', number_format($summary['taxBase'], 2, '.', '')));
            $datiRiepilogo->appendChild($this->dom->createElement('Imposta', number_format($summary['taxAmount'], 2, '.', '')));
            $datiRiepilogo->appendChild($this->dom->createElement('EsigibilitaIVA', 'I')); // e.g. immediate
            $datiBeniServizi->appendChild($datiRiepilogo);
        }

        // You can add <DatiPagamento> etc. here if needed, mirroring the structure from the sample.
    }

    /**
     * Sign the XML (XAdES-BES). Same logic from your previous code.
     */
    public function signXML(string $certPath, ?string $keyPath = null, ?string $password = null): void
    {
        $doc = new DOMDocument();
        $doc->loadXML($this->dom->saveXML());

        $dsig = new XMLSecurityDSig();
        $dsig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        $dsig->addReference(
            $doc,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
        );

        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);

        if ($keyPath) {
            $key->loadKey($keyPath, true);
        } elseif ($certPath) {
            $pkcs12 = file_get_contents($certPath);
            if (!openssl_pkcs12_read($pkcs12, $certs, $password)) {
                throw new Exception('Failed to read .pfx certificate.');
            }
            $key->loadKey($certs['pkey']);
        } else {
            throw new Exception('Private key or .pfx certificate is required for signing.');
        }

        $dsig->sign($key);

        // Attach the certificate (recommended)
        if ($certPath && !$keyPath) {
            $dsig->add509Cert($certs['cert']);
        }

        $dsig->appendSignature($doc->documentElement);
        $this->dom = $doc;
    }

    public function getString(): string
    {
        return $this->dom->saveXML();
    }
}
