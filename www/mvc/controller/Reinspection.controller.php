<?php

class Reinspection_Controller extends \Sys\Controller
{

    function list_block_action($params)
    {
        $this->RenderView(
            'masterpage',
            array(
                'reinspection/block_list',
                'reinspection/block'
            )
        );
    }

    function save_block_json($params)
    {
        $block_id = (int)$this->ReadPost('id');
        
        $tot_c = (float)$this->ReadPost('tot_c');
        $tot_a = (float)$this->ReadPost('tot_a');
        $tot_l = (float)$this->ReadPost('tot_l');
        $net_c = (float)$this->ReadPost('net_c');
        $net_a = (float)$this->ReadPost('net_a');
        $net_l = (float)$this->ReadPost('net_l');
        $sale_net_c = (float)$this->ReadPost('sale_net_c');
        $sale_net_a = (float)$this->ReadPost('sale_net_a');
        $sale_net_l = (float)$this->ReadPost('sale_net_l');

        $tot_vol = (float)$this->ReadPost('tot_vol');
        $tot_weight = (float)$this->ReadPost('tot_weight');
        $net_vol = (float)$this->ReadPost('net_vol');
        $sale_net_vol = (float)$this->ReadPost('sale_net_vol');

        $invoice_model = $this->LoadModel('Invoice', true);
        $invoice_model->reinspection($block_id, $tot_c, $tot_a, $tot_l, $net_c, $net_a, $net_l,
                            $sale_net_c, $sale_net_a, $sale_net_l, $tot_vol, $tot_weight, $net_vol, $sale_net_vol);

        $this->print_json($invoice_model);
    }

}