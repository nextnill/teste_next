<?php

class TravelPlan_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $lot_transport_id = isset($params[0]) ? (int)$params[0] : 0;

        $data['lot_transport_id'] = $lot_transport_id;

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($lot_transport_id);

        $data['lot_transport'] = $lot_transport_model;

        $this->RenderView('masterpage', array(
            'travel_plan/list',
            'travel_plan/detail',
            'travel_plan/import_template',
            'travel_plan/cost'
            ), $data);
    }

    function list_json($params)
    {
        $lot_transport_id = isset($params[0]) ? (int)$params[0] : 0;

    	$travel_plan_model = $this->LoadModel('TravelPlan', true);
    	$list = $travel_plan_model->get_by_lot_transport($lot_transport_id);
    	
        $this->print_json($list);
    }

    function save_json($params)
    {
        $travel_plan_model = $this->LoadModel('TravelPlan', true);
        
        $travel_plan_model->lot_transport_id = $this->ReadPost('lot_transport_id');
        $travel_plan_model->travel_route_id = $this->ReadPost('travel_route_id');
        
        $this->print_json($travel_plan_model->save());
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $travel_plan_model = $this->LoadModel('TravelPlan', true);        
        $travel_plan_model->populate($id);

        $this->print_json($travel_plan_model->delete());
    }

    function import_template_json($params)
    {
        $travel_plan_model = $this->LoadModel('TravelPlan', true);
        
        $lot_transport_id = $this->ReadPost('lot_transport_id');
        $travel_plan_template_id = $this->ReadPost('travel_plan_template_id');
        
        $this->print_json($travel_plan_model->import_template($lot_transport_id, $travel_plan_template_id));
    }
    
}
