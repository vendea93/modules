<?php

namespace Techy4m\XmlExports;

use Techy4m\XmlExports\Data\Invoice;
use Techy4m\XmlExports\EInvoice\EInvoice;

class EInvoiceManager
{
    public function __construct(
        private readonly EInvoice $handler
    )
    {
    }

    public function asXML(Invoice $invoice): string
    {
        return $this->handler->generate($invoice);
    }

    public function asEmailAttachment(Invoice $invoiceObject, string $fileName): array
    {
       return XmlOutputWriter::emailAttachment($fileName, $this->asXML($invoiceObject));
    }
}
