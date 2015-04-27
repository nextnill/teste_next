<?php

class Inspection_Controller extends \Sys\Controller
{

    function list_client_action($params)
    {
        $this->RenderView('masterpage', array('inspection/client_list'));
    }

    function list_block_action($params)
    {
        $client_id = (int)$params[0];
        $data['client_id'] = $client_id;
        
        $client_model = $this->LoadModel('Client', true);
        $client_model->populate($client_id);
        $data['client'] = $client_model;

        $this->RenderView(
            'masterpage',
            array(
                'inspection/block_list',
                'inspection/add_block',
                'inspection/refuse',
                'block/detail',
                'production_order/items/defects_marker',
                'production_order/items/photo_upload',
                'production_order/items/photo_view',
                'inspection/confirm'
            ),
            $data
        );
    }

    function list_block_json($params)
    {
        $client_id = (int)$params[0];
        $block_model = $this->LoadModel('Block', true);
        $list = $block_model->get_client_reservations($client_id);
        
        $this->print_json($list);
    }

    function save_json($params)
    {
        $client_id = $this->ReadPost('client_id');
        $blocks = $this->ReadPost('blocks');
        $ret = array();

        // totaliza blocos
        $total_refused = 0;
        $total_accepted = 0;

        $blocks_refused = array();
        $blocks_accepted = array();

        foreach ($blocks as $key => $block) {
            if (isset($block['refused']) && ($block['refused'] == 'true')) {
                $blocks_refused[] = $block;
                $total_refused++;
            }
            else {
                $blocks_accepted[] = $block;
                $total_accepted++;
            }

        }

        //print_r($blocks_accepted);
        //print_r($blocks_refused);
        //exit;

        // se algum bloco foi aceito, crio a invoice
        if ($total_accepted > 0) {
            // carrego os models
            $invoice_model = $this->LoadModel('Invoice', true);

            // crio o cabeçalho (invoice)
            $invoice_model->client_id = $client_id;
            $ret['invoice_id'] = $invoice_model->save();

            if ($invoice_model->id > 0) {
                // adiciono os itens (invoice_item)
                foreach ($blocks_accepted as $key => $block) {
                    $invoice_item_model = $this->LoadModel('InvoiceItem', true);
                    $invoice_item_model->invoice_id = $invoice_model->id;
                    $invoice_item_model->block_id = $block['id'];
                    $invoice_item_model->client_id = $client_id;
                    $invoice_item_model->block_number = $block['block_number'];
                    $invoice_item_model->tot_c = $block['tot_c'];
                    $invoice_item_model->tot_a = $block['tot_a'];
                    $invoice_item_model->tot_l = $block['tot_l'];
                    $invoice_item_model->tot_vol = $block['tot_vol'];
                    $invoice_item_model->net_c = $block['net_c'];
                    $invoice_item_model->net_a = $block['net_a'];
                    $invoice_item_model->net_l = $block['net_l'];
                    $invoice_item_model->net_vol = $block['net_vol'];
                    $invoice_item_model->sale_net_c = $block['sale_net_c'];
                    $invoice_item_model->sale_net_a = $block['sale_net_a'];
                    $invoice_item_model->sale_net_l = $block['sale_net_l'];
                    $invoice_item_model->sale_net_vol = $block['sale_net_vol'];
                    $invoice_item_model->tot_weight = $block['tot_weight'];
                    $invoice_item_model->obs = $block['obs'];
                    $invoice_item_model->poblo_status_id = $block['poblo_status_id'];
                    $invoice_item_model->client_block_number = $block['client_block_number'];
                    $invoice_item_model->block_number_interim = $block['block_number_interim'];

                    $ret['invoice_item_id'][] = $invoice_item_model->save();
                }
            }
        }

        // se algum bloco foi recusado, registro no histórico do bloco e removo a reserva
        if ($blocks_refused > 0) {
            foreach ($blocks_refused as $key => $block) {
                $block_refused_model = $this->LoadModel('BlockRefused', true);

                $block_refused_model->block_id = $block['id'];
                $block_refused_model->client_id = $client_id;
                $block_refused_model->reason = $block['refused_reason'];

                $ret['refused_id'][] = $block_refused_model->save();
            }
        }
        $this->print_json($ret);
    }

}