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

class View extends Base_Controller {

    public function invoice($invoice_url_key)
    {
        $this->load->model('invoices/Mdl_invoices');

        $invoice = $this->Mdl_invoices->guest_visible()->where('invoice_url_key', $invoice_url_key)->get();

        if ($invoice->num_rows() == 1)
        {
            $this->load->model('invoices/Mdl_items');
            $this->load->model('invoices/Mdl_invoice_tax_rates');

            $invoice = $invoice->row();
            
            if ($this->session->userdata('user_type') <> 1 and $invoice->invoice_status_id == 2)
            {
                $this->Mdl_invoices->mark_viewed($invoice->invoice_id);
            }

            $data = array(
                'invoice'           => $invoice,
                'items'             => $this->Mdl_items->where('invoice_id', $invoice->invoice_id)->get()->result(),
                'invoice_tax_rates' => $this->Mdl_invoice_tax_rates->where('invoice_id', $invoice->invoice_id)->get()->result(),
                'invoice_url_key'   => $invoice_url_key,
                'flash_message'     => $this->session->flashdata('flash_message')
            );

            $this->load->view('invoice_templates/public/' . $this->Mdl_settings->setting('public_invoice_template') . '.php', $data);
        }
    }

    public function generate_invoice_pdf($invoice_url_key, $stream = TRUE, $invoice_template = NULL)
    {
        $this->load->model('invoices/Mdl_invoices');
        
        $invoice = $this->Mdl_invoices->guest_visible()->where('invoice_url_key', $invoice_url_key)->get();

        if ($invoice->num_rows() == 1)
        {
            $invoice = $invoice->row();

            if (!$invoice_template)
            {
                $invoice_template = $this->Mdl_settings->setting('default_pdf_invoice_template');
            }
            
            $this->load->helper('pdf');
            
            generate_invoice_pdf($invoice->invoice_id, $stream, $invoice_template);
        }
    }

    public function quote($quote_url_key)
    {
        $this->load->model('quotes/Mdl_quotes');

        $quote = $this->Mdl_quotes->guest_visible()->where('quote_url_key', $quote_url_key)->get();

        if ($quote->num_rows() == 1)
        {
            $this->load->model('quotes/Mdl_quote_items');
            $this->load->model('quotes/Mdl_quote_tax_rates');

            $quote = $quote->row();
            
            if ($this->session->userdata('user_type') <> 1 and $quote->quote_status_id == 2)
            {
                $this->Mdl_quotes->mark_viewed($quote->quote_id);
            }

            $data = array(
                'quote'           => $quote,
                'items'           => $this->Mdl_quote_items->where('quote_id', $quote->quote_id)->get()->result(),
                'quote_tax_rates' => $this->Mdl_quote_tax_rates->where('quote_id', $quote->quote_id)->get()->result(),
                'quote_url_key'   => $quote_url_key,
                'flash_message'   => $this->session->flashdata('flash_message')
            );

            $this->load->view('quote_templates/public/' . $this->Mdl_settings->setting('public_quote_template') . '.php', $data);
        }
    }

    public function generate_quote_pdf($quote_url_key, $stream = TRUE, $quote_template = NULL)
    {
        $this->load->model('quotes/Mdl_quotes');

        $quote = $this->Mdl_quotes->guest_visible()->where('quote_url_key', $quote_url_key)->get();
        
        if ($quote->num_rows() == 1)
        {
            $quote = $quote->row();

            if (!$quote_template)
            {
                $quote_template = $this->Mdl_settings->setting('default_pdf_quote_template');
            }
            
            $this->load->helper('pdf');
            
            generate_quote_pdf($quote->quote_id, $stream, $quote_template);            
        }
    }
    
    public function approve_quote($quote_url_key)
    {
        $this->load->model('quotes/Mdl_quotes');
        $this->Mdl_quotes->approve_quote_by_key($quote_url_key);
        redirect('guest/view/quote/' . $quote_url_key);
    }
    
    public function reject_quote($quote_url_key)
    {
        $this->load->model('quotes/Mdl_quotes');
        $this->Mdl_quotes->reject_quote_by_key($quote_url_key);
        redirect('guest/view/quote/' . $quote_url_key);
    }

}

?>