<?php

namespace Techy4m\XmlExports\EInvoice;

use Techy4m\XmlExports\Data\Invoice;

interface EInvoice
{
    public function generate(Invoice $eInvoice): string;
}