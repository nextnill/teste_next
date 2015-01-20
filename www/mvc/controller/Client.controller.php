<?php

use \Sys\DB;

class Client_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('client/list', 'client/detail'));
    }

    function list_json($params)
    {
    	$client_model = $this->LoadModel('Client', true);
    	$list = $client_model->get_list();
    	
        $this->print_json($list);
    }

    function list_head_office_json($params)
    {
        $client_model = $this->LoadModel('Client', true);
        $list = $client_model->get_list_head_office();
        
        $this->print_json($list);
    }

    function list_without_lot_json($params)
    {
        $block_model = $this->LoadModel('Block', true);
        $list = $block_model->get_clients_without_lot();
        
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $client_model = $this->LoadModel('Client', true);
        $client_model->populate($id);

        $this->print_json($client_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $client_model = $this->LoadModel('Client', true);

        if ($id > 0)
        {
            $client_model->populate($id);
        }
        $client_model->name = $this->ReadPost('name');
        $client_model->code = $this->ReadPost('code');
        $client_model->doc_exig_com_inv = DB::check_to_sql($this->ReadPost('doc_exig_com_inv'));
        $client_model->doc_exig_pack_list = DB::check_to_sql($this->ReadPost('doc_exig_pack_list'));
        $client_model->doc_exig_bl = DB::check_to_sql($this->ReadPost('doc_exig_bl'));
        $client_model->doc_exig_certif_orig = DB::check_to_sql($this->ReadPost('doc_exig_certif_orig'));
        $client_model->doc_exig_proforma_invoice = DB::check_to_sql($this->ReadPost('doc_exig_proforma_invoice'));
        $client_model->doc_exig_fumigation_certificate = DB::check_to_sql($this->ReadPost('doc_exig_fumigation_certificate'));
        $client_model->doc_exig_bill_of_lading = DB::check_to_sql($this->ReadPost('doc_exig_bill_of_lading'));
        $client_model->head_office_id = $this->ReadPost('head_office');
        $client_model->terms_of_payment = $this->ReadPost('terms_of_payment');
        $client_model->contact = $this->ReadPost('contact');
        $client_model->telephone = $this->ReadPost('telephone');
        $client_model->mobile = $this->ReadPost('mobile');
        $client_model->fax = $this->ReadPost('fax');
        $client_model->email = $this->ReadPost('email');
        $client_model->contact_other = $this->ReadPost('contact_other');
        $client_model->eori = $this->ReadPost('eori');   
        $client_model->client_groups = $this->ReadPost('client_groups');
        $client_model->agencies = $this->ReadPost('agencies');
        $client_model->ports = $this->ReadPost('ports');
        $client_model->consignee = $this->ReadPost('consignee');
        $client_model->notify_address = $this->ReadPost('notify_address');
        $client_model->marks = $this->ReadPost('marks');
        $client_model->destination_port = $this->ReadPost('destination_port');
        $client_model->port_of_loading = $this->ReadPost('port_of_loading');
        $client_model->obs_body_of_bl = $this->ReadPost('obs_body_of_bl');
        $client_model->desc_of_goods = $this->ReadPost('desc_of_goods');
        $client_model->obs = $this->ReadPost('obs');

        $client_model->save();
        
        $this->print_json($client_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $client_model = $this->LoadModel('Client', true);
        
        if ($id > 0)
        {
            $client_model->populate($id);
            $client_model->delete();
        }

        $this->print_json($client_model);
    }

}