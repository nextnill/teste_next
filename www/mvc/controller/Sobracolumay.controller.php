<?php

class Sobracolumay_Controller extends \Sys\Controller
{

    function list_action($params)
    {
        $type = (strtolower(trim($params[0])) == 'final' ? 'final' : 'interim');
        $data['type'] = $type;
        $this->RenderView('masterpage', array(
            'sobracolumay/list',
            'sobracolumay/reserve',
            'block/detail',
            'production_order/items/defects_marker',
            'production_order/items/photo_upload',
            'production_order/items/photo_view',
        ), $data);
    }

    function list_json($params)
    {
        $block_model = $this->LoadModel('Block', true);
        $type = (strtolower(trim($params[0])) == 'final' ? $block_model::BLOCK_TYPE_FINAL : $block_model::BLOCK_TYPE_INTERIM);
        $client_exception = isset($params[1]) ? (int)$params[1] : null;
        $list = $block_model->get_sobracolumay($type, $client_exception);
        
        $this->print_json($list);
    }

    /*
    function list_final_action($params)
    {
        $this->RenderView('masterpage', array('block_list_final/list', 'block_list_final/reserve', 'block/detail'));
    }

    function list_interim_action($params)
    {
        $this->RenderView('masterpage', array('block_list_interim/list'));
    }

    function list_final_json($params)
    {
        $block_model = $this->LoadModel('Block', true);
        $list = $block_model->get_sobracolumay($block_model::BLOCK_TYPE_FINAL);
        
        $this->print_json($list);
    }

    function list_interim_json($params)
    {
    	$block_model = $this->LoadModel('Block', true);
        $list = $block_model->get_sobracolumay($block_model::BLOCK_TYPE_INTERIM);
        
        $this->print_json($list);
    }
    */

    function download_excel($params)
    {
        $type = (strtolower(trim($params[0])) == 'final' ? 'final' : 'interim');

        $list = null;
        $block_model = $this->LoadModel('Block', true);
        if ($type == 'final') {
            $list = $block_model->get_sobracolumay($block_model::BLOCK_TYPE_FINAL);
        }
        else {
            $list = $block_model->get_sobracolumay($block_model::BLOCK_TYPE_INTERIM);
        }

        /** Include PHPExcel */
        require_once 'sys/libs/PHPExcel.php';

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $el = 1;

        $quarry_name = '';
        $quality_name = '';
        $block_count = 0;
        $block_net_vol_sum = 0;
        $block_tot_weight_sum = 0;
        
        // função interna para adicionar totalizador na pedreira
        function add_totalizador($objPHPExcel, $el, $block_count, $block_net_vol_sum, $block_tot_weight_sum) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $block_count);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $block_net_vol_sum);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $block_tot_weight_sum);

            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getFont()->setBold(true);
        }

        foreach ($list as $key => $block) {
            
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            //$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(12);

            if ($block['quarry_name'] . $block['quality_name'] != $quarry_name . $quality_name) {
                
                // se não for o primeiro registro,
                if ($key > 0) {
                    add_totalizador($objPHPExcel, $el, $block_count, $block_net_vol_sum, $block_tot_weight_sum);
                    $el++;

                    // incrementa uma linha
                    $el++;
                }

                // zero totalizadores
                $block_count = 0;
                $block_net_vol_sum = 0;
                $block_tot_weight_sum = 0;

                // imprimo a pedreira
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $block['quarry_name']);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');;

                $el++;

                // imprimo a qualidade
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $block['quality_name']);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');;

                $el++;

                $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, 'Block Number');
                //$objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Quality');
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Tot Meas');
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, '');
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, '');
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, 'Net Meas');
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, '');
                $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, '');
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, 'Vol');
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, 'Weight');
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, 'Obs.');
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$el, 'Reserved');

                // merge cells
                $objPHPExcel->getActiveSheet()->mergeCells('B'.$el.':D'.$el);
                $objPHPExcel->getActiveSheet()->mergeCells('E'.$el.':G'.$el);

                // styles
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                //$objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('K'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getFont()->setBold(true);

                $el++;
            }

            $block_count++;
            $block_net_vol_sum += $block['net_vol'];
            $block_tot_weight_sum += $block['tot_weight'];

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $block['block_number']);
            //$objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $block['quality_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $block['tot_c']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $block['tot_a']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, $block['tot_l']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, $block['net_c']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, $block['net_a']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $block['net_l']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $block['net_vol']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $block['tot_weight']);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, $block['obs']);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$el, $block['reserved_client_code']);

            // styles

            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setWrapText(true);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            //$objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //$objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
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
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $objPHPExcel->getActiveSheet()->getStyle('K'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('K'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            
            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.00');
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);


            $border_style = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':K'.$el)->applyFromArray($border_style);

            $el++;

            $quarry_name = $block['quarry_name'];
            $quality_name = $block['quality_name'];
        }

        // último registro
        // adiciono o totalizador
        add_totalizador($objPHPExcel, $el, $block_count, $block_net_vol_sum, $block_tot_weight_sum);
        $el++;

        // posiciono no inicio da tabela
        $objPHPExcel->getActiveSheet()->getStyle('A1');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle(ucfirst($type) . ' Sobracolumay');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="sobracolumay_'.$type.'.xls"');
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