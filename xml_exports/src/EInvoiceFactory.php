<?php

namespace Techy4m\XmlExports;

use Techy4m\XmlExports\EInvoice\EInvoice;
use Techy4m\XmlExports\EInvoice\GermanXRechnung;
use Techy4m\XmlExports\EInvoice\ItalianInvoice;
use Techy4m\XmlExports\EInvoice\PeppolInvoice;
use Techy4m\XmlExports\EInvoice\RomanianEInvoice;
use Techy4m\XmlExports\EInvoice\SpainInvoice;
use Techy4m\XmlExports\Enums\Scheme;

class EInvoiceFactory
{
    public static function createHandler(Scheme $scheme): EInvoice
    {
        return match ($scheme) {
            Scheme::Italy => new ItalianInvoice(),
            Scheme::Romania => new RomanianEInvoice(),
            Scheme::Spain => new SpainInvoice(),
            Scheme::Germany => new GermanXRechnung(),
            default => new PeppolInvoice(),
        };

    }
}