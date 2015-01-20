<?php

use \Sys\DB;

class LotTransportCost_Controller extends \Sys\Controller
{

    function detail_json($params)
    {
        $id = (int)$params[0];
        $lot_transport_cost_model = $this->LoadModel('LotTransportCost', true);
        $lot_transport_cost_model->populate($id);

        $this->print_json($lot_transport_cost_model);
    }

    function lot_detail_json($params)
    {
        $lot_transport_id = (int)$params[0];
        $lot_transport_cost_model = $this->LoadModel('LotTransportCost', true);
        $list = $lot_transport_cost_model->get_by_lot_transport($lot_transport_id);

        $this->print_json($list);
    }

    function save_json($params)
    {
        $lot_transport_id = (int)$this->ReadPost('lot_transport_id');
        $costs = $this->ReadPost('costs');
        $costs_route = $this->ReadPost('costs_route');
        
        $lot_transport_cost_model = $this->LoadModel('LotTransportCost', true);
        $lot_transport_cost_model->save($lot_transport_id, $costs, $costs_route);
    }

}