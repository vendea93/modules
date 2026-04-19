<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Techy4m\XmlExports\Bulk\BulkExporter;
use Techy4m\XmlExports\Bulk\BulkExporterConfig;
use Techy4m\XmlExports\Data\Invoice;
use Techy4m\XmlExports\EInvoiceFactory;
use Techy4m\XmlExports\EInvoiceManager;
use Techy4m\XmlExports\Enums\Scheme;
use Techy4m\XmlExports\UploadManager;
use Techy4m\XmlExports\XmlOutputWriter;

/**
 * @property-read  Invoices_model $invoices_model
 * @property-read App_Input $input
 */
class Xml_exports extends AdminController
{

    private EInvoiceManager $eInvoiceManager;

    public function __construct()
    {
        parent::__construct();
        $scheme = Scheme::from(get_option('xml_export_active_scheme'));
        $handler = EInvoiceFactory::createHandler($scheme);
        $this->eInvoiceManager = new EInvoiceManager($handler);
    }

    public function bulk_export(): void
    {
        if (!staff_can('bulk', 'xml_exports')) {
            access_denied('view xml_exports');
        }
        $this->load->model('invoices_model');

        if ($this->input->post()) {
            $config = new BulkExporterConfig(
                'invoices',
                staff_can('view', 'invoices'),
                $this->input->post('date-from'),
                $this->input->post('date-to'),
                $this->input->post('status'),
            );

            $bulkExporter = new BulkExporter($config);
            $bulkExporter->exportInvoices();
            return;
        }

        $features = ['invoices' => _l('bulk_export_pdf_invoices'),];
        $data['features'] = $features;
        $data['title'] = _l('Bulk_xml_exports');
        $data['bodyclass'] = 'invoices-total-manual';
        $data['invoiceStatuses'] = $this->invoices_model->get_statuses();


        $this->load->view('xml_exports/bulk_export', $data);
    }

    public function invoice($id, $action = 'download'): void
    {
        if (!staff_can('view', 'xml_exports')) {
            access_denied('view xml_exports');
        }

        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($id);
        $fileName = format_invoice_number($invoice->id) . '.xml';

        $xml = $this->eInvoiceManager->asXML(new Invoice($invoice));
        if ($action === 'download') {
            XmlOutputWriter::download($fileName, $xml);
        } else {
            XmlOutputWriter::stream($fileName, $xml);
        }
    }

    public function delete_certificate($scheme): void
    {
        if (staff_cant('edit', 'settings')) {
            access_denied('settings');
        }

        $type = Scheme::from($scheme);

        if ($type == Scheme::Italy) {
            unlink(UploadManager::getUploadedFilePath('xml_export_italy_certificate'));
            update_option('xml_export_italy_certificate', '');
        } elseif ($type == Scheme::Spain) {
            unlink(UploadManager::getUploadedFilePath('xml_export_spain_certificate'));
            update_option('xml_export_spain_certificate', '');
        }

        set_alert('success', 'Certificate deleted successfully');

        redirect(admin_url('settings?group=xml_exports'));
    }

    public function delete_private_key($scheme): void
    {
        if (staff_cant('edit', 'settings')) {
            access_denied('settings');
        }

        $type = Scheme::from($scheme);

        if ($type == Scheme::Italy) {
            unlink(UploadManager::getUploadedFilePath('xml_export_italy_private_key'));
            update_option('xml_export_italy_private_key', '');
        } elseif ($type == Scheme::Spain) {
            unlink(UploadManager::getUploadedFilePath('xml_export_spain_private_key'));
            update_option('xml_export_spain_private_key', '');
        }

        set_alert('success', 'Private Key deleted successfully');
        redirect(admin_url('settings?group=xml_exports'));
    }
}
