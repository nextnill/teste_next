<?php

use \Sys\Util;

class PackingList_Controller extends \Sys\Controller
{

    function save_json($params)
    {
        $lot_transport_id = $this->ReadPost('lot_transport_id');
        $shipped_from = $this->ReadPost('shipped_from');
        $client_notify_address = $this->ReadPost('client_notify_address');
        $bl = $this->ReadPost('bl');
        $dated = $this->ReadPost('packing_list_dated');
        $vessel = $this->ReadPost('vessel');
        $commercial_invoice_number = $this->ReadPost('commercial_invoice_number');
        $packing_list_ref = $this->ReadPost('packing_list_ref');

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($lot_transport_id);

        $ret = $lot_transport_model->set_packing_list(
            $shipped_from,
            $client_notify_address,
            $bl,
            $dated,
            $vessel,
            $commercial_invoice_number,
            $packing_list_ref
        );

        $this->print_json($ret);
    }

    function download($params)
    {
        $lot_transport_id = $this->ReadGet('lot_transport_id');

        // carrego o lote
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($lot_transport_id);

        //print_r($lot_transport_model); exit;

        if ($lot_transport_model->id > 0) {
            // gero html
            $title = '<h3 align="center">PACKING LIST</h3>';

            $html = '<table border="0" cellpadding="1" cellspacing="0">';
            $html .= '<thead>
                         <tr>
                          <td width="40" align="center" rowspan="2"><b>Quantity</b></td>
                          <td width="35" align="center" rowspan="2"><b>Type</b></td>
                          <td width="80" align="center" rowspan="2"><b>Block Numbers</b></td>
                          <td width="150" align="center" colspan="3"><b>Net Measures</b></td>
                          <td width="60" align="center" rowspan="2"><b>M³</b></td>
                          <td width="60" align="center" rowspan="2"><b>Weight</b></td>
                         </tr>
                         <tr>
                          <td width="50" align="center"><b>Length</b></td>
                          <td width="50" align="center"><b>Height</b></td>
                          <td width="50" align="center"><b>Width</b></td>
                         </tr>
                        </thead>';

            function refresh_qtd($html_blocks, $group_key, $qtd) {
                return str_replace('{'.$group_key.'_qtd}', $qtd, $html_blocks);
            }
            function rodape($total_m3, $total_weight, $product_name, $quality_name) {

                $words_total_m3 = Util::ConvertNumber($total_m3, 3);
                $words_total_m3 = $words_total_m3[0] . ' cubic meters ' . ($words_total_m3[1] != 'zero' ? ', ' . $words_total_m3[1] : '');

                $words_total_weight = Util::ConvertNumber($total_weight, 3);
                $words_total_weight = $words_total_weight[0] . ($words_total_weight[1] != 'zero' ? ', ' . $words_total_weight[1] : '') . ' kilogrames';

                $rodape = '<tr>
                          <td align="center" colspan="8">&nbsp;</td>
                        </tr>

                        <tr>
                          <td align="left" colspan="8">
                            ROUGH GRANITE BLOCKS
                          </td>
                        </tr>
                        <tr>
                          <td align="left" colspan="6">
                            ' . strtoupper($product_name . ' ' . $quality_name) . '
                          </td>
                          <td align="center" valign="bottom">
                            <b>' . number_format($total_m3, 3) . '</b>
                          </td>
                          <td align="center">
                            <b>' . number_format($total_weight, 3) . '</b>
                          </td>
                        </tr>

                        <tr>
                          <td align="center" colspan="8">&nbsp;</td>
                        </tr>

                        <tr>
                          <td align="left" colspan="8">
                            (' . strtoupper($words_total_m3) . ')
                          </td>
                        </tr>
                        <tr>
                          <td align="left" colspan="8">
                            (' . strtoupper($words_total_weight) . ')
                          </td>
                        </tr>

                        <tr>
                          <td align="center" colspan="8">&nbsp;</td>
                        </tr>

                        <tr>
                          <td align="center" colspan="8">&nbsp;</td>
                        </tr>';
                    return $rodape;
            }

            $blocks = '';
            $group_key = '';
            $total_m3 = 0;
            $total_weight = 0;
            $primeiro = false;
            $qtd = 0;
            $product_name = '';
            $quality_name = '';

            //listo blocos
            foreach ($lot_transport_model->items as $key => $block) {
                if ($group_key != $block['product_id'] . '|' . $block['quality_id']) {
                    $old_group_key = $group_key;
                    $group_key = $block['product_id'] . '|' . $block['quality_id'];

                    // imprime rodapé final
                    if ($key != 0) {
                        $blocks .= rodape($total_m3, $total_weight, $product_name, $quality_name);
                    }

                    $blocks = refresh_qtd($blocks, $old_group_key, $qtd);

                    $total_m3 = 0;
                    $total_weight = 0;
                    $primeiro = true;
                    $qtd = 0;
                }

                $blocks .= '<tr>
                          <td width="40" align="center">' . ($primeiro ? '{'.$group_key.'_qtd}' : '') . '</td>
                          <td width="35" align="center">' . ($primeiro ? 'Blocks' : '') . '</td>
                          <td width="80" align="center">' . $block['block_number'] . '</td>
                          <td width="50" align="center">' . $block['sale_net_c'] . '</td>
                          <td width="50" align="center">' . $block['sale_net_a'] . '</td>
                          <td width="50" align="center">' . $block['sale_net_l'] . '</td>
                          <td width="60" align="center">' . $block['sale_net_vol'] . '</td>
                          <td width="60" align="center">' . $block['tot_weight'] . '</td>
                        </tr>';

                $primeiro = false;
                $qtd++;

                $total_m3 += $block['sale_net_vol'];
                $total_weight += $block['tot_weight'];
                $product_name = $block['product_name'];
                $quality_name = $block['quality_name'];
            }

            $blocks .= rodape($total_m3, $total_weight, $product_name, $quality_name);

            $blocks = refresh_qtd($blocks, $group_key, $qtd);

            $html .= $blocks . '</table>';

            // FOB
            if (!empty($lot_transport_model->shipped_from)) {
                $html .= '<table border="0" cellpadding="1" cellspacing="0">
                            <tr><td align="left">FOB ' . strtoupper($lot_transport_model->shipped_from) . '</td></tr>
                            <tr><td>&nbsp;</td></tr>
                          </table>';
            }

            // Client
            $html .= '<table border="0" cellpadding="1" cellspacing="0">
                        <tr><td align="left" width="45">CLIENT:</td><td align="left">' . strtoupper($lot_transport_model->client_name) . '</td></tr>
                        <tr><td>&nbsp;</td><td align="left">' . nl2br(strtoupper($lot_transport_model->client_notify_address)) . '</td></tr>
                        <tr><td colspan="2">&nbsp;</td></tr>
                      </table>';

            $dated = ($lot_transport_model->packing_list_dated != '0000-00-00' ? date_create($lot_transport_model->packing_list_dated) : '');
            $dated = (!empty($dated) ? date_format($dated, 'F jS, Y') : '');

            $html .= '<table border="0" cellpadding="1" cellspacing="0">
                        <tr>
                            <td align="left" colspan="2">COMMERCIAL INVOICE: ' . $lot_transport_model->commercial_invoice_number . '</td>
                        </tr>
                        <tr>
                            <td align="left" width="150">B/L NR. ' . strtoupper($lot_transport_model->bl) . '</td>
                            <td align="left">DATED: ' . strtoupper($dated) . '</td>
                        </tr>
                        <tr>
                            <td align="left" colspan="2">VESSEL: ' . strtoupper($lot_transport_model->vessel) . '</td>
                        </tr>
                        <tr>
                            <td align="left" colspan="2">REF: ' . $lot_transport_model->packing_list_ref . '</td>
                        </tr>
                      </table>';
            
            // assinatura
            $html .= '<table border="0" cellpadding="1" cellspacing="0">
                        <tr><td align="center">&nbsp;</td></tr>
                        <tr><td align="center">&nbsp;</td></tr>
                        <tr><td align="center">___________________________________________</td></tr>
                        <tr><td align="center">MONTE SANTO MINERADORA E EXPORTADORA S.A.</td></tr>
                      </table>';


            // gero pdf

            /** Include tcpdf */
            require_once 'sys/libs/tcpdf.php';

            $pdf = new MonteSantoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            //$pdf->setPrintHeader(false);
            //$pdf->setPrintFooter(false);
            $pdf->SetFont('helvetica', 'N', 9);
            $pdf->AddPage();
            $pdf->writeHTML($title.$html, true, false, true, false, '');

            // altera estado do download no lote
            $lot_transport_model->set_down_packing_list();

            // envia arquivo gerado para o usuario
            $pdf->Output($lot_transport_model->lot_number . '_packing_list.pdf', 'I'); // D = força download
        }
    }
    
}