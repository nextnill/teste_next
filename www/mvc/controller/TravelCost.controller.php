<?php

class TravelCost_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('travel_cost/list', 'travel_cost/detail'));
    }

    function list_json($params)
    {
    	$travel_cost_model = $this->LoadModel('TravelCost', true);
    	$list = $travel_cost_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $travel_cost_model = $this->LoadModel('TravelCost', true);
        $travel_cost_model->populate($id);
        
        $this->print_json($travel_cost_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $travel_cost_model = $this->LoadModel('TravelCost', true);
        
        if ($id > 0)
        {
            $travel_cost_model->populate($id);
        }
        
        $travel_cost_model->name = $this->ReadPost('name');
        $travel_cost_model->type = $this->ReadPost('type');

        $this->print_json($travel_cost_model->save());
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $travel_cost_model = $this->LoadModel('TravelCost', true);
        
        if ($id > 0)
        {
            $travel_cost_model->populate($id);
            $travel_cost_model->delete();
        }

        $this->print_json($travel_cost_model);
    }

}