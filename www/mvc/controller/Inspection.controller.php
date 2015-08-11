<?php

use \Sys\Util;

class Inspection_Controller extends \Sys\Controller
{

    function list_client_action($params)
    {
        $this->RenderView('masterpage', array('inspection/client_list'));
    }

    function list_notification_action($params)
    {
        $this->RenderView('masterpage', array('inspection/inspection_notification'));
    }

    function list_block_action($params)
    {
        $client_id = (int)$params[0];
        $data['client_id'] = $client_id;
        
        $client_model = $this->LoadModel('Client', true);
        $client_model->populate($client_id);
        $data['client'] = $client_model;

        $this->RenderView(
            'masterpage',
            array(
                'inspection/block_list',
                'inspection/add_block',
                'inspection/refuse',
                'block/detail',
                'production_order/items/defects_marker',
                'production_order/items/photo_upload',
                'production_order/items/photo_view',
                'inspection/confirm'
            ),
            $data
        );
    }

    function list_block_json($params)
    {
        $client_id = (int)$params[0];
        $block_model = $this->LoadModel('Block', true);
        $list = $block_model->get_client_reservations($client_id);
        
        $this->print_json($list);
    }

    function save_json($params)
    {
        $client_id = $this->ReadPost('client_id');
        $blocks = json_decode($this->ReadPost('blocks'), true);
        $ret = array();

        // totaliza blocos
        $total_refused = 0;
        $total_accepted = 0;

        $blocks_refused = array();
        $blocks_accepted = array();

        foreach ($blocks as $key => $block) {
            if (isset($block['refused']) && ($block['refused'] == 'true')) {
                $blocks_refused[] = $block;
                $total_refused++;
            }
            else {
                $blocks_accepted[] = $block;
                $total_accepted++;
            }

        }


        // se algum bloco foi aceito, crio a invoice
        if ($total_accepted > 0) {
            // carrego os models
            $invoice_model = $this->LoadModel('Invoice', true);

            // crio o cabeçalho (invoice)
            $invoice_model->client_id = $client_id;
            $ret['invoice_id'] = $invoice_model->save();

            if ($invoice_model->id > 0) {
                // adiciono os itens (invoice_item)
                foreach ($blocks_accepted as $key => $block) {
                    $invoice_item_model = $this->LoadModel('InvoiceItem', true);
                    $invoice_item_model->invoice_id = $invoice_model->id;
                    $invoice_item_model->block_id = $block['id'];
                    $invoice_item_model->client_id = $client_id;
                    $invoice_item_model->block_number = $block['block_number'];
                    $invoice_item_model->tot_c = $block['tot_c'];
                    $invoice_item_model->tot_a = $block['tot_a'];
                    $invoice_item_model->tot_l = $block['tot_l'];
                    $invoice_item_model->tot_vol = $block['tot_vol'];
                    $invoice_item_model->net_c = $block['net_c'];
                    $invoice_item_model->net_a = $block['net_a'];
                    $invoice_item_model->net_l = $block['net_l'];
                    $invoice_item_model->net_vol = $block['net_vol'];
                    $invoice_item_model->sale_net_c = $block['sale_net_c'];
                    $invoice_item_model->sale_net_a = $block['sale_net_a'];
                    $invoice_item_model->sale_net_l = $block['sale_net_l'];
                    $invoice_item_model->sale_net_vol = $block['sale_net_vol'];
                    $invoice_item_model->tot_weight = $block['tot_weight'];
                    $invoice_item_model->obs = $block['obs'];
                    $invoice_item_model->poblo_status_id = $block['poblo_status_id'];
                    $invoice_item_model->client_block_number = $block['client_block_number'];
                    $invoice_item_model->block_number_interim = $block['block_number_interim'];

                    $ret['invoice_item_id'][] = $invoice_item_model->save();
                }
            }
        }

        // se algum bloco foi recusado, registro no histórico do bloco e removo a reserva
        if ($blocks_refused > 0) {
            foreach ($blocks_refused as $key => $block) {
                $block_refused_model = $this->LoadModel('BlockRefused', true);

                $block_refused_model->block_id = $block['id'];
                $block_refused_model->client_id = $client_id;
                $block_refused_model->reason = $block['refused_reason'];

                $ret['refused_id'][] = $block_refused_model->save();
            }
        }
        $this->pdf_notification($invoice_model->id);
        $this->print_json($ret);

    }

    function save_notification_json($params){

        $inspection_notification = $this->ReadPost('email_notification');

        $parameters_model = $this->LoadModel('Parameters', true); 

        $ret = $parameters_model->set('inspection_notification',  $inspection_notification);

        $this->print_json($ret);
    }

    function load_email_notification(){

        $parameters_model = $this->LoadModel('Parameters', true);
        $ret = $parameters_model->get('inspection_notification');

        $this->print_json($ret);
    }


    function pdf_notification($invoice_id){

        $id = $invoice_id;
       
        $invoice_model = $this->LoadModel('Invoice', true);
        $invoice_model->populate($id);

        $invoice_item_model = $this->LoadModel('InvoiceItem', true);
        $list = $invoice_item_model->get_by_invoice($id);

        $client_model = $this->LoadModel('Client', true);
        $client_model->populate($invoice_model->client_id);

        $parameters_model = $this->LoadModel('Parameters', true);
        $destinatario = $parameters_model->get('inspection_notification');


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
        $cabecalho = '';

    
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
       

        require_once 'sys/libs/tcpdf.php';

        $pdf = new MonteSantoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        
        $pdf->SetFont('helvetica', 'N', 9);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        $arquivo = CERTIFICATE.'/inspection_certificate.pdf'; 

        @unlink($arquivo);

        if (!file_exists(CERTIFICATE)) {
            mkdir(CERTIFICATE, 0770, true);
        }

        // envia arquivo gerado para o usuario
       $pdf->Output($arquivo, 'F'); // D = força download
       
       if($destinatario != ''){

          $titulo = "Inspection Certificate";
          
         Util::send_email($destinatario, $arquivo, $titulo, $email);
        } 

        @unlink($arquivo);
    }
}