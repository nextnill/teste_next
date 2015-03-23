<?php

use \Sys\Validation;

class BlockPhoto_Controller extends \Sys\Controller
{

    function list_json($params)
    {
    	$id = (int)$params[0];
    	$poi_model = $this->LoadModel('ProductionOrderItem', true);
        $blocks = $poi_model->get_by_po($id);

        $this->print_json($blocks);
    }

    function upload_photo_json($params)
    {
        $production_order_item_id = $this->ReadPost('production_order_item_id');
        $block_id = $this->ReadPost('block_id');
        $obs = $this->ReadPost('obs');
        $return = array();

        $files = $_FILES;
        
        $validation = new Validation();

        // verifica se houve erro no upload dos anexos
        foreach ($files as $file) {
            
            if (($file['error'] != UPLOAD_ERR_OK) && ($file['error'] != UPLOAD_ERR_NO_FILE)) {
                
                switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $err = 'Failed to send ' . $file['name'] . '. The file size is larger than allowed (' . ini_get('upload_max_filesize') . ').';
                        $validation->add(Validation::VALID_ERR_FIELD, $err);
                        break;
                    default:
                        $err = 'Failed to send ' . $file['name'];
                        $validation->add(Validation::VALID_ERR_FIELD, $err);
                        break;
                }
            }
        }

        if (sizeof($_POST) == 0) {
            $err = 'Failed to send photo.';
            $validation->add(Validation::VALID_ERR_FIELD, $err);
        }

        if ($validation->isValid()) {
            foreach ($files as $file) {
                $block_photo_model = $this->LoadModel('BlockPhoto', true);
                $block_photo_model->production_order_item_id = $production_order_item_id;
                $block_photo_model->block_id = $block_id;
                $block_photo_model->obs = $obs;
                $return[] = $block_photo_model->save($file);
            }
            $this->print_json(array('photos' => $return));
        }
        else {
            $this->print_json(array('validation' => $validation));
        }        
    }

    function show_photo_json($params)
    {
        $block_photo_id = (int)$params[0];
        $size = (string)$params[1];

        $block_photo_model = $this->LoadModel('BlockPhoto', true);
        $block_photo_model->populate($block_photo_id);
        
        $file_show = $block_photo_model->request_img($size);
        
        // se existe o arquivo solicitado
        if (file_exists($file_show)) {
            header('Content-Type: image/jpeg');
            readfile($file_show);
            exit;
        }
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $block_photo_model = $this->LoadModel('BlockPhoto', true);
        
        if ($id > 0)
        {
            $block_photo_model->populate($id);
            $block_photo_model->delete();
        }

        $this->print_json($block_photo_model);
    }

}