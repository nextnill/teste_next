<?php

class ScheduleInspection_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('schedule_inspection/list', 'schedule_inspection/detail'));
    }

    function list_json($params)
    {
        $quarry_id = -1;
        if (isset($params[0])) {
            $quarry_id = (int)$params[0];
        }
        
        $ano = $this->ReadGet('ano');
        $mes = $this->ReadGet('mes');

        $quarry_id = ($quarry_id > 0 ? $quarry_id : null);
        $ano = ($ano > 0 ? $ano : null);
        $mes = ($mes > 0 ? $mes : null);

    	$schedule_inspection_model = $this->LoadModel('ScheduleInspection', true);
    	$list = $schedule_inspection_model->get_list(false, $quarry_id, $ano, $mes);
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $schedule_inspection_model = $this->LoadModel('ScheduleInspection', true);
        $schedule_inspection_model->populate($id);
        
        $this->print_json($schedule_inspection_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $schedule_inspection_model = $this->LoadModel('ScheduleInspection', true);
        
        if ($id > 0)
        {
            $schedule_inspection_model->populate($id);
        }
        
        $schedule_inspection_model->day = $this->ReadPost('day');
        $schedule_inspection_model->time = $this->ReadPost('time');
        $schedule_inspection_model->quarry_id = $this->ReadPost('quarry_id');
        $schedule_inspection_model->client_id = $this->ReadPost('client_id');
        $schedule_inspection_model->obs = $this->ReadPost('obs');

        $schedule_inspection_model->save();

        $this->print_json($schedule_inspection_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $schedule_inspection_model = $this->LoadModel('ScheduleInspection', true);
        
        if ($id > 0)
        {
            $schedule_inspection_model->populate($id);
            $schedule_inspection_model->delete();
        }

        $this->print_json($schedule_inspection_model);
    }

}