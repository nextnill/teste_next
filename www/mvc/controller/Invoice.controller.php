<?php

use \Sys\DB;
use \Sys\Util;

class Invoice_Controller extends \Sys\Controller
{

    function list_action($params)
    {   

        $ano = null;
        $mes = null;
        $client_id = null;

        if(isset($_SESSION[SPRE.'ic_ano_filtro'])){  
            $ano =  $_SESSION[SPRE.'ic_ano_filtro'];
        }
        
        if(isset($_SESSION[SPRE.'ic_mes_filtro'])){ 
            $mes = $_SESSION[SPRE.'ic_mes_filtro'];
        }
        
        if(isset($_SESSION[SPRE.'ic_client_id'])){
            $client_id = $_SESSION[SPRE.'ic_client_id'];
        }
        
        $parametro["ano"] = $ano;
        $parametro["mes"] = $mes;
        $parametro["client_id"] = $client_id;

        $this->RenderView('masterpage', array('invoice/list'), $parametro);
    }

    function list_json($params)
    {
        $client_id = -1;
        if (isset($params[0])) {
            $client_id = (int)$params[0];
           
        }
      //  print_r($client_id);
        $ano = $this->ReadGet('ano');
        $mes = $this->ReadGet('mes');


        $_SESSION[SPRE.'ic_ano_filtro'] = $ano;  
        $_SESSION[SPRE.'ic_mes_filtro'] = $mes;
        $_SESSION[SPRE.'ic_client_id'] = $client_id;
        

        $client_id = ($client_id > 0 ? $client_id : null);
        $ano = ($ano > 0 ? $ano : null);
        $mes = ($mes > 0 ? $mes : null);

    	$invoice_model = $this->LoadModel('Invoice', true);
    	$list = $invoice_model->get_list(false, $client_id, $ano, $mes);
    	
        $this->print_json($list);
        
    }

    function detail_action($params)
    {   
        $id = (int)$params[0];

        $data['invoice_id'] = $id;
        $this->RenderView('masterpage', array(
            'invoice/detail',
            'block/detail',
            'production_order/items/defects_marker',
            'production_order/items/photo_upload',
            'production_order/items/photo_view',
        ), $data);
    }

    function detail_json($params)
    {
        $id = (int)$params[0];
        $invoice_model = $this->LoadModel('Invoice', true);
        $invoice_model->populate($id);

        $this->print_json($invoice_model);
    }

    function blocks_json($params)
    {
        $id = (int)$params[0];

        $invoice_item_model = $this->LoadModel('InvoiceItem', true);
        $list = $invoice_item_model->get_by_invoice($id);
        
        $this->print_json($list);
    }

    function list_clients_json($params)
    {
        $invoice_model = $this->LoadModel('Invoice', true);
        $list = $invoice_model->get_clients();

        $this->print_json($list);
    }

    function delete_json($params)
    {
        $id = (int)$this->ReadPost('id');
        $invoice_model = $this->LoadModel('Invoice', true);
        
        if ($id > 0)
        {
            $invoice_model->populate($id);
            $invoice_model->delete();
        }

        $this->print_json($invoice_model);
    }


