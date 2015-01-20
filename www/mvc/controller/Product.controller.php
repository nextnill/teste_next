<?php

class Product_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('product/list', 'product/detail'));
    }

    function list_json($params)
    {
        $quarry_id = $this->ReadGet('quarry');
    	$product_model = $this->LoadModel('Product', true);

        if (!empty($quarry_id))
            $list = $product_model->get_by_quarry($quarry_id);
        else
            $list = $product_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $product_model = $this->LoadModel('Product', true);
        $product_model->populate($id);
        
        $this->print_json($product_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $product_model = $this->LoadModel('Product', true);
        
        if ($id > 0)
        {
            $product_model->populate($id);
        }
        
        $product_model->name = $this->ReadPost('name');
        $product_model->weight_vol = $this->ReadPost('weight_vol');
        
        $product_model->save();

        $this->print_json($product_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $product_model = $this->LoadModel('Product', true);
        
        if ($id > 0)
        {
            $product_model->populate($id);
            $product_model->delete();
        }

        $this->print_json($product_model);
    }

}