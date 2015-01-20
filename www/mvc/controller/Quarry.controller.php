<?php

class Quarry_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $this->RenderView('masterpage', array('quarry/list', 'quarry/detail'));
    }

    function list_json($params)
    {
    	$quarry_model = $this->LoadModel('Quarry', true);
    	$list = $quarry_model->get_list();
    	
        $this->print_json($list);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $quarry_model = $this->LoadModel('Quarry', true);
        $quarry_model->populate($id);
        
        $this->print_json($quarry_model);
    }

    function save_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $quarry_model = $this->LoadModel('Quarry', true);
        
        if ($id > 0)
        {
            $quarry_model->populate($id);
        }
        
        $quarry_model->name = $this->ReadPost('name');
        $quarry_model->products = $this->ReadPost('products');
        $quarry_model->defects = $this->ReadPost('defects');
        $quarry_model->final_block_number = $this->ReadPost('final_block_number');
        $quarry_model->interim_block_number = $this->ReadPost('interim_block_number');
        $quarry_model->seq_final = $this->ReadPost('seq_final');
        $quarry_model->seq_interim = $this->ReadPost('seq_interim');
        $quarry_model->save();
        
        $this->print_json($quarry_model);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $quarry_model = $this->LoadModel('Quarry', true);
        
        if ($id > 0)
        {
            $quarry_model->populate($id);
            $quarry_model->delete();
        }

        $this->print_json($quarry_model);
    }

    function next_val_final_json($params)
    {
        $id = (int)$params[0];
        $qtd = 1;
        if (isset($params[1])) {
            $qtd = (int)$params[1];
        }
        
        $block_numbers = array();

        $this->LoadModel('Quarry');
        for ($i=0; $i < $qtd; $i++) { 
            $next_val = Quarry_Model::next_val_final($id);
            $block_numbers[] = array('block_number' => $next_val);
        }

        $this->print_json($block_numbers);
    }

    function next_val_interim_json($params)
    {
        $id = (int)$params[0];
        $qtd = 1;
        if (isset($params[1])) {
            $qtd = (int)$params[1];
        }
        
        $block_numbers = array();

        $this->LoadModel('Quarry');
        for ($i=0; $i < $qtd; $i++) { 
            $next_val = Quarry_Model::next_val_interim($id);
            $block_numbers[] = array('block_number' => $next_val);
        }

        $this->print_json($block_numbers);
    }

}