    function download($params){
  
    $id = (int)$this->ReadGet('invoice_id');
    $send_email = (int)$this->ReadGet('send_email');
   // print_r($send_email);exit();
    $invoice_model = $this->LoadModel('Invoice', true);
    $invoice_model->populate($id);
    
    $invoice_item_model = $this->LoadModel('InvoiceItem', true);
    $list = $invoice_item_model->get_by_invoice($id);
    
    $client_model = $this->LoadModel('Client', true);
    $client_model->populate($invoice_model->client_id);
    
    $destinatario = $invoice_model->list_email_notification($invoice_model->client_id, true);
    //$parameters_model = $this->LoadModel('Parameters', true);
    //$destinatario = $parameters_model->get('inspection_notification');
    

    $html = '<table border="0" cellpadding="12" cellspacing="0">
                        <tr>
                            <td align="center"><b><span class="titulo">INSPECTION CERTIFICATE OF '. $client_model->name .'</span></b></td>
                        </tr>
                        
            </table>';
      
    
    $chave_anterior = '';
    $bloco_count = 0;
    $peso_count = 0;
    $volume_count = 0;
    $total_bloco = 0;
    $total_peso = 0;
    $total_volume = 0;
    $html2 = '';
    
    function imprimir_cabecalho($quarry_name, $quality_name){
        $cabecalho = '<table cellpadding="5">
                    <tr>
                        <td><b><span class="conteudo">'.strtoupper($quarry_name.' - '.$quality_name).'</span></b></td>
                    </tr>
                </table>
                <table border="1" cellpadding="2" cellspacing="0" width="100%">
                    <tr>    
                        <td width="110"><b><span class="conteudo">BLOCK Nº.:</span></b></td>
                        <td colspan="3" width="150" align="center"><b><span class="conteudo">TOT MEAS.:</span></b></td>
                        <td colspan="3" width="150" align="center"><b><span class="conteudo">SALE NET MEAS.:</span></b></td>
                        <td width="60"><b><span class="conteudo">SALE VOL.:</span></b></td>
                        <td width="60"><b><span class="conteudo">WEIGH:</span></b></td> 
                    </tr>';


                        return $cabecalho;
    }


    function imprimir_linha($bloco){

        $linhas = '<tr>
                            <td width="110"><span class="conteudo">'. $bloco['block_number'] .'</span></td>
                            <td width="50" align="right"><span class="conteudo">'. $bloco['tot_c'] .'</span></td>
                            <td width="50" align="right"><span class="conteudo">'. $bloco['tot_a'] .'</span></td>
                            <td width="50" align="right"><span class="conteudo">'. $bloco['tot_l'] .'</span></td>
                            <td width="50" align="right"><span class="conteudo">'. $bloco['sale_net_c'] .'</span></td>
                            <td width="50" align="right"><span class="conteudo">'. $bloco['sale_net_a'] .'</span></td>
                            <td width="50" align="right"><span class="conteudo">'. $bloco['sale_net_l'] .'</span></td>
                            <td width="60" align="right"><span class="conteudo">'. $bloco['sale_net_vol'] .'</span></td>
                            <td width="60" align="right"><span class="conteudo">'. $bloco['tot_weight'] .'</span></td>
                        </tr>';


                        
                        return $linhas;

    }

    function totalizador($bloco_count, $volume_count, $peso_count){

    $total = '<tr>
                        <td width="110" align="center"><span class="conteudo">'. $bloco_count .'</span></td>
                        <td width="50" align="right"><span class="conteudo"></span></td>
                        <td width="50" align="right"><span class="conteudo"></span></td>
                        <td width="50" align="right"><span class="conteudo"></span></td>
                        <td width="50" align="right"><span class="conteudo"></span></td>
                        <td width="50" align="right"><span class="conteudo"></span></td>
                        <td width="50" align="right"><span class="conteudo"></span></td>
                        <td width="60" align="right"><span class="conteudo">'. number_format($volume_count, 3) .'</span></td>
                        <td width="60" align="right"><span class="conteudo">'. number_format($peso_count, 3) .'</span></td>
                    </tr>
                    </table>
                    <p></p>
                    ';

                        return $total;
                        
    }


    function totalizador_geral($total_bloco, $total_volume, $total_peso){

    $total_geral =  '<table border="1" cellpadding="2" cellspacing="0" width="100%">
                                <tr>
                                    <td width="110" align="center"><span class="conteudo">'. $total_bloco .'</span></td>
                                    <td width="50" align="right"><span class="conteudo"></span></td>
                                    <td width="50" align="right"><span class="conteudo"></span></td>
                                    <td width="50" align="right"><span class="conteudo"></span></td>
                                    <td width="50" align="right"><span class="conteudo"></span></td>
                                    <td width="50" align="right"><span class="conteudo"></span></td>
                                    <td width="50" align="right"><span class="conteudo"></span></td>
                                    <td width="60" align="right"><span class="conteudo">'. number_format($total_volume, 3) .'</span></td>
                                    <td width="60" align="right"><span class="conteudo">'. number_format($total_peso, 3) .'</span></td>
                                </tr>
                            </table>';

                        return $total_geral;                        
    }

    function assinatura_observacoes(){

        $assinatura =   '<br><br><br><br><br><br>
                            <table align="center" cellpadding="10">
                                <tr>
                                    <td><span class="conteudo">ESPECIAL INSTRUCTIONS:</span>  _____________________________________________</td>
                                </tr>
                                <tr>
                                    <td>_____________________________________________________________________</td>
                                </tr>
                            </table>
                            <br><br><br><br>
                            <br><br><br><br>
                            <table>
                                <tr>
                                    <td width="250" align="center">________________________________</td>
                                    <td width="250" align="center">________________________________</td>
                                </tr>
                                <tr>
                                    <td width="250" align="center"><span class="conteudo">SELLER SIGNATURE</span></td>
                                    <td width="250" align="center"><span class="conteudo">BUYER SIGNATURE</span></td>
                                </tr>
                            </table>';

        return $assinatura;    
    }
    
    foreach($list as $bloco){
        
        $nova_chave = $bloco['quarry_name'] . $bloco['quality_name'];

        if($nova_chave != $chave_anterior){
            if($chave_anterior != ''){

              
                //imprimir rodapé da tabela anterior
                $html .= totalizador($bloco_count, $volume_count, $peso_count);
                

            }
            //imprimir cabeçalho da tabela
            $html .= imprimir_cabecalho($bloco['quarry_name'], $bloco['quality_name']);
            $bloco_count = 0;
            $volume_count = 0;
            $peso_count = 0;     
           
        }

        // imprimir linhas da tabela
        $html .= imprimir_linha($bloco);
        $chave_anterior = $nova_chave;

        $bloco_count++;
        $volume_count += $bloco['sale_net_vol'];
        $peso_count += $bloco['tot_weight'];

        $total_bloco++;
        $total_volume += $bloco['sale_net_vol'];
        $total_peso += $bloco['tot_weight'];

    }
    //imprimir rodape
     
        //imprimir rodapé da tabela anterior
        $html .= totalizador($bloco_count, $volume_count, $peso_count);
        $html .= totalizador_geral($total_bloco, $total_volume, $total_peso);
        $email = $html;
        $html .= assinatura_observacoes();
        

       

   
  //print($html2);
  //print($tot_bloco);  
require_once 'sys/libs/tcpdf.php';

            $pdf = new MonteSantoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            //$pdf->setPrintHeader(false);
            //$pdf->setPrintFooter(false);
            $pdf->SetFont('helvetica', 'N', 9);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
               @unlink($arquivo);
            // envia arquivo gerado para o usuario
          
            if($send_email == 1){
                
                $arquivo = CERTIFICATE.'/inspection_certificate.pdf'; 

                if (!file_exists(CERTIFICATE)) {
                    mkdir(CERTIFICATE, 0770, true);
                }
      
                // envia arquivo gerado para o usuario
               $pdf->Output($arquivo, 'F'); // D = força download
               
               if($destinatario != ''){
        
                  $titulo = "Inspection Certificate";
                  
                 $email_enviado = Util::send_email($destinatario, $arquivo, $titulo, $email);
                 $this->print_json(array("email_enviado" => $email_enviado));
                } 
        
                @unlink($arquivo);  
            }
           else{
            
             $pdf->Output('inspecton_certificate.pdf', 'D'); // D = força download     
        }
           
    }



