<?php

use \Sys\DB;

class LotTransport_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $ano = null;
        $mes = null;
        $client_id = null;

        if(isset($_SESSION[SPRE.'lo_ano_filtro'])){  
            $ano =  $_SESSION[SPRE.'lo_ano_filtro'];
        }
        
        if(isset($_SESSION[SPRE.'lo_mes_filtro'])){ 
            $mes = $_SESSION[SPRE.'lo_mes_filtro'];
        }
        
        if(isset($_SESSION[SPRE.'lo_client_id'])){
            $client_id = $_SESSION[SPRE.'lo_client_id'];
        }
        
        $parametro["ano"] = $ano;
        $parametro["mes"] = $mes;
        $parametro["client_id"] = $client_id;

        $this->RenderView('masterpage', array('lot_transport/list', 'lot_transport/dismember', 'lot_transport/dismember_confirm'), $parametro);
    }

    function list_json($params)
    {
    	$client_id = -1;
        if (isset($params[0])) {
            $client_id = (int)$params[0];
        }

        $limit = 0;
        if (isset($params[1])) {
            $limit = $params[1];
        }

        $ano = 0;
        if (isset($params[2])) {
            $ano = $params[2];
        }
        $mes = 0;
        if (isset($params[3])) {
            $mes = $params[3];
        }
        
        $_SESSION[SPRE.'lo_ano_filtro'] = $ano;  
        $_SESSION[SPRE.'lo_mes_filtro'] = $mes;
        $_SESSION[SPRE.'lo_client_id'] = $client_id;

        $client_id = ($client_id > 0 ? $client_id : null);

        $lot_transport_model = $this->LoadModel('LotTransport', true);
    	$list = $lot_transport_model->get_list(false, $client_id, $limit, $ano, $mes);
    	
        $this->print_json($list);
    }

    function detail_action($params)
    {
        $id = 0;
        if (isset($params[0])) {
            $id = (int)$params[0];
        }

        $data['lot_transport_id'] = $id;

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($id);
        
        if ($id == 0) {
            $lot_transport_model->lot_number = $lot_transport_model->next_val_lot_number();
        }

        $data['lot_transport'] = $lot_transport_model;

        $this->RenderView('masterpage', array(
            'lot_transport/detail',
            'travel_plan/list',
            'travel_plan/detail',
            'travel_plan/import_template',
            'travel_plan/cost',
            'lot_transport/add_block',
            'lot_transport/client_list',
            'block/detail',
            'production_order/items/photo_upload',
            'production_order/items/photo_view'
        ), $data);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($id);

        $this->print_json($lot_transport_model);
    }
    
    function next_val_lot_number_json($params)
    {
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $this->print_json(array('lot_number' => $lot_transport_model->next_val_lot_number()));
    }

    function exists_lot_number_json($params)
    {
        $lot_number = $this->ReadGet('lot_number');

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $this->print_json(array('exists' => $lot_transport_model->exists_lot_number($lot_number)));
    }

    function save_json($params)
    {
        $id = $this->ReadPost('id');
        $blocks = json_decode($this->ReadPost('blocks'));
        $lot_number = $this->ReadPost('lot_number');
        $client_id = $this->ReadPost('client_id');

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($id);

        $lot_transport_model->lot_number = $lot_number;
        $lot_transport_model->client_id = $client_id;

        $lot_transport_model->items = $blocks;

        // salvo o cabeçalho (lot_transport)
        $ret = $lot_transport_model->save();

        $this->print_json($ret);
    }

    function release_json($params) {
        $id = $this->ReadPost('id');
        $release = $this->ReadPost('release');

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($id);
        $ret = $lot_transport_model->release($release);

        $this->print_json($ret);
    }

    function change_order_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $type = (string)$this->ReadPost('type');
        
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($id);
        $ret = $lot_transport_model->change_order($type);

        $this->print_json($ret);
    }

    function client_remove_json($params) {

        $lot_transport_id = (int)$this->ReadPost('lot_transport_id');
        $client_remove = $this->ReadPost('client_remove');
        $ignore_itens = $this->ReadPost('ignore_itens');
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($lot_transport_id);
        $lot_transport_model->client_remove = $client_remove;
        $this->print_json($lot_transport_model->save(null, $ignore_itens));
    }

    function local_market_json($params) {

        $lot_transport_id = (int)$this->ReadPost('lot_transport_id');
        $local_market = $this->ReadPost('local_market');
        $ignore_itens = $this->ReadPost('ignore_itens');
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($lot_transport_id);
        $lot_transport_model->local_market = $local_market;
        $this->print_json($lot_transport_model->save(null, $ignore_itens));
    }

    function delete_json($params)
    {
        $id = $this->ReadPost('id');
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($id);

        // apago o cabeçalho (lot_transport)
        $ret = $lot_transport_model->delete();

        $this->print_json($ret);
    }

    function dismember_json($params) {
        $orig_lot_transport_id = $this->ReadPost('orig_lot_transport_id');
        $lot_number = $this->ReadPost('lot_number');
        $items = $this->ReadPost('items');

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($orig_lot_transport_id);
        $ret = $lot_transport_model->dismember($lot_number, $items);

        $this->print_json($ret);
    }
    
}