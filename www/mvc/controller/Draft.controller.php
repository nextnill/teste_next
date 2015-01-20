<?php

use \Sys\Validation;

class Draft_Controller extends \Sys\Controller
{

    function list_json($params)
    {
    	$id = (int)$params[0];
    	$poi_model = $this->LoadModel('LotTransport', true);
        $lot = $poi_model->get_by_po($id);

        $this->print_json($lot);
    }

    function upload_file_json($params)
    {
        $draft_lot_id = $this->ReadPost('id');
        
        
        $return = array();

        $files = $_FILES;
        //print_r($files);
       // exit;
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
            $err = 'Failed to send file.';
            $validation->add(Validation::VALID_ERR_FIELD, $err);
        }

        if ($validation->isValid()) {
                $file = $files[0];
           // foreach ($files as $file) {
                $lot_file_model = $this->LoadModel('LotTransport', true);
                $lot_file_model->populate($draft_lot_id);
                
               // print_r($lot_file_model);
                //exit;
                $return = $lot_file_model->save($file);
            //}
            $this->print_json(array('files' => $return));
        }
        else {
            $this->print_json(array('validation' => $validation));
        }        
    }

    function download_file_json($params){

    $draft_lot_id = $this->ReadGet('id');
    $lot_file_model = $this->LoadModel('LotTransport', true);
    $lot_file_model->populate($draft_lot_id); 


    $file = DRAFT_PATH."/anx_".$draft_lot_id;    
   
    if (file_exists($file))
{

    
    // força ao navegador (ou proxy) a não utilizar o cache!
    // serve para os protocolos HTTP/1.0 e HTTP/1.1
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
    header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    // dados do arquivo
    header('Content-Type: ' . $lot_file_model->draft_type);
    header('Content-Length: ' . $lot_file_model->draf_size);
    header('Content-Disposition: attachment; filename="' . basename($lot_file_model->draft_file) . '"');
    
    // imprime o arquivo
    readfile($file); 
    exit;
}
else
{
    Header('location: ?c=404');
}

    }
}
    