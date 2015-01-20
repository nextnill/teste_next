<?php

use \Sys\DB;

class BlockList_Controller extends \Sys\Controller
{

    function list_client_action($params)
    {
        $this->RenderView('masterpage', array('blocklist/client_list'));
    }

    function download($params){
 	
	$client_id = $this->ReadGet('client_id');
    $client_model = $this->LoadModel('Client', true);
    $client_model->populate($client_id);
	$block_model = $this->LoadModel('Block', true);
	$list = $block_model->get_client_reservations($client_id);


    $html = '<table border="0" cellpadding="12" cellspacing="0">
                        <tr>
                            <td align="center"><font size="12"><b>BLOCK LIST OF '. $client_model->name .'</b></font></td>
                        </tr>
                        
            </table>';
      //print_r($list);
    
    $chave_anterior = '';
    $bloco_count = 0;
    $peso_count = 0;
    $volume_count = 0;
    $total_bloco = 0;
    $total_peso = 0;
    $total_volume = 0;
    $html2 = '';
	
    function imprimir_cabecalho($quarry_name, $quality_name){
        $cabecalho = '   
                    <table cellpadding="5">
                        <tr>
                            <td><font size="10"><b>'.strtoupper($quarry_name.' - '.$quality_name).'</b></font></td>
                        </tr>
                    </table>
                    <table border="1" cellpadding="2" cellspacing="0" width="100%">
                        <tr>    
                            <td width="110"><font size="10"><b>BLOCK Nº.:</b></font></td>
                            <td colspan="3" width="150" align="center"><font size="10"><b>TOT MEAS.:</b></font></td>
                            <td colspan="3" width="150" align="center"><font size="10"><b>NET MEAS.:</b></font></td>
                            <td width="60"><font size="10" align="center"><b>VOL.:</b></font></td>
                            <td width="60"><font size="10" align="center"><b>WEIGH:</b></font></td> 
                        </tr>';

                        return $cabecalho;
    }


    function imprimir_linha($bloco){

        $linhas = '<tr>
                            <td width="110">'. $bloco['block_number'] .'</td>
                            <td width="50" align="right">'. $bloco['tot_c'] .'</td>
                            <td width="50" align="right">'. $bloco['tot_a'] .'</td>
                            <td width="50" align="right">'. $bloco['tot_l'] .'</td>
                            <td width="50" align="right">'. $bloco['net_c'] .'</td>
                            <td width="50" align="right">'. $bloco['net_a'] .'</td>
                            <td width="50" align="right">'. $bloco['net_l'] .'</td>
                            <td width="60" align="right">'. $bloco['tot_vol'] .'</td>
                            <td width="60" align="right">'. $bloco['tot_weight'] .'</td>
                        </tr>';

                        return $linhas;

    }

    function totalizador($bloco_count, $volume_count, $peso_count){

    $total =       '<tr>
                            <td width="110" align="center">'. $bloco_count .'</td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="60" align="right">'. number_format($volume_count, 3) .'</td>
                            <td width="60" align="right">'. number_format($peso_count, 3) .'</td>
                        </tr>
                        </table>
                        <p></p>
                        ';

                        return $total;
                        
    }


    function totalizador_geral($total_bloco, $total_volume, $total_peso){

    $total_geral =       '<table border="1" cellpadding="2" cellspacing="0">
                        <tr>
                            <td width="110" align="center">'. $total_bloco .'</td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="50" align="right"></td>
                            <td width="60" align="right">'. number_format($total_volume, 3) .'</td>
                            <td width="60" align="right">'. number_format($total_peso, 3) .'</td>
                        </tr>
                        </table>';

                        return $total_geral;
                        
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
        $volume_count += $bloco['tot_vol'];
        $peso_count += $bloco['tot_weight'];

        $total_bloco++;
        $total_volume += $bloco['tot_vol'];
        $total_peso += $bloco['tot_weight'];

    }
    //imprimir rodape
     
        //imprimir rodapé da tabela anterior
        $html .= totalizador($bloco_count, $volume_count, $peso_count);
        $html .=totalizador_geral($total_bloco, $total_volume, $total_peso);
        

       

   
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

            // envia arquivo gerado para o usuario
           $pdf->Output('block_list.pdf', 'D'); // D = força download
 }
    
}



