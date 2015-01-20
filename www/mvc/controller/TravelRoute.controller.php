<?php

class TravelRoute_Controller extends \Sys\Controller
{

    function list_locations_json($params)
    {
        $quarry_model = $this->LoadModel('Quarry', true);
        $quarry_list = $quarry_model->get_list();

        $terminal_model = $this->LoadModel('Terminal', true);
        $terminal_list = $terminal_model->get_list();
        
        $locations['quarry'] = $quarry_list;
        $locations['terminal'] = $terminal_list;

        $this->print_json($locations);
    }

    function list_action($params)
    {
        $this->RenderView('masterpage', array('travel_route/list', 'travel_route/detail'));
    }

    function list_json($params)
    {
    	$travel_route_model = $this->LoadModel('TravelRoute', true);
    	$list = $travel_route_model->get_list();
    	
        $this->print_json($list);
    }

    function list_start_json($params)
    {
        $start_quarry_id = null;
        $start_terminal_id = null;

        $start_type = $params[0];

        if ($start_type == 'q') {
            $start_quarry_id = $params[1];
        }
        else if ($start_type == 't') {
            $start_terminal_id = $params[1];
        }

        $travel_route_model = $this->LoadModel('TravelRoute', true);
        $list = $travel_route_model->get_by_start($start_quarry_id, $start_terminal_id);
        
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $travel_route_model = $this->LoadModel('TravelRoute', true);
        $travel_route_model->populate($id);

        $this->print_json($travel_route_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $travel_route_model = $this->LoadModel('TravelRoute', true);
        
        if ($id > 0)
        {
            $travel_route_model->populate($id);
        }
        $travel_route_model->start_quarry_id = $this->ReadPost('start_quarry_id');
        $travel_route_model->start_terminal_id = $this->ReadPost('start_terminal_id');
        $travel_route_model->end_quarry_id = $this->ReadPost('end_quarry_id');
        $travel_route_model->end_terminal_id = $this->ReadPost('end_terminal_id');
        $travel_route_model->shipping_time = $this->ReadPost('shipping_time');
        $travel_route_model->blocks = $this->ReadPost('blocks');
        
        $this->print_json($travel_route_model->save());
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $travel_route_model = $this->LoadModel('TravelRoute', true);
        
        if ($id > 0)
        {
            $travel_route_model->populate($id);
            $travel_route_model->delete();
        }

        $this->print_json($travel_route_model);
    }
}
