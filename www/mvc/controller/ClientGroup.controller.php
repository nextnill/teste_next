<?php

class ClientGroup_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('client_group/list', 'client_group/detail'));
    }

    function list_json($params)
    {
    	$client_group_model = $this->LoadModel('ClientGroup', true);
    	$list = $client_group_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $client_group_model = $this->LoadModel('ClientGroup', true);
        $client_group_model->populate($id);
        
        $this->print_json($client_group_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $client_group_model = $this->LoadModel('ClientGroup', true);
        
        if ($id > 0)
        {
            $client_group_model->populate($id);
        }
        
        $client_group_model->name = $this->ReadPost('name');
        $client_group_model->save();

        $this->print_json($client_group_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $client_group_model = $this->LoadModel('ClientGroup', true);
        
        if ($id > 0)
        {
            $client_group_model->populate($id);
            $client_group_model->delete();
        }

        $this->print_json($client_group_model);
    }

    function get_by_user($params)
    {
        $list = array();

        $client_group_model = $this->LoadModel('ClientGroup', true);
        $user = $this->ActiveUser();
        
        if ($user['admin'] === true) {
            $list = $client_group_model->get_list(false, false);
        }
        else {
            $list = $client_group_model->get_by_user((int)$user['id'], false);
            // ajusto o nome dos atributos
            foreach ($list as $list_key => $client_group) {
                $list[$list_key]['id'] = $list[$list_key]['client_group_id'];
                $list[$list_key]['name'] = $list[$list_key]['client_group_name'];
                unset($list[$list_key]['client_group_id']);
                unset($list[$list_key]['client_group_name']);
                unset($list[$list_key]['user_id']);
            }
        }
        
        $this->print_json($list);
    }

}