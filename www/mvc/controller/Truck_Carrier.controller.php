<?php

use \Sys\DB;

class Truck_Carrier_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('truck_carrier/list', 'truck_carrier/detail'));
    }

    function list_json($params)
    {
    	$truck_carrier_model = $this->LoadModel('Truck_Carrier', true);
    	$list = $truck_carrier_model->get_list();
    	

        $this->print_json($list);
    }


    function detail_json($params)
    {
        $id = (int)$params[1];
        
        $truck_carrier_model = $this->LoadModel('Truck_Carrier', true);
        $truck_carrier_model->populate($id);

        $this->print_json($truck_carrier_model);
    }   

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $truck_carrier_model = $this->LoadModel('Truck_Carrier', true);

        if ($id > 0)
        {
            $truck_carrier_model->populate($id);
        }
        $truck_carrier_model->name = $this->ReadPost('name');
        $truck_carrier_model->code_trucks = json_decode($this->ReadPost('code_trucks'));
        

        $truck_carrier_model->save();
        
        $this->print_json($truck_carrier_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $truck_carrier_model = $this->LoadModel('Truck_Carrier', true);
        
        if ($id > 0)
        {
            $truck_carrier_model->populate($id);
            $truck_carrier_model->delete();
        }

        $this->print_json($truck_carrier_model);
    }

}