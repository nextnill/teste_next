<?php

use \Sys\DB;

class Poblo_Controller extends \Sys\Controller
{

    function list_action($params)
    {   
        $user = $this->ActiveUser();
        $permissions = $user['permissions'];

        if(in_array('poblo', $permissions)){
            $this->RenderView('masterpage', array(
                    'poblo/list',
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
        // listar os blocos
        $lot_transport_model = $this->LoadModel('LotTransport', true);
    	$list = $lot_transport_model->get_poblo();
    	
        $this->print_json($list);
    }

    function obs_json($params)
    {
        $lot_number = (string)$this->ReadGet('lot_number');
        $lot_transport_id = (int)$this->ReadGet('lot_transport_id');
        
        // salvar obs dos blocos do poblo
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $obs = $lot_transport_model->get_poblo_obs($lot_number, $lot_transport_id);

        $this->print_json(array(
            'lot_number' => $lot_number,
            'lot_transport_id' => $lot_transport_id,
            'obs' => $obs
        ));
    }

    function salve_obs_json($params)
    {
        $lot_number = (string)$this->ReadPost('lot_number');
        $lot_transport_id = (int)$this->ReadPost('lot_transport_id');
        $obs = (string)$this->ReadPost('obs');
        // salvar obs dos blocos do poblo
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $obs = $lot_transport_model->set_poblo_obs($lot_number, $lot_transport_id, $obs);

        $this->print_json(array(
            'lot_number' => $lot_number,
            'lot_transport_id' => $lot_transport_id,
            'obs' => $obs
        ));
    }
    
}