<?php

use \Sys\DB;

class Agency_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('agency/list', 'agency/detail'));
    }

    function list_json($params)
    {
    	$agency_model = $this->LoadModel('Agency', true);
    	$list = $agency_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $agency_model = $this->LoadModel('Agency', true);
        $agency_model->populate($id);

        $this->print_json($agency_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $agency_model = $this->LoadModel('Agency', true);
        
        if ($id > 0)
        {
            $agency_model->populate($id);
        }
        $agency_model->shipping_company = $this->ReadPost('shipping_company');
        $agency_model->name = $this->ReadPost('name');
        $agency_model->code = $this->ReadPost('code');
        $agency_model->contact = $this->ReadPost('contact');
        $agency_model->telephone = $this->ReadPost('telephone');
        $agency_model->mobile = $this->ReadPost('mobile');
        $agency_model->fax = $this->ReadPost('fax');
        $agency_model->email = $this->ReadPost('email');
        $agency_model->contact_other = $this->ReadPost('contact_other');
        $agency_model->obs = $this->ReadPost('obs');
        
        $agency_model->save();
        
        $this->print_json($agency_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $agency_model = $this->LoadModel('Agency', true);
        
        if ($id > 0)
        {
            $agency_model->populate($id);
            $agency_model->delete();
        }

        $this->print_json($agency_model);
    }

}