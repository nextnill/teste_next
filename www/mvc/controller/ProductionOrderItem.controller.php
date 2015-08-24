<?php

use \Sys\Validation;

class ProductionOrderItem_Controller extends \Sys\Controller
{

    function header_json($params)
    {
        $id = (int)$params[0];
        $poi_model = $this->LoadModel('ProductionOrderItem', true);
        $header = $poi_model->get_header($id);

        $this->print_json($header);
    }

    function blocks_json($params)
    {
    	$id = (int)$params[0];
    	$poi_model = $this->LoadModel('ProductionOrderItem', true);
        $blocks = $poi_model->get_by_po($id);

        $this->print_json($blocks);
    }

    function save_json($params)
    {
        $blocks = json_decode($this->ReadPost('blocks'));
        $production_order_id = (int)$this->ReadPost('production_order_id');
        $ret = array();
        $confirm = ($this->ReadPost('confirm') == 'true');

        if ($blocks) {

            foreach ($blocks as $key => $block) {
                
                $id = 0;
                $block_id = 0;
                $removed = false;
                $block_number = '';
                $tot_c = 0;
                $tot_a = 0;
                $tot_l = 0;
                $tot_vol = 0;
                $tot_weight = 0;
                $net_c = 0;
                $net_a = 0;
                $net_l = 0;
                $net_vol = 0;
                $obs = '';
                $defects_json = null;
                $quality_id = null;
                $defects = array();

                if (isset($block['id'])) {
                    $id = (int)$block['id'];
                }

                 if (isset($block['block_id'])) {
                    $block_id = (int)$block['block_id'];
                }

                if (isset($block['removed'])) {
                    $removed = $block['removed'] === 'true';
                }

                if (isset($block['block_number'])) {
                    $block_number = $block['block_number'];
                }

                if (isset($block['quality_id'])) {
                    $quality_id = (int)$block['quality_id'];
                }

                if (isset($block['tot_c'])) {
                    $tot_c = (float)$block['tot_c'];
                }

                if (isset($block['tot_a'])) {
                    $tot_a = (float)$block['tot_a'];
                }

                if (isset($block['tot_l'])) {
                    $tot_l = (float)$block['tot_l'];
                }

                if (isset($block['tot_vol'])) {
                    $tot_vol = (float)$block['tot_vol'];
                }

                if (isset($block['tot_weight'])) {
                    $tot_weight = (float)$block['tot_weight'];
                }

                if (isset($block['net_c'])) {
                    $net_c = (float)$block['net_c'];
                }

                if (isset($block['net_a'])) {
                    $net_a = (float)$block['net_a'];
                }

                if (isset($block['net_l'])) {
                    $net_l = (float)$block['net_l'];
                }

                if (isset($block['net_vol'])) {
                    $net_vol = (float)$block['net_vol'];
                }

                if (isset($block['obs'])) {
                    $obs = $block['obs'];
                }

                if (isset($block['defects_json'])) {
                    $defects_json = $block['defects_json'];
                }
                

                if (isset($block['defects'])) {
                    $defects = $block['defects'];
                }
                
                $poi_model = $this->LoadModel('ProductionOrderItem', true);
                $poi_model->id = $id;
                $poi_model->block_id = $block_id;
                $poi_model->production_order_id = $production_order_id;
                $poi_model->block_number = $block_number;
                $poi_model->quality_id = ($quality_id == 0 ? null : $quality_id);
                $poi_model->tot_c = $tot_c;
                $poi_model->tot_a = $tot_a;
                $poi_model->tot_l = $tot_l;
                $poi_model->tot_vol = $tot_vol;
                $poi_model->tot_weight = $tot_weight;
                $poi_model->net_c = $net_c;
                $poi_model->net_a = $net_a;
                $poi_model->net_l = $net_l;
                $poi_model->net_vol = $net_vol;
                $poi_model->obs = $obs;
                $poi_model->defects_json = $defects_json;

                $poi_model->defects = $defects;
                
                if ($removed) {
                    $poi_model->delete();
                }
                else {
                    $ret[] = $poi_model->save();
                }
            }

            $fail = false;
            foreach ($ret as $key => $value) {
                $class = get_class($value);
                if ($class == 'Sys\Validation') {
                    $fail = true;
                }
            }

            if ($confirm === true && !$fail) {
                $po_model = $this->LoadModel('ProductionOrder', true);
                $po_model->populate($production_order_id);
                $po_model->confirm();
            }
        }

        $this->print_json($ret);
    }

}