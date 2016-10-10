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
                    'poblo/poblo_edit',
                    'poblo/excel'
                    ));
        }
        else {
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
                    'inspection_name'=>$lot_transport['inspection_name'],                    
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
        $wagon_number = $this->ReadPost('wagon_number');

        
        if($wagon_number <> ''){
            $block_model = $this->LoadModel('Block', true);
            $block_model->update_wagon_number($block_id, $wagon_number);
        }       

        $this->print_json(array('block_id' => $block_id));
    }
    
    function download_excel($params) {

        $quarry_name = '';
        $quality_name = '';
        $count_blocks_final = 0;
        $count_quality_blocks_final = 0;
        $sum_volume_final = 0;
        $sum_weight_final = 0;
        $sum_price_final = 0;

        $count_blocks = 0;
        $count_quality_blocks = 0;
        $sum_volume = 0;
        $sum_weight = 0;
        $sum_price = 0;

        $count_blocks_quarry = 0;
        $count_quality_blocks_quarry = 0;
        $sum_volume_quarry = 0;
        $sum_weight_quarry = 0;                    
        $sum_price_quarry = 0;

        $inspection_name = '';
        $lot_number = '';
        
        // tratamento das cores
        $client_color = array();

        $colors_sobra_background = array(
            array('background' => 'FFE082', 'texto' => '000000'),
            array('background' => 'EF9A9A', 'texto' => '000000'),
            array('background' => '81D4FA', 'texto' => '000000'),
            array('background' => 'FFAB91', 'texto' => '000000'),
            array('background' => 'E6EE9C', 'texto' => '000000'),
            array('background' => 'BCAAA4', 'texto' => '000000'),
            array('background' => '795548', 'texto' => 'FFFFFF'),
            array('background' => 'FFB300', 'texto' => '000000'),
            array('background' => 'E53935', 'texto' => 'FFFFFF'),
            array('background' => '039BE5', 'texto' => 'FFFFFF'),
            array('background' => 'F4511E', 'texto' => 'FFFFFF'),
            array('background' => 'C0CA33', 'texto' => '000000'),
            array('background' => '6D4C41', 'texto' => 'FFFFFF'),
            array('background' => 'FFD54F', 'texto' => '000000'),
            array('background' => 'E57373', 'texto' => '000000'),
            array('background' => '4FC3F7', 'texto' => '000000'),
            array('background' => 'FF8A65', 'texto' => '000000'),
            array('background' => 'DCE775', 'texto' => '000000'),
            array('background' => 'A1887F', 'texto' => 'FFFFFF'),
            array('background' => 'FFCA28', 'texto' => '000000'),
            array('background' => 'EF5350', 'texto' => 'FFFFFF'),
            array('background' => '29B6F6', 'texto' => '000000'),
            array('background' => 'FF7043', 'texto' => '000000'),
            array('background' => 'D4E157', 'texto' => '000000'),
            array('background' => '8D6E63', 'texto' => 'FFFFFF'),
            array('background' => 'FFECB3', 'texto' => '000000'),
            array('background' => 'FFCDD2', 'texto' => '000000'),
            array('background' => 'B3E5FC', 'texto' => '000000'),
            array('background' => 'FFCCBC', 'texto' => '000000'),
            array('background' => 'F0F4C3', 'texto' => '000000'),
            array('background' => 'D7CCC8', 'texto' => '000000'),
            array('background' => 'FFC107', 'texto' => '000000'),
            array('background' => 'F44336', 'texto' => 'FFFFFF'),
            array('background' => '03A9F4', 'texto' => '000000'),
            array('background' => 'FF5722', 'texto' => 'FFFFFF'),
            array('background' => 'CDDC39', 'texto' => '000000'),
            array('background' => 'FFA000', 'texto' => '000000'),
            array('background' => 'D32F2F', 'texto' => 'FFFFFF'),
            array('background' => '0288D1', 'texto' => 'FFFFFF'),
            array('background' => 'E64A19', 'texto' => 'FFFFFF'),
            array('background' => 'AFB42B', 'texto' => '000000'),
            array('background' => '5D4037', 'texto' => 'FFFFFF'),
            array('background' => 'FF8F00', 'texto' => '000000'),
            array('background' => 'C62828', 'texto' => 'FFFFFF'),
            array('background' => '0277BD', 'texto' => 'FFFFFF'),
            array('background' => 'D84315', 'texto' => 'FFFFFF'),
            array('background' => '9E9D24', 'texto' => '000000'),
            array('background' => '4E342E', 'texto' => 'FFFFFF'),
            array('background' => 'FF6F00', 'texto' => '000000'),
            array('background' => 'B71C1C', 'texto' => 'FFFFFF'),
            array('background' => '01579B', 'texto' => 'FFFFFF'),
            array('background' => 'BF360C', 'texto' => 'FFFFFF'),
            array('background' => '827717', 'texto' => 'FFFFFF'),
            array('background' => '3E2723', 'texto' => 'FFFFFF')
        );

        /** Include PHPExcel */
        require_once 'sys/libs/PHPExcel.php';

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $el = 1;


        // SOBRACOLUMAY
        // função interna para adicionar totalizador na pedreira
        function add_totalizador_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $count_blocks_final);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $count_quality_blocks_final);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $sum_volume_final);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $sum_weight_final);            

            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0F8FF');;
        }

        // função interna para adicionar totalizador na pedreira
        function add_totalizador_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $count_blocks);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $count_quality_blocks);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $sum_volume);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $sum_weight);            

            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFF0');;
        }        

        // função interna para adicionar totalizador na pedreira
        function add_totalizador_inspection_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $count_blocks);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $count_quality_blocks);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, $sum_volume);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $sum_weight);            

            $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
            $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFF0');;
        }                

        // função interna para adicionar totalizador na pedreira
        function add_totalizador_inspection_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $count_blocks_final);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $count_quality_blocks_final);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, $sum_volume_final);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $sum_weight_final);            

            $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
            $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0F8FF');;
        }                        

        // função interna para adicionar totalizador na pedreira
        function add_totalizador_inspection_quarry($objPHPExcel, $el, $count_blocks_quarry, $count_quality_blocks_quarry, $sum_volume_quarry, $sum_weight_quarry) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $count_blocks_quarry);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $count_quality_blocks_quarry);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, $sum_volume_quarry);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $sum_weight_quarry);            

            $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
            $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0FFF0');;
        }    

        // função interna para adicionar totalizador na pedreira
        function add_totalizador_lot_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final, $sum_price_final) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $sum_price_final);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, $count_blocks_final);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, $count_quality_blocks_final);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $sum_volume_final);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, $sum_weight_final);            

            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0F8FF');;
        }                            

        // função interna para adicionar totalizador na pedreira
        function add_totalizador_lot_quarry($objPHPExcel, $el, $count_blocks_quarry, $count_quality_blocks_quarry, $sum_volume_quarry, $sum_weight_quarry, $sum_price_quarry) {
            // imprimo os totalizadores
        
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $sum_price_quarry);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, $count_blocks_quarry);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, $count_quality_blocks_quarry);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $sum_volume_quarry);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, $sum_weight_quarry);            

            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F0FFF0');;
        }    

        // função interna para adicionar totalizador na pedreira
        function add_totalizador_lot_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight, $sum_price) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $sum_price);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, $count_blocks);            
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, $count_quality_blocks);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $sum_volume);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, $sum_weight);            

            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFF0');;
        }   

        //load block color according to the selected client
        function color_sobra(&$client_color, $colors_sobra_background, $objPHPExcel, $el, $block) {

            if ($block['reserved_client_id']) {
                
                // verifico se já existe o item.client_color no client_color
                $existe = false;
                $cor = null;

                foreach ($client_color as $key => $value) {
                    if(intval($value['client_id']) == intval($block['reserved_client_id'])){                        
                        $existe = true;
                        $cor = $value;
                        break;
                    }
                }    

                // se não existe, adiciono nova cor do cliente em client_color
                if (!$existe) {
                    $new_client_color = array('client_id' => intval($block['reserved_client_id']),
                                              'background' => $colors_sobra_background[count($client_color) + 1]['background'],
                                              'texto' => $colors_sobra_background[count($client_color) + 1]['texto']);

                    array_push($client_color, $new_client_color);
                    $cor = $new_client_color;
                }

                // pinto a cor da linha com a cor atribuida para o cliente
                $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($cor['background']);

                $styleArray = array(
                    'font'  => array(
                        'color' => array('rgb' => $cor['texto'])             
                    ));                

                $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->applyFromArray($styleArray);                
            }
        }

        $client_id = -1;
        if (isset($params[0])) {
            $client_id = (int)$params[0];
        }

        $client_id = ($client_id > 0 ? $client_id : null);

         // listar os blocos
        $lot_transport_model = $this->LoadModel('LotTransport', true);

        // sobracolumay
        $list = $lot_transport_model->get_sobracolumay($client_id);                     

        if (!empty($list)){
            foreach ($list as $key => $block) {
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);            
                
                if($key == 0){
                    $quarry_name = $block['quarry_name'];
                    $quality_name = $block['quality_name'];
                    // imprimo a pedreira
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,'Final Sobracolumay - ' . $block['quarry_name']);

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
          
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':J'.$el);

                    $el++;


                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, 'Production'); 
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Block Number');             
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, 'Quality');                         
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'Net Meas');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, 'Vol');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, 'Weight');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, 'Reserved');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, 'Obs');

                    // styles
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);                

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('D'.$el.':F'.$el);
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $el++;                

                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'C');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, 'A');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, 'L');                                                         

                    $lAnt = $el - 1;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$lAnt.':A'.$el);
                    $objPHPExcel->getActiveSheet()->mergeCells('B'.$lAnt.':B'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('C'.$lAnt.':C'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('G'.$lAnt.':G'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('H'.$lAnt.':H'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('I'.$lAnt.':I'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('J'.$lAnt.':J'.$el);                    

                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);                                        
                    $el++;                
                }

                if ($block['quarry_name'] != $quarry_name) {

                    add_totalizador_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight);
                    $el++;                                        
                    add_totalizador_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final);
                    $el++;

                    // incrementa uma linha
                    $el++;
      

                    $quarry_name = $block['quarry_name'];
                    $quality_name = $block['quality_name'];
                    $count_blocks_final = 0;
                    $count_quality_blocks_final = 0;
                    $sum_volume_final = 0;
                    $sum_weight_final = 0;      

                    $count_blocks = 0;
                    $count_quality_blocks = 0;
                    $sum_volume = 0;
                    $sum_weight = 0;

                    // imprimo a pedreira
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,'Final Sobracolumay - ' . $block['quarry_name']);

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':J'.$el);

                    $el++;


                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, 'Production'); 
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Block Number');             
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, 'Quality');                         
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'Net Meas');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, 'Vol');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, 'Weight');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, 'Reserved');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, 'Obs');

                        // styles
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);                

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('D'.$el.':F'.$el);
                    
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $el++;

                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'C');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, 'A');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, 'L');                                                         

                    $lAnt = $el - 1;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$lAnt.':A'.$el);
                    $objPHPExcel->getActiveSheet()->mergeCells('B'.$lAnt.':B'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('C'.$lAnt.':C'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('G'.$lAnt.':G'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('H'.$lAnt.':H'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('I'.$lAnt.':I'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('J'.$lAnt.':J'.$el);                    

                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);                                        
                    $el++;                                    
                }

                if($block['quality_name'] != $quality_name){
                    add_totalizador_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight);
                    $el++;
                    
                    $quality_name = $block['quality_name'];
                    $count_blocks = 0;
                    $count_quality_blocks = 0;
                    $sum_volume = 0;
                    $sum_weight = 0;

                }

                $count_blocks_final++;
                $count_quality_blocks_final++;
                $sum_volume_final += $block['net_vol'];
                $sum_weight_final += $block['tot_weight'];

                $count_blocks++;
                $count_quality_blocks++;
                $sum_volume += $block['net_vol'];
                $sum_weight += $block['tot_weight'];

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $block['date_production']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $block['block_number']);

                // Verificamos a cor do cliente    
                color_sobra($client_color, $colors_sobra_background, $objPHPExcel, $el, $block);

                $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $block['quality_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, $block['net_c']);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, $block['net_a']);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, $block['net_l']);     
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $block['net_vol']);     
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $block['tot_weight']);     
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $block['reserved_client_code']);                 
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, $block['obs_poblo']);                             

                // styles

                $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setWrapText(true);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                
                
                $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
                $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
                

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);


                $border_style = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->applyFromArray($border_style);

                $el++;            
            }

            // último registro
            // adiciono o totalizador
            add_totalizador_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight);
            $el++;                                                
            add_totalizador_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final);
            $el++;        

        }


        // INSPECTIONS
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
        $list = $inspection_certificate_return;        

        $inspection_name = '';
        $quarry_id = '';
        $quality_id = '';
        $count_blocks_final = 0;
        $count_quality_blocks_final = 0;
        $sum_volume_final = 0;
        $sum_weight_final = 0;

        $count_blocks = 0;
        $count_quality_blocks = 0;
        $sum_volume = 0;
        $sum_weight = 0;

        $count_blocks_quarry = 0;
        $count_quality_blocks_quarry = 0;
        $sum_volume_quarry = 0;
        $sum_weight_quarry = 0;    

        if (!empty($list)){
            $el++;


            foreach ($list as $key => $block) {
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);         

                if($key == 0){
                    $inspection_name = $block['inspection_name'];

                    // imprimo a pedreira
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$block['inspection_name']);

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':H'.$el);

                    $el++;

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, 'Block Number'); 
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Quality');             
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, 'Sale Meas');                         
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, 'Vol');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, 'Weight');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, 'Obs');                                     
                    
                    // styles
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('C'.$el.':E'.$el);
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $el++;                                    

                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, 'C');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'A');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, 'L');                                                         

                    $lAnt = $el - 1;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$lAnt.':A'.$el);
                    $objPHPExcel->getActiveSheet()->mergeCells('B'.$lAnt.':B'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('F'.$lAnt.':F'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('G'.$lAnt.':G'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('H'.$lAnt.':H'.$el);                    

                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);                                        
                    $el++;                                                        
                } 

                if($inspection_name != $block['inspection_name']){
                    add_totalizador_inspection_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight);
                    $el++;                    
                    add_totalizador_inspection_quarry($objPHPExcel, $el, $count_blocks_quarry, $count_quality_blocks_quarry, $sum_volume_quarry, $sum_weight_quarry);
                    $el++;
                    add_totalizador_inspection_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final);
                    $el++;
                    // incrementa uma linha
                    $el++;
                    
                    $inspection_name = $block['inspection_name'];
                    $count_blocks_final = 0;
                    $count_quality_blocks_final = 0;
                    $sum_volume_final = 0;
                    $sum_weight_final = 0;

                    $count_blocks = 0;
                    $count_quality_blocks = 0;
                    $sum_volume = 0;
                    $sum_weight = 0;

                    $count_blocks_quarry = 0;
                    $count_quality_blocks_quarry = 0;
                    $sum_volume_quarry = 0;
                    $sum_weight_quarry = 0;                        
                
                    // imprimo a pedreira
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$block['inspection_name']);

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':H'.$el);

                    $el++;


                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, 'Block Number'); 
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Quality');             
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, 'Sale Meas');                         
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, 'Vol');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, 'Weight');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, 'Obs');                                     

                    // styles
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('C'.$el.':E'.$el);
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $el++;       

                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, 'C');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'A');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, 'L');                                                         

                    $lAnt = $el - 1;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$lAnt.':A'.$el);
                    $objPHPExcel->getActiveSheet()->mergeCells('B'.$lAnt.':B'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('F'.$lAnt.':F'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('G'.$lAnt.':G'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('H'.$lAnt.':H'.$el);                    

                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFont()->setBold(true);                                        
                    $el++;                                                        
                }

                // Percorremos os blocks 
                foreach ($block['blocks'] as $itemKey => $item) {
                    if($itemKey == 0){       
                        $quarry_id = $item['quarry_id'];
                        $quality_id = $item['quality_id'];                     
                    }

                    if($quality_id != $item['quality_id'] or $quarry_id != $item['quarry_id']){
                        add_totalizador_inspection_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight);
                        $el++;
                        
                        $quality_id = $item['quality_id'];                     
                        $count_blocks = 0;
                        $count_quality_blocks = 0;
                        $sum_volume = 0;
                        $sum_weight = 0;
                    }    

                    if($quarry_id != $item['quarry_id']){
                        add_totalizador_inspection_quarry($objPHPExcel, $el, $count_blocks_quarry, $count_quality_blocks_quarry, $sum_volume_quarry, $sum_weight_quarry);
                        $el++;
                        
                        $quarry_id = $item['quarry_id'];
                        $count_blocks_quarry = 0;
                        $count_quality_blocks_quarry = 0;
                        $sum_volume_quarry = 0;
                        $sum_weight_quarry = 0;

                    }                        

                    $count_blocks_final++;
                    $count_quality_blocks_final++;
                    $sum_volume_final += $item['invoice_sale_net_vol'];
                    $sum_weight_final += $item['tot_weight'];

                    $count_blocks++;
                    $count_quality_blocks++;
                    $sum_volume += $item['invoice_sale_net_vol'];
                    $sum_weight += $item['tot_weight'];                                                    

                    $count_blocks_quarry++;
                    $count_quality_blocks_quarry++;
                    $sum_volume_quarry += $item['invoice_sale_net_vol'];
                    $sum_weight_quarry += $item['tot_weight'];                                                                            

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $item['block_number']);

                    // pinto a cor da linha com a cor atribuida para o status
                    if ($item['cor_poblo_status']){
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB(substr($item['cor_poblo_status'], 1));                    

                        if($item['cor_poblo_status_texto'] == '#fff'){
                            $styleArray = array(
                                'font'  => array(
                                    'color' => array('rgb' => 'FFFFFF')             
                            ));
                        }    
                        else {
                             $styleArray = array(
                                'font'  => array(
                                    'color' => array('rgb' => '000000')             
                            ));                                                                        
                        }

                        $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->applyFromArray($styleArray);                        
                    }



                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $item['quality_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $item['net_c']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, $item['net_a']);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, $item['net_l']);     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, $item['invoice_sale_net_vol']);     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $item['tot_weight']);     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $item['obs_poblo']);                             

                    // styles

                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setWrapText(true);

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
                    
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);


                    $border_style = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':H'.$el)->applyFromArray($border_style);

                    $el++;            
                }                 
            }
            add_totalizador_inspection_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight);
            $el++;
            add_totalizador_inspection_quarry($objPHPExcel, $el, $count_blocks_quarry, $count_quality_blocks_quarry, $sum_volume_quarry, $sum_weight_quarry);
            $el++;
            add_totalizador_inspection_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final);
            $el++;
        }


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
                    'inspection_name'=>$lot_transport['inspection_name'],                    
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

        $list = $lot_transport_return;

        $lot_number = '';
        $quarry_id = '';
        $quality_id = '';
        $count_blocks_final = 0;
        $count_quality_blocks_final = 0;
        $sum_volume_final = 0;
        $sum_weight_final = 0;
        $sum_price_final = 0;

        $count_blocks = 0;
        $count_quality_blocks = 0;
        $sum_volume = 0;
        $sum_weight = 0;
        $sum_price = 0;

        $count_blocks_quarry = 0;
        $count_quality_blocks_quarry = 0;
        $sum_volume_quarry = 0;
        $sum_weight_quarry = 0;            
        $sum_price_quarry = 0;        

        if (!empty($list)){
            $el++;

            foreach ($list as $key => $block) {

                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);                         
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);

                if($key == 0){
                   $lot_number =  $block['lot_number'];


                    $objRichText = new PHPExcel_RichText();
                    $objBold = $objRichText->createTextRun($block['lot_number'] . ' - ');

                    // Fazemos o tratamento do status do lot
                    if ($block['lot_transport_status'] == '0'){
                        $objStatus = $objRichText->createTextRun(' Draft');
                        $objStatus->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLACK ) );                        
                    } 
                    else if ($block['lot_transport_status'] == '1'){
                        $objStatus = $objRichText->createTextRun(' Released');
                        $objStatus->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLUE ) );                        
                    } 
                    else if ($block['lot_transport_status'] == '2'){
                        $objStatus = $objRichText->createTextRun(' Travel Started');
                        $objStatus->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );                                               
                    } 
                    else if ($block['lot_transport_status'] == '3'){
                        $objStatus = $objRichText->createTextRun(' Delivered');
                        $objStatus->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKGREEN ) );                        
                    }                     

                    $objStatus->getFont()->setBold(true);
                    $objBold->getFont()->setBold(true);

                    // imprimo o cabeçalho de lot
                    
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$objRichText);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':L'.$el);
                    $el++;                    

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$block['client_name']);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':L'.$el);
                    $el++;                                        

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$block['inspection_name']);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':L'.$el);                    
                    $el++;                    

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$block['vessel']);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':L'.$el);                    
                    $el++; 

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, 'NF'); 
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Date');             
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, 'Price');                         
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'Block Number');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, 'Quality');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, 'Sale Meas');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, 'Vol');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, 'Weight');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$el, 'Wagon Number');                                                         
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$el, 'Obs');

                    // styles
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('F'.$el.':H'.$el);
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $el++;          

                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, 'C');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, 'A');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, 'L');                                                         

                    $lAnt = $el - 1;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$lAnt.':A'.$el);
                    $objPHPExcel->getActiveSheet()->mergeCells('B'.$lAnt.':B'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('C'.$lAnt.':C'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('D'.$lAnt.':D'.$el);                                        
                    $objPHPExcel->getActiveSheet()->mergeCells('E'.$lAnt.':E'.$el);                                                            
                    $objPHPExcel->getActiveSheet()->mergeCells('I'.$lAnt.':I'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('J'.$lAnt.':J'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('K'.$lAnt.':K'.$el);                                        
                    $objPHPExcel->getActiveSheet()->mergeCells('L'.$lAnt.':L'.$el);                                                            

                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);                                        
                    $el++;                                    

                }

                if($lot_number != $block['lot_number']){
                    add_totalizador_lot_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight, $sum_price);
                    $el++;                    
                    add_totalizador_lot_quarry($objPHPExcel, $el, $count_blocks_quarry, $count_quality_blocks_quarry, $sum_volume_quarry, $sum_weight_quarry, $sum_price_quarry);
                    $el++;                                
                    add_totalizador_lot_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final, $sum_price_final);
                    $el++;

                    // incrementa uma linha
                    $el++;

                    $lot_number = $block['lot_number'];
                    $count_blocks_final = 0;
                    $count_quality_blocks_final = 0;
                    $sum_volume_final = 0;
                    $sum_weight_final = 0;
                    $sum_price_final = 0;

                    $count_blocks = 0;
                    $count_quality_blocks = 0;
                    $sum_volume = 0;
                    $sum_weight = 0;
                    $sum_price = 0;

                    $count_blocks_quarry = 0;
                    $count_quality_blocks_quarry = 0;
                    $sum_volume_quarry = 0;
                    $sum_weight_quarry = 0;                      
                    $sum_price_quarry = 0;

                    $objRichText = new PHPExcel_RichText();
                    $objBold = $objRichText->createTextRun($block['lot_number'] . ' - ');

                    // Fazemos o tratamento do status do lot
                    if ($block['lot_transport_status'] == '0'){
                        $objStatus = $objRichText->createTextRun(' Draft');
                        $objStatus->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLACK ) );                        
                    } 
                    else if ($block['lot_transport_status'] == '1'){
                        $objStatus = $objRichText->createTextRun(' Released');
                        $objStatus->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_BLUE ) );                        
                    } 
                    else if ($block['lot_transport_status'] == '2'){
                        $objStatus = $objRichText->createTextRun(' Travel Started');
                        $objStatus->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );                                               
                    } 
                    else if ($block['lot_transport_status'] == '3'){
                        $objStatus = $objRichText->createTextRun(' Delivered');
                        $objStatus->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::
COLOR_DARKGREEN ) );                        
                    }                     

                    $objStatus->getFont()->setBold(true);
                    $objBold->getFont()->setBold(true);

                    // imprimo o cabeçalho de lot
                    
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$objRichText);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':L'.$el);
                    $el++;                    

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$block['client_name']);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':L'.$el);
                    $el++;                                        

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$block['inspection_name']);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':L'.$el);                    
                    $el++;                    

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el,$block['vessel']);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':L'.$el);                    
                    $el++; 

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, 'NF'); 
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Date');             
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, 'Price');                         
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'Block Number');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, 'Quality');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, 'Sale Meas');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, '');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, 'Vol');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, 'Weight');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$el, 'Wagon Number');                                                         
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$el, 'Obs');

                    // styles
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('F'.$el.':H'.$el);
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $el++;         

                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, 'C');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, 'A');                                     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, 'L');                                                         

                    $lAnt = $el - 1;

                    // merge cells
                    $objPHPExcel->getActiveSheet()->mergeCells('A'.$lAnt.':A'.$el);
                    $objPHPExcel->getActiveSheet()->mergeCells('B'.$lAnt.':B'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('C'.$lAnt.':C'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('D'.$lAnt.':D'.$el);                                        
                    $objPHPExcel->getActiveSheet()->mergeCells('E'.$lAnt.':E'.$el);                                                            
                    $objPHPExcel->getActiveSheet()->mergeCells('I'.$lAnt.':I'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('J'.$lAnt.':J'.$el);                    
                    $objPHPExcel->getActiveSheet()->mergeCells('K'.$lAnt.':K'.$el);                                        
                    $objPHPExcel->getActiveSheet()->mergeCells('L'.$lAnt.':L'.$el);                                                            

                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);            
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFont()->setBold(true);                                        
                    $el++;                                                                                                                           
                }  

                // Percorremos os blocks 
                foreach ($block['blocks'] as $itemKey => $item) {

                    if($itemKey == 0){       
                        $quarry_id = $item['quarry_id'];
                        $quality_id = $item['quality_id'];                     
                    }

                    if($quality_id != $item['quality_id'] or $quarry_id != $item['quarry_id']){
                        add_totalizador_lot_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight, $sum_price);
                        $el++;
                        
                        $quality_id = $item['quality_id'];                     
                        $count_blocks = 0;
                        $count_quality_blocks = 0;
                        $sum_volume = 0;
                        $sum_weight = 0;
                        $sum_price = 0;
                    }                        

                    if($quarry_id != $item['quarry_id']){
                        add_totalizador_lot_quarry($objPHPExcel, $el, $count_blocks_quarry, $count_quality_blocks_quarry, $sum_volume_quarry, $sum_weight_quarry, $sum_price_quary);
                        $el++;
                        
                        $quarry_id = $item['quarry_id'];
                        $count_blocks_quarry = 0;
                        $count_quality_blocks_quarry = 0;
                        $sum_volume_quarry = 0;
                        $sum_weight_quarry = 0;
                        $sum_price_quarry = 0;

                    }                        

                    $count_blocks_final++;
                    $count_quality_blocks_final++;
                    $sum_volume_final += $item['invoice_sale_net_vol'];
                    $sum_weight_final += $item['tot_weight'];
                    $sum_price_final += $item['invoice_item_price'];

                    $count_blocks++;
                    $count_quality_blocks++;
                    $sum_volume += $item['invoice_sale_net_vol'];
                    $sum_weight += $item['tot_weight'];                                                    
                    $sum_price += $item['invoice_item_price'];

                    $count_blocks_quarry++;
                    $count_quality_blocks_quarry++;
                    $sum_volume_quarry += $item['invoice_sale_net_vol'];
                    $sum_weight_quarry += $item['tot_weight'];                                                                            
                    $sum_price_quarry += $item['invoice_item_price'];                    

                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $item['invoice_item_nf']);

                    // Quebramos a data
                    $strData  = $item['invoice_date_record'];
                    $parts = explode(" ", $strData);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $parts[0]);

                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $item['invoice_item_price']);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, $item['block_number']);

                    // pinto a cor da linha com a cor atribuida para o status
                    if ($item['cor_poblo_status']){
                        $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB(substr($item['cor_poblo_status'], 1));                    

                        if($item['cor_poblo_status_texto'] == '#fff'){
                            $styleArray = array(
                                'font'  => array(
                                    'color' => array('rgb' => 'FFFFFF')             
                            ));
                        }    
                        else {
                             $styleArray = array(
                                'font'  => array(
                                    'color' => array('rgb' => '000000')             
                            ));                                                                        
                        }

                        $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->applyFromArray($styleArray);                                                
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, $item['quality_name']);
                    $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, $item['invoice_sale_net_c']);     
                    $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $item['invoice_sale_net_a']);     
                    $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $item['invoice_sale_net_l']);     
                    $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $item['invoice_sale_net_vol']);                 
                    $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, $item['tot_weight']);                             
                    $objPHPExcel->getActiveSheet()->setCellValue('K'.$el, $item['block_wagon_number']);                             
                    $objPHPExcel->getActiveSheet()->setCellValue('L'.$el, $item['obs_poblo']);                             

                    // styles

                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getAlignment()->setWrapText(true);

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);                    
                    $objPHPExcel->getActiveSheet()->getStyle('K'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle('K'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getStyle('L'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    
                    
                    $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
                    $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getNumberFormat()->setFormatCode('#,###0.000');                    
                    

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);


                    $border_style = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':L'.$el)->applyFromArray($border_style);

                    $el++;                                
                }    
            }

            add_totalizador_lot_quality($objPHPExcel, $el, $count_blocks, $count_quality_blocks, $sum_volume, $sum_weight, $sum_price);
            $el++;
            add_totalizador_lot_quarry($objPHPExcel, $el, $count_blocks_quarry, $count_quality_blocks_quarry, $sum_volume_quarry, $sum_weight_quarry, $sum_price_quarry);
            $el++;            
            add_totalizador_lot_final($objPHPExcel, $el, $count_blocks_final, $count_quality_blocks_final, $sum_volume_final, $sum_weight_final, $sum_price_final);
            $el++;            
        }


        // posiciono no inicio da tabela
        $objPHPExcel->getActiveSheet()->getStyle('A1');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Poblo');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="poblo.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;               
    }
}