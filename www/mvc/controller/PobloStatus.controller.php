<?php

class PobloStatus_Controller extends \Sys\Controller
{

    function list_action($params)
    {
   
        $this->RenderView('masterpage', array('poblo_status/list', 'poblo_status/detail'));
    }

    function list_json($params)
    {
    	$poblo_status_model = $this->LoadModel('PobloStatus', true);
    	$list = $poblo_status_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $poblo_status_model = $this->LoadModel('PobloStatus', true);
        $poblo_status_model->populate($id);
        
        $this->print_json($poblo_status_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $poblo_status_model = $this->LoadModel('PobloStatus', true);
        
        if ($id > 0)
        {
            $poblo_status_model->populate($id);
        }
        
        $poblo_status_model->status = $this->ReadPost('status');
        $poblo_status_model->cor = $this->ReadPost('cor');       
  
        $ret = $poblo_status_model->save();

        $this->print_json($ret);
    }

     function save_color_json($params)
    {
        $id = (int)$this->ReadPost('invoice_item_id');

        $invoice_item_model = $this->LoadModel('InvoiceItem', true);
        
        if ($id > 0)
        {
            $invoice_item_model->populate($id);
        } 
        
        $invoice_item_model->poblo_status_id = $this->ReadPost('poblo_status_id');

        $ret = $invoice_item_model->save();

        $this->print_json($ret);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');

        $poblo_status_model = $this->LoadModel('PobloStatus', true);
        
        if ($id > 0) {
            $poblo_status_model->populate($id);
            $poblo_status_model->delete();
        }

        $this->print_json($poblo_status_model);
    }

}