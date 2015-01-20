<?php

class User_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        // verificar se o usuário logado é um administrador,
        // caso contrário não poderá fazer esta alteração no objeto
        $session = $this->Session();

        $data['user'] = $session->get_user();
        $this->RenderView('masterpage', array('user/list', 'user/detail', 'user/permission'), $data);
    }

    function list_json($params)
    {
    	$user_model = $this->LoadModel('User', true);
    	$list = $user_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $user_model = $this->LoadModel('User', true);
        $user_model->populate($id);
        
        $this->print_json($user_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $user_model = $this->LoadModel('User', true);
        
        if ($id > 0)
        {
            $user_model->populate($id);
        }
        
        $user_model->name = $this->ReadPost('name');
        $user_model->password = $this->ReadPost('password');
        $user_model->blocked = $this->ReadPost('blocked');
        
        // verificar se o usuário logado é um administrador,
        // caso contrário não poderá fazer esta alteração no objeto
        $session = $this->Session();
        $user = $session->get_user();
        
        if ($user['admin'] === true) {
            $user_model->admin = $this->ReadPost('admin');
        }

        $ret = $user_model->save();

        $this->print_json($ret);
    }

    function save_permissions_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $quarries = $this->ReadPost('quarries');
        $permissions = $this->ReadPost('permissions');

        $user_model = $this->LoadModel('User', true);
        $ret = $user_model->populate($id);
        if ($id > 0) {
            $user_model->quarries = $quarries;
            $user_model->permissions = $permissions;
            $ret = $user_model->save();
        }
        
        $this->print_json($ret);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');

        $user_model = $this->LoadModel('User', true);
        
        if ($id > 0) {
            $user_model->populate($id);
            $user_model->delete();
        }

        $this->print_json($user_model);
    }

}