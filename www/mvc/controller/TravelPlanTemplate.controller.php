<?php

class TravelPlanTemplate_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('travel_plan_template/list', 'travel_plan_template/detail', 'travel_plan_template/detail_add_route'));
    }

    function list_json($params)
    {
    	$travel_plan_template_model = $this->LoadModel('TravelPlanTemplate', true);
    	$list = $travel_plan_template_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $travel_plan_template_model = $this->LoadModel('TravelPlanTemplate', true);
        $travel_plan_template_model->populate($id);

        $this->print_json($travel_plan_template_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $travel_plan_template_model = $this->LoadModel('TravelPlanTemplate', true);
        
        if ($id > 0)
        {
            $travel_plan_template_model->populate($id);
        }
        $travel_plan_template_model->description = $this->ReadPost('description');
        $travel_plan_template_model->items = $this->ReadPost('routes');
        
        $ret = $travel_plan_template_model->save();
        
        $this->print_json($ret);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $travel_plan_template_model = $this->LoadModel('TravelPlanTemplate', true);
        
        if ($id > 0)
        {
            $travel_plan_template_model->populate($id);
            $travel_plan_template_model->delete();
        }

        $this->print_json($travel_plan_template_model);
    }

}