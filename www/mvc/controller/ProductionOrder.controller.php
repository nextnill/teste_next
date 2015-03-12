<?php

class ProductionOrder_Controller extends \Sys\Controller
{

	// WEB PAGES
    function list_action($params)
    {
        $this->RenderView('masterpage', array('production_order/list', 'production_order/detail'));
    }

    function items_action($params)
    {
        $id = (int)$params[0];
        $data['po_id'] = $id;
        
        $this->RenderView(
            'masterpage',
            array(
                'production_order/detail',
                'production_order/items/list',
                'production_order/items/list_item_template',
                'production_order/items/defects_marker',
                'production_order/items/photo_upload',
                'production_order/items/photo_view'
            ) , $data
        );

    }
    
    // JSON SERVICES
    function list_json($params)
    {

        $quarry_id = -1;
        if(isset($params[0])){
            $quarry_id = (int)$params[0];
        }

        $block_type = $this->ReadGet('block_type');
        $ano = $this->ReadGet('ano');
        $mes = $this->ReadGet('mes');

        $quarry_id = ($quarry_id > 0 ? $quarry_id : null);
        $block_type = ($block_type > 0 ? $block_type : null);
        $ano = ($ano > 0 ? $ano : null);
        $mes = ($mes > 0 ? $mes : null);

    	$po_model = $this->LoadModel('ProductionOrder', true);
    	$list = $po_model->get_list($quarry_id, $block_type, $ano, $mes);
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $po_model = $this->LoadModel('ProductionOrder', true);
        $po_model->populate($id);
        
        $this->print_json($po_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $po_model = $this->LoadModel('ProductionOrder', true);
        
        if ($id > 0)
        {
            $po_model->populate($id);
        }
        
        $po_model->quarry_id = $this->ReadPost('quarry_id');
        $po_model->date_production = $this->ReadPost('date_production');
        $po_model->product_id = $this->ReadPost('product_id');
        $po_model->block_type = $this->ReadPost('block_type');

        $po_model->save();

        $this->print_json($po_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $po_model = $this->LoadModel('ProductionOrder', true);
        
        if ($id > 0)
        {
            $po_model->populate($id);
            $po_model->delete();
        }

        $this->print_json($po_model);
    }
}