    function download_excel($params)
    {
        
        $list = null;
        $id = (int)$this->ReadGet('invoice_id');
        $invoice_model = $this->LoadModel('Invoice', true);
        $invoice_model->populate($id);
        
        $invoice_item_model = $this->LoadModel('InvoiceItem', true);
        $list = $invoice_item_model->get_by_invoice($id);
        
        $client_model = $this->LoadModel('Client', true);
        $client_model->populate($invoice_model->client_id);

        $cliente_name = $client_model->name;

        /** Include PHPExcel */
        require_once 'sys/libs/PHPExcel.php';

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $el = 8;

        $quarry_name = '';
        $quality_name = '';
        $block_count = 0;
        $block_count_total = 0;
        $block_net_sale_vol_sum = 0;
        $block_net_sale_vol_sum_geral = 0;
        $block_weight = 0;
        $block_weight_total = 0;

        function cabecalho($objPHPExcel, $cliente_name){

            $today = date("d/m/Y");

            $objDrawing = new PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('logo');
            $objDrawing->setDescription('logo');
            $objDrawing->setPath('assets/img/logo_relatorio_excel.png');
            $objDrawing->setCoordinates('A1');
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

             $objPHPExcel->getActiveSheet()->setCellValue('B1', 'MONTE SANTO Mineradora e Exportadora S/A');
             $objPHPExcel->getActiveSheet()->setCellValue('A3', 'INSPECTION CERTIFICATE');
             $objPHPExcel->getActiveSheet()->setCellValue('A5', 'IMPORTER: ');
             $objPHPExcel->getActiveSheet()->setCellValue('B5', $cliente_name);
             $objPHPExcel->getActiveSheet()->setCellValue('A6', 'DATE: ');
             $objPHPExcel->getActiveSheet()->setCellValue('B6', $today);


             $objPHPExcel->getActiveSheet()->mergeCells('B1:J1');
             $objPHPExcel->getActiveSheet()->mergeCells('A3:J3');
             $objPHPExcel->getActiveSheet()->mergeCells('B5:F5');
             $objPHPExcel->getActiveSheet()->mergeCells('B6:F6');

             $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
             $objPHPExcel->getActiveSheet()->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
             $objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
             $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
             $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(14);
             $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setSize(12);
             $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
             $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);

        }   

