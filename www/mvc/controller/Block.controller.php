<?php

class Block_Controller extends \Sys\Controller
{

    function list_action($params)
    {

        $user = $this->ActiveUser();
        $permissions = $user['permissions'];
        $data['permissions'] = $permissions;


        $block_number = null;
        $client_id = null;

        if(isset($_SESSION[SPRE.'bl_block_number'])){  
            $block_number =  $_SESSION[SPRE.'bl_block_number'];
        }

         if(isset($_SESSION[SPRE.'bl_client_id'])){
            $client_id = $_SESSION[SPRE.'bl_client_id'];
        }

        $parametros["client_id"] = $client_id;    
        $parametros["block_number"] = $block_number;

        $data['parametros'] = $parametros;

        $this->RenderView('masterpage', array(
            'block/list',
            'block/detail',
            'production_order/items/defects_marker',
            'production_order/items/photo_upload',
            'production_order/items/photo_view',
        ), $data);
    }

    function list_json($params)
    {
    	$block_model = $this->LoadModel('Block', true);

        $block_number = isset($_GET['block_number']) ? (string)$_GET['block_number'] : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
        $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;            

        $_SESSION[SPRE.'bl_block_number'] = $block_number;
        $_SESSION[SPRE.'bl_client_id'] = $client_id;
        
    	$list = $block_model->get_list($block_number, $limit, $client_id);
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $block_model = $this->LoadModel('Block', true);

        if ($params[0] == 'block_number') {
            $block_number = (string)$params[1];
            $block_model->populate(null, $block_number);
        }
        else {
            $id = (int)$params[0];
            $block_model->populate($id);
        }
        
        $this->print_json($block_model);
    }

    function exists_json($params)
    {
        $block_model = $this->LoadModel('Block', true);

        if ($params[0] == 'block_number') {
            $block_model->block_number = (string)$params[1];
        }
        else {
            $block_model->id = (int)$params[0];
        }
        
        $this->print_json(array('exists' => $block_model->exists()));
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $block_model = $this->LoadModel('Block', true);
        
        if ($id > 0) {
            $block_model->populate($id);
        }
        
        $block_number = (string)$this->ReadPost('block_number');
        $tot_c = (float)$this->ReadPost('tot_c');
        $tot_a = (float)$this->ReadPost('tot_a');
        $tot_l = (float)$this->ReadPost('tot_l');
        $tot_vol = (float)$this->ReadPost('tot_vol');
        $tot_weight = (float)$this->ReadPost('tot_weight');
        $net_c = (float)$this->ReadPost('net_c');
        $net_a = (float)$this->ReadPost('net_a');
        $net_l = (float)$this->ReadPost('net_l');
        $net_vol = (float)$this->ReadPost('net_vol');
        $obs = (string)$this->ReadPost('obs');
        $defects_json = (string)$this->ReadPost('defects_json');
        $quality_id = (int)$this->ReadPost('quality_id');
        $defects = $this->ReadPost('defects');
        $block_number_interim = (string)$this->ReadPost('block_number_interim');

        $block_model->block_number = $block_number;
        $block_model->tot_c = $tot_c;
        $block_model->tot_a = $tot_a;
        $block_model->tot_l = $tot_l;
        $block_model->tot_vol = $tot_vol;
        $block_model->tot_weight = $tot_weight;
        $block_model->net_c = $net_c;
        $block_model->net_a = $net_a;
        $block_model->net_l = $net_l;
        $block_model->net_vol = $net_vol;
        $block_model->obs = $obs;
        $block_model->defects_json = $defects_json;
        $block_model->quality_id = $quality_id;
        $block_model->defects = $defects;
        $block_model->block_number_interim = $block_number_interim;
        $block_model->save();


        $this->print_json($block_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $block_model = $this->LoadModel('Block', true);
        
        if ($id > 0)
        {
            $block_model->populate($id);
            $block_model->delete();
        }

        $this->print_json($block_model);
    }

    function reserve_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $client_block_number = (string)$this->ReadPost('client_block_number');
        $reserved_client_id = (int)$this->ReadPost('reserved_client_id');

        $block_model = $this->LoadModel('Block', true);
        $block_model->reserve($id, $reserved_client_id, $client_block_number);
        
        $this->print_json($block_model);
    }

    function reserve_selected_json($params){

            $id_reserve = json_decode($_POST['id']);
            $blocks_reserved = array();

            for($i=0; $i<count($id_reserve); $i++){
            
            $id = $id_reserve[$i];
            $client_block_number = $this->ReadPost('client_block_number');
            $reserved_client_id = (int)$this->ReadPost('reserved_client_id');

            $block_model = $this->LoadModel('Block', true);
            $block_model->reserve($id, $reserved_client_id, $client_block_number);
            $blocks_reserved = $block_model;
        }
        

        $this->print_json($blocks_reserved);   
    }

    function list_clients_with_reservations_json($params)
    {
        $block_model = $this->LoadModel('Block', true);
        $list = $block_model->get_clients_with_reservations();
        
        $this->print_json($list);
    }

    function list_blocks_without_lot_json($params)
    {
        $client_id = null;
        if (isset($params[0])) {
            $client_id = (int)$params[0];
        }
        
        $block_model = $this->LoadModel('Block', true);
        $list = $block_model->get_without_lot($client_id);
        
        $this->print_json($list);
    }

    function list_blocks_with_lot_json($params)
    {
        $block_number = '';
        if (isset($params[0])) {
            $block_number = (string)$params[0];
        }

        $block_model = $this->LoadModel('Block', true);
        $list = $block_model->get_with_lot($block_number);
        
        $this->print_json($list);
    }

}