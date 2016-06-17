<?php

use \Sys\Util;
use \Sys\DB;

class Price_Controller extends \Sys\Controller
{

    function list_action($params)
    {   
        $user = $this->ActiveUser();
        $permissions = $user['permissions'];

        if(in_array('price', $permissions)){

            $this->RenderView('masterpage', array(
                'price/list',
                'price/detail',
                'price/history'
            ));
        }
        else{

            $this->RenderView(array('masterpage'), array('home'));
        }
    }

    function list_json($params)
    {
        // listar os blocos
        $price_model = $this->LoadModel('Price', true);
        $list = $price_model->get_by_client_group((int)$_GET['client_group_id']);
        
        $this->print_json($list);
    }

    function history_json($params)
    {
        // listar os blocos
        $price_model = $this->LoadModel('Price', true);
        $list = $price_model->get_history((int)$_GET['client_id']);
        
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $price_model = $this->LoadModel('Price', true);
        $price_model->populate($id);
        
        $this->print_json($price_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $price_model = $this->LoadModel('Price', true);
        
        if ($id > 0)
        {
            $price_model->populate($id);
        }
        
        $price_model->client_id = $this->ReadPost('client_id');
        $price_model->date_ref = $this->ReadPost('date_ref');
        $price_model->comments = $this->ReadPost('comments');
        $return_last_price = (bool)$this->ReadPost('return_last_price') === true;
        $price_model->values = json_decode($this->ReadPost('values'));
        
        $ret = $price_model->save();

        if (is_int($ret)) {
            // reload
            if ($return_last_price == true) {
                $price_model->populate_by_client_id($price_model->client_id);
            }
            else {
                $price_model->populate($ret);
            }

            $this->print_json($price_model);
        }
        else if (get_class($ret) == 'Sys\Validation') {
            $this->print_json(array('validation' => $ret));
        }
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $return_last_price = (bool)$this->ReadPost('return_last_price') === true;
        $price_model = $this->LoadModel('Price', true);
        
        if ($id > 0)
        {
            $price_model->populate($id);
            $price_model->delete($return_last_price);

            // reload
            if ($return_last_price == true) {
                $price_model->populate_by_client_id($price_model->client_id);
            }
            else {
                $price_model->populate($id);
            }
        }

        $this->print_json($price_model);
    }
    
}