        function rodape($objPHPExcel, $el){

            $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, 'SPECIAL INSTRUCTIONS: ');
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, '________________________________________________________________________________________________________');
            $objPHPExcel->getActiveSheet()->setCellValue('G'.($el+1), '________________________________________________________________________________________________________');

            $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->mergeCells('D'.$el.':F'.$el);
            $objPHPExcel->getActiveSheet()->mergeCells('G'.$el.':J'.$el);
            $objPHPExcel->getActiveSheet()->mergeCells('G'.($el+1).':J'.($el+1));
        }

        function assinatura($objPHPExcel, $el){

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, '________________________________________________________________________________________________________');
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, '________________________________________________________________________________________________________');
            $objPHPExcel->getActiveSheet()->setCellValue('A'.($el+1), 'SELLER SIGNATURE');
            $objPHPExcel->getActiveSheet()->setCellValue('I'.($el+1), 'BUYER SIGNATURE');

            $objPHPExcel->getActiveSheet()->getStyle('A'.($el+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('I'.($el+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$el.':C'.$el);
            $objPHPExcel->getActiveSheet()->mergeCells('I'.$el.':J'.$el);
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($el+1).':C'.($el+1));
            $objPHPExcel->getActiveSheet()->mergeCells('I'.($el+1).':J'.($el+1));

        }

        // função interna para adicionar totalizador na pedreira
        function add_totalizador($objPHPExcel, $el, $block_net_sale_vol_sum, $block_weight) {
            // imprimo os totalizadores
            
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $block_net_sale_vol_sum);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $block_weight);

            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);
        }

        function add_totalizador_geral($objPHPExcel, $el, $block_count_total, $block_net_sale_vol_sum_geral, $block_weight_total) {
            // imprimo os totalizadores
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, 'Tot:');
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $block_count_total);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, 'Vol/WT:');
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $block_net_sale_vol_sum_geral);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $block_weight_total);

            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        
            $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);
        }

        cabecalho($objPHPExcel, $cliente_name);

        foreach ($list as $key => $block) {

            
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            //$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(33);
    
            if ($block['quarry_name'] . $block['quality_name'] != $quarry_name . $quality_name) {
                
                // se não for o primeiro registro,
                if ($key > 0) {
                    add_totalizador($objPHPExcel, $el, $block_net_sale_vol_sum, $block_weight);
                    $el++;
                    // incrementa uma linha
                    $el++;
                }

                // zero totalizadores
                $block_count = 0;
                $block_net_sale_vol_sum = 0;
                $block_weight = 0;
                // imprimo a pedreira
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $block['quarry_name']);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('F2F3F4');

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
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, 'Tot MEAS');
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, 'Sale Net MEAS');
                $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, 'Vol');
                $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, 'Weight');
                $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, 'Obs.');

                $objPHPExcel->getActiveSheet()->mergeCells('B'.$el.':D'.$el);
                $objPHPExcel->getActiveSheet()->mergeCells('E'.$el.':G'.$el);



                // merge cells

                // styles
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                //$objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $objPHPExcel->getActiveSheet()->getStyle('A'.$el.':J'.$el)->getFont()->setBold(true);

                $el++;
            }

            
            

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$el, $block['block_number']);
            //$objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $block['quality_name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$el, $block['tot_c']);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$el, $block['tot_a']);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$el, $block['tot_l']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$el, $block['sale_net_c']);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$el, $block['sale_net_a']);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$el, $block['sale_net_l']);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$el, $block['sale_net_vol']);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$el, $block['tot_weight']);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$el, $block['obs']);

            $block_count++;
            $block_net_sale_vol_sum += $block['sale_net_vol'];
            $block_net_sale_vol_sum_geral += $block['sale_net_vol'];
            $block_weight += $block['tot_weight'];
            $block_weight_total += $block['tot_weight'];
            $block_count_total++;
          
            // styles

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
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$el)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            
            $objPHPExcel->getActiveSheet()->getStyle('B'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('C'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('D'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('E'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('F'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('G'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('H'.$el)->getNumberFormat()->setFormatCode('#,###0.000');
            $objPHPExcel->getActiveSheet()->getStyle('I'.$el)->getNumberFormat()->setFormatCode('#,###0.000');

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

            $quarry_name = $block['quarry_name'];
            $quality_name = $block['quality_name'];
        }

        // último registro
        // adiciono o totalizador
        add_totalizador($objPHPExcel, $el, $block_net_sale_vol_sum, $block_weight);
        add_totalizador_geral($objPHPExcel, $el+3, $block_count_total, $block_net_sale_vol_sum_geral, $block_weight_total);
        rodape($objPHPExcel, $el+6);
        assinatura($objPHPExcel, $el+10);
        $el++;  

        // posiciono no inicio da tabela
        $objPHPExcel->getActiveSheet()->getStyle('A2');

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle(ucfirst($type) . ' Inpection Certificate');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="inspection_certificate_'.$type.'.xls"');
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