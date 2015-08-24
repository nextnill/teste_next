<?php

use \Sys\Util;
use \Sys\DB;

class Poblo_Controller extends \Sys\Controller
{

    function list_action($params)
    {   
        $user = $this->ActiveUser();
        $permissions = $user['permissions'];

        if(in_array('poblo', $permissions)){

            $this->RenderView('masterpage', array(
                    'poblo/new_list_poblo',
                    'poblo/obs',
                    'block/detail',
                    'production_order/items/defects_marker',
                    'production_order/items/photo_upload',
                    'production_order/items/photo_view',
                    'poblo/down_packing_list',
                    'poblo/down_commercial_invoice',
                    'poblo/up_draft'
                    ));
        }
        else{

            $this->RenderView(array('masterpage'), array('home'));
        }
    }

    function list_json($params)
    {
        $client_id = -1;
        if (isset($params[0])) {
            $client_id = (int)$params[0];
        }
        $client_id = ($client_id > 0 ? $client_id : null);

        // listar os blocos
        $lot_transport_model = $this->LoadModel('LotTransport', true);
    	

        $list['lot'] = $lot_transport_model->get_lot($client_id);
        $list['sobracolumay'] = $lot_transport_model->get_sobracolumay($client_id);
        $list['inspection_certificate'] = $lot_transport_model->get_inspection_certificate($client_id);
    	
        $this->print_json($list);
    }

       function list_poblo_json($params)
    {
        // listar os blocos
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $list = $lot_transport_model->get_poblo();
        
        $this->print_json($list);
    }


    function obs_json($params)
    {
        $block_id = (string)$this->ReadGet('block_id');
        
        // salvar obs dos blocos do poblo
        $block = $this->LoadModel('Block', true);
        $obs = $block->get_poblo_obs($block_id);

        $this->print_json(array('obs' => $obs));
    }

    function salve_obs_json($params)
    {

        $block_id = (int)$this->ReadPost('block_id');
        $obs = (string)$this->ReadPost('obs');

        // salvar obs dos blocos do poblo
        $block = $this->LoadModel('Block', true);
        $obs = $block->set_poblo_obs($block_id, $obs);

        $this->print_json(array('obs' => $obs));
    }
    
}