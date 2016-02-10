<?php

use \Sys\Util;

class TravelPlanItem_Controller extends \Sys\Controller
{

    function list_pending_action($params)
    {
        $this->RenderView('masterpage', array(
            'travel_plan_item/list_pending',
            'travel_plan_item/start_shipping',
            'truck_carrier/save_new_truck'

        ));
    }

    function list_history_action($params)
    {
        $this->RenderView('masterpage', array('travel_plan_item/list_history'));
    }

    function list_pending_json($params)
    {

        $travel_plan_item_model = $this->LoadModel('TravelPlanItem', true);

        $list = $travel_plan_item_model->get_list_pending();
        
        $this->print_json($list);
    }

    function start_shipping_json($params)
    {
        $blocks = json_decode($this->ReadPost('blocks'));

        $travel_plan_item_model = $this->LoadModel('TravelPlanItem', false);

        $result = [];
        foreach ($blocks as $key => $item) {
            if(is_object($item)){
                $item = (array)$item;
            }

            
            $travel_plan_item_model = new TravelPlanItem_Model();
            $result[] = $travel_plan_item_model->start_shipping(
                $item['next_travel_plan_id'],
                $item['lot_transport_id'],
                $item['lot_transport_item_id'],
                $item['block_id'],
                $item['invoice_item_id'],
                $item['invoice_item_nf'], 
                $item['invoice_item_price'],
                $item['next_travel_route_id'],
                $item['invoice_item_wagon_number'],
                $item['invoice_date_nf'],
                $item['truck_id']
            );
        }

        $this->print_json($result);
    }

    function mark_completed_json($params)
    {
        $blocks = json_decode($this->ReadPost('blocks'));

        $travel_plan_item_model = $this->LoadModel('TravelPlanItem', true);
        $result = [];
        foreach ($blocks as $key => $item) {

            if(is_array($item)){
                $item = (object)$item;
            }
            $travel_plan_item_model->populate($item->current_travel_plan_item_id);
            $result[] = $travel_plan_item_model->mark_completed();
        }

        $this->print_json($result);
    }

    function client_removed_json($params)
    {
        $blocks = json_decode($this->ReadPost('blocks'));

        $travel_plan_item_model = $this->LoadModel('TravelPlanItem', false);


        $result = [];
        foreach ($blocks as $key => $item) {
            $travel_plan_item_model = new TravelPlanItem_Model();
            
            $result[] = $travel_plan_item_model->client_removed(
                $item->lot_transport_id,
                $item->lot_transport_item_id,
                $item->block_id,
                $item->invoice_item_id,
                $item->invoice_item_nf,
                $item->invoice_item_price,
                $item->invoice_item_wagon_number
            );
        }

        $this->print_json($result);
    }

    function list_history_json($params)
    {
        // $start_date = $this->ReadGet('start_date');
        // $end_date = $this->ReadGet('end_date');

        $lot_transport_id = $this->ReadGet('lot_transport_id');

        $travel_plan_item_model = $this->LoadModel('TravelPlanItem', true);
        $list = $travel_plan_item_model->get_history($lot_transport_id);

        $this->print_json($list);
    }

}
