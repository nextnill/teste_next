<?php

class Quality_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('quality/list', 'quality/detail'));
    }

    function list_json($params)
    {
    	$quality_model = $this->LoadModel('Quality', true);
    	$list = $quality_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $quality_model = $this->LoadModel('Quality', true);
        $quality_model->populate($id);
        
        $this->print_json($quality_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $quality_model = $this->LoadModel('Quality', true);
        
        if ($id > 0)
        {
            $quality_model->populate($id);
        }
        
        $quality_model->name = $this->ReadPost('name');
        $quality_model->save();

        $this->print_json($quality_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $quality_model = $this->LoadModel('Quality', true);
        
        if ($id > 0)
        {
            $quality_model->populate($id);
            $quality_model->delete();
        }

        $this->print_json($quality_model);
    }

    function change_order_json($params)
    {
        //$id = (int)$params[0];
        //$type = (string)$params[1];
        $id = (int)$this->ReadPost('id');
        $type = (string)$this->ReadPost('type');
        
        $quality_model = $this->LoadModel('Quality', true);
        $quality_model->populate($id);

        $result = $quality_model->change_order($type);

        $this->print_json($result);
    }

}