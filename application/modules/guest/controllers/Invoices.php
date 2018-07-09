<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * FusionInvoice
 * 
 * A free and open source web based invoicing system
 *
 * @package		FusionInvoice
 * @author		Jesse Terry
 * @copyright	Copyright (c) 2012 - 2013 FusionInvoice, LLC
 * @license		http://www.fusioninvoice.com/license.txt
 * @link		http://www.fusioninvoice.com
 * 
 */

class Invoices extends Guest_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('invoices/Mdl_invoices');
    }

    public function index()
    {
        // Display open invoices by default
        redirect('guest/invoices/status/open');
    }

    public function status($status = 'open', $page = 0)
    {
        // Determine which group of invoices to load
        switch ($status)
        {
            case 'paid':
                $this->Mdl_invoices->is_paid()->where_in('fi_invoices.patient_id', $this->user_patients);
                break;
            default:
                $this->Mdl_invoices->is_open()->where_in('fi_invoices.patient_id', $this->user_patients);
                break;

        }

        $this->Mdl_invoices->paginate(site_url('guest/invoices/status/' . $status), $page);
        $invoices = $this->Mdl_invoices->result();

        $this->layout->set(
            array(
                'invoices'           => $invoices,
                'status'             => $status
            )
        );

        $this->layout->buffer('content', 'guest/invoices_index');
        $this->layout->render('layout_guest');
    }

    public function view($invoice_id)
    {
        $this->load->model('invoices/Mdl_items');
        $this->load->model('invoices/Mdl_invoice_tax_rates');
        
        $invoice = $this->Mdl_invoices->where('fi_invoices.invoice_id', $invoice_id)->where_in('fi_invoices.patient_id', $this->user_patients)->get()->row();
        
        if (!$invoice)
        {
            show_404();
        }
        
        $this->Mdl_invoices->mark_viewed($invoice->invoice_id);

        $this->layout->set(
            array(
                'invoice'           => $invoice,
                'items'             => $this->Mdl_items->where('invoice_id', $invoice_id)->get()->result(),
                'invoice_tax_rates' => $this->Mdl_invoice_tax_rates->where('invoice_id', $invoice_id)->get()->result(),
                'invoice_id'        => $invoice_id
            )
        );

        $this->layout->buffer(
            array(
                array('content', 'guest/invoices_view')
            )
        );

        $this->layout->render('layout_guest');
    }

    public function generate_pdf($invoice_id, $stream = TRUE, $invoice_template = NULL)
    {
        $this->load->helper('pdf');
        
        $this->Mdl_invoices->mark_viewed($invoice_id);
        
        generate_invoice_pdf($invoice_id, $stream, $invoice_template);
    }

}

?>