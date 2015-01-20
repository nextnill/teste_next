<?php

class Defect_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('defect/list', 'defect/detail'));
    }

    function list_json($params)
    {
        $list = array();
        $defect_model = $this->LoadModel('Defect', true);

        if (!empty($params) && sizeof($params) > 0) {
            $id = (int)$params[0];
            $list = $defect_model->get_by_quarry($id);
        }
        else {
            $list = $defect_model->get_list();
        }

        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $defect_model = $this->LoadModel('Defect', true);
        $defect_model->populate($id);
        
        $this->print_json($defect_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $defect_model = $this->LoadModel('Defect', true);
        
        if ($id > 0)
        {
            $defect_model->populate($id);
        }
        
        $defect_model->name = $this->ReadPost('name');
        $defect_model->description = $this->ReadPost('description');
        $defect_model->save();

        $this->print_json($defect_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $defect_model = $this->LoadModel('Defect', true);
        
        if ($id > 0)
        {
            $defect_model->populate($id);
            $defect_model->delete();
        }

        $this->print_json($defect_model);
    }

}