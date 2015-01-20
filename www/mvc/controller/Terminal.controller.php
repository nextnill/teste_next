<?php

use \Sys\DB;

class Terminal_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('terminal/list', 'terminal/detail'));
    }

    function list_json($params)
    {
        $type = null;

        $terminal_model = $this->LoadModel('Terminal', true);

        if (isset($params[0])) {
            switch ($params[0]) {
                case 'rail':
                    $type = $terminal_model::TERMINAL_TYPE_RAIL;
                    break;
                case 'port':
                    $type = $terminal_model::TERMINAL_TYPE_PORT;
                    break;
            }
        }
    	
    	$list = $terminal_model->get_list(false, $type);
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $terminal_model = $this->LoadModel('Terminal', true);
        $terminal_model->populate($id);

        $this->print_json($terminal_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $terminal_model = $this->LoadModel('Terminal', true);
        
        if ($id > 0)
        {
            $terminal_model->populate($id);
        }
        $terminal_model->type = $this->ReadPost('type');
        $terminal_model->name = $this->ReadPost('name');
        $terminal_model->code = $this->ReadPost('code');
        $terminal_model->shipping_cost_ton = $this->ReadPost('shipping_cost_ton');
        $terminal_model->shipping_cost_fixed = $this->ReadPost('shipping_cost_fixed');
        $terminal_model->country = $this->ReadPost('country');
        $terminal_model->contact = $this->ReadPost('contact');
        $terminal_model->telephone = $this->ReadPost('telephone');
        $terminal_model->mobile = $this->ReadPost('mobile');
        $terminal_model->fax = $this->ReadPost('fax');
        $terminal_model->email = $this->ReadPost('email');
        $terminal_model->contact_other = $this->ReadPost('contact_other');
        $terminal_model->obs = $this->ReadPost('obs');
        $terminal_model->wagon_number = DB::check_to_sql($this->ReadPost('wagon_number'));

        //print_r($terminal_model);exit;
        
        $terminal_model->save();
        
        $this->print_json($terminal_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $terminal_model = $this->LoadModel('Terminal', true);
        
        if ($id > 0)
        {
            $terminal_model->populate($id);
            $terminal_model->delete();
        }

        $this->print_json($terminal_model);
    }

}