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
                    'poblo/up_draft',
                    'poblo/poblo_edit'
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

        // sobracolumay
        $list['sobracolumay'] = $lot_transport_model->get_sobracolumay($client_id);
    	
        // lot
        $lot_transport_list = $lot_transport_model->get_lot($client_id);
        $lot_transport_return = array();
        $ultimo_lot_transport_id = 0;
        $lot_transport_index = -1;
        foreach ($lot_transport_list as $lot_transport_key => $lot_transport) {
            if ($ultimo_lot_transport_id != $lot_transport['lot_transport_id']) {
                
                array_push($lot_transport_return, array(
                    'lot_transport_id'=>$lot_transport['lot_transport_id'],
                    'lot_number'=>$lot_transport['lot_number'],
                    'down_packing_list'=>$lot_transport['down_packing_list'],
                    'down_commercial_invoice'=>$lot_transport['down_commercial_invoice'],
                    'down_draft'=>$lot_transport['down_draft'],
                    'draft_file'=>$lot_transport['draft_file'],
                    'client_id'=>$lot_transport['client_id'],
                    'client_code'=>$lot_transport['client_code'],
                    'client_name'=>$lot_transport['client_name'],
                    'vessel'=>$lot_transport['vessel'],
                    'packing_list_dated'=>$lot_transport['packing_list_dated'],
                    'shipped_to'=>$lot_transport['shipped_to'],
                    'lot_transport_status'=>$lot_transport['lot_transport_status'],
                    'down_packing_list'=>$lot_transport['down_packing_list'],
                    'draft_file'=>$lot_transport['draft_file'],
                    'down_draft'=>$lot_transport['down_draft'],
                    'down_commercial_invoice'=>$lot_transport['down_commercial_invoice'],
                    'blocks'=>array()
                ));
                
                $ultimo_lot_transport_id = $lot_transport['lot_transport_id'];
            }
            unset($lot_transport['inspection_name']);
            unset($lot_transport['lot_number']);
            unset($lot_transport['down_packing_list']);
            unset($lot_transport['down_commercial_invoice']);
            unset($lot_transport['down_draft']);
            unset($lot_transport['draft_file']);
            unset($lot_transport['sold_client_code']);
            unset($lot_transport['sold_client_name']);
            unset($lot_transport['client_id']);
            unset($lot_transport['client_code']);
            unset($lot_transport['client_name']);
            array_push($lot_transport_return[sizeof($lot_transport_return)-1]['blocks'], $lot_transport);
        }
        $list['lot'] = $lot_transport_return;

        // inspection_certificate
        $inspection_certificate_list = $lot_transport_model->get_inspection_certificate($client_id);
        $inspection_certificate_return = array();
        $ultimo_invoice_id = 0;
        $inspection_certificate_index = -1;
        foreach ($inspection_certificate_list as $inspection_certificate_key => $inspection_certificate) {
            if ($ultimo_invoice_id != $inspection_certificate['invoice_id']) {
                
                array_push($inspection_certificate_return, array(
                    'invoice_id'=>$inspection_certificate['invoice_id'],
                    'inspection_name' => $inspection_certificate['inspection_name'],
                    'sold_client_id' => $inspection_certificate['sold_client_id'],
                    'sold_client_code' => $inspection_certificate['sold_client_code'],
                    'sold_client_name' => $inspection_certificate['sold_client_name'],
                    'blocks'=>array()
                ));
                
                $ultimo_invoice_id = $inspection_certificate['invoice_id'];
            }
            unset($inspection_certificate['inspection_name']);
            unset($inspection_certificate['sold_client_id']);
            unset($inspection_certificate['sold_client_code']);
            unset($inspection_certificate['sold_client_name']);
            //unset($inspection_certificate['invoice_date_record']);
            array_push($inspection_certificate_return[sizeof($inspection_certificate_return)-1]['blocks'], $inspection_certificate);
        }
        $list['inspection_certificate'] = $inspection_certificate_return;
    	
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

    function save_edit($params){

        $invoice_item_id = (int)$this->ReadPost('invoice_item_id');

        $invoice_item_model = $this->LoadModel('InvoiceItem', true);
        $invoice_item_model->populate($invoice_item_id);


        $date_nf = (string)$this->ReadPost('date_nf');
        $nf = (string)$this->ReadPost('nf');
        $price = str_replace(',', '', (string)$this->ReadPost('price'));

        
        if($nf != null){
            $invoice_item_model->nf = $nf;
        }
        
        if($price != null){
            $invoice_item_model->price = $price;
        }

        if($date_nf != ''){
            
            $invoice_item_model->date_nf = $date_nf;
        }

        $invoice_item_model->save();


        $block_id = (int)$this->ReadPost('block_id');
        $wagon_number = (int)$this->ReadPost('wagon_number');

        if($wagon_number > 0){
            $travel_plan_item_model = $this->LoadModel('TravelPlanItem', true);
            $travel_plan_item_model->update_wagon_number($block_id, $wagon_number);
        }
       

        $this->print_json(array('block_id' => $block_id));
    }
    
}