<?php

use \Sys\Util;

class CommercialInvoice_Controller extends \Sys\Controller
{

    function save_json($params)
    {
        $lot_transport_id = $this->ReadPost('lot_transport_id');
        $commercial_invoice_number = $this->ReadPost('commercial_invoice_number');
        $packing_list_ref = $this->ReadPost('packing_list_ref');
        $commercial_invoice_date = $this->ReadPost('commercial_invoice_date');
        $shipped_from = $this->ReadPost('shipped_from');
        $shipped_to = $this->ReadPost('shipped_to');
        $client_notify_address = $this->ReadPost('client_notify_address');
        $client_consignee = $this->ReadPost('client_consignee');
        $vessel = $this->ReadPost('vessel');
        $products = $this->ReadPost('products');

        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($lot_transport_id);


        $ret = $lot_transport_model->set_commercial_invoice(
            $shipped_from,
            $shipped_to,
            $client_notify_address,
            $client_consignee,
            $commercial_invoice_date,
            $vessel,
            $commercial_invoice_number,
            $packing_list_ref,
            $products
        );

        $this->print_json($ret);
    }

    function list_json($params)
    {
        $lot_transport_id = $this->ReadGet('lot_transport_id');

        $list = array();
        $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
        $list = $lot_transport_item_model->get_by_lot_transport_group_by_product_quality($lot_transport_id);

        $this->print_json($list);
    }

    function download($params)
    {
        $lot_transport_id = $this->ReadGet('lot_transport_id');

        // carrego o lote
        $lot_transport_model = $this->LoadModel('LotTransport', true);
        $lot_transport_model->populate($lot_transport_id);


        if ($lot_transport_model->id > 0) {
            
            $commercial_invoice_date = ($lot_transport_model->commercial_invoice_date != '0000-00-00' ? date_create($lot_transport_model->commercial_invoice_date) : '');
            $commercial_invoice_date = (!empty($commercial_invoice_date) ? date_format($commercial_invoice_date, 'F jS, Y') : '');

            // gero html
            $html = '
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td>
                                CNPJ: 62.644.505/0003-08
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Inscrição Estadual: 324.060.493.0134
                            </td>
                            <td>
                                Date: ' . $commercial_invoice_date . '
                            </td>
                        </tr>
                        <tr>
                            <td>
                                D/V: ' . $lot_transport_model->packing_list_ref . '
                            </td>
                            <td>
                                Commercial Invoice: ' . $lot_transport_model->commercial_invoice_number . '
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                    </table>
            ';
            // Client
            $html .= '
                    <table border="1" cellpadding="3" cellspacing="0" width="100%">
                        <tr>
                            <td>
                                <table border="0" cellpadding="3" cellspacing="0" width="100%">
                                    <tr>
                                        <td>
                                            <table border="0" cellpadding="1" cellspacing="0">
                                                <tr>
                                                    <td align="left" width="45">Client:</td>
                                                    <td align="left" width="100%">' . strtoupper($lot_transport_model->client_name) . '</td>
                                                </tr>
                                                <tr><td>&nbsp;</td><td align="left">' . nl2br(strtoupper($lot_transport_model->client_notify_address)) . '</td></tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                
                                <table border="0" cellpadding="3" cellspacing="0" width="100%">
                                    <tr>
                                        <td>
                                            <table border="0" cellpadding="1" cellspacing="0" width="100%">
                                                <tr>
                                                    <td align="left" colspan="2">Consigned to: ' . nl2br(strtoupper($lot_transport_model->client_consignee)) . ' </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" colspan="2">Notify to: ' . strtoupper($lot_transport_model->client_name) . '</td>
                                                </tr>
                                                <tr>
                                                    <td align="left" width="50%">Shipped from: ' . strtoupper($lot_transport_model->shipped_from) . '</td>
                                                    <td align="left" width="50%">Shipped to: ' . strtoupper($lot_transport_model->shipped_to) . '</td>
                                                </tr>
                                                <tr>
                                                    <td align="left" width="50%">Per S.S. "' . strtoupper($lot_transport_model->vessel) . '"</td>
                                                    <td align="left" width="50%">Total Weight: ' . $lot_transport_model->tot_weight . '</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                            </td>
                        </tr>
                    </table>
            ';

            // produtos cabeçalho
            $html .= '<br><br>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="center">
                                Products of Brazil
                            </td>
                        </tr>
                    </table>
                    <br><br>
            ';

            $html .= '<table border="0" cellpadding="1" cellspacing="0" width="100%">';
            $html .=    '
                            <tr>
                                <td width="10%" align="center">
                                    BLOCK
                                </td>
                                <td width="42%" align="left">
                                    ROUGH GRANITE BLOCKS
                                </td>
                                <td width="15%" align="center">
                                    &nbsp;
                                </td>
                                <td width="15%" align="center">
                                    &nbsp;
                                </td>
                                <td width="17%" align="center">
                                    &nbsp;
                                </td>
                            </tr>
                        ';

            /*
            function refresh_qtd($html_blocks, $group_key, $qtd) {
                return str_replace('{'.$group_key.'_qtd}', $qtd, $html_blocks);
            }
            */

            function linha($qtd, $sale_net_vol, $total_weight, $product_name, $quality_name, $value) {
                
                $words_total_m3 = Util::ConvertNumber($sale_net_vol, 3);
                $words_total_m3 = $words_total_m3[0] . ' cubic meters ' . ($words_total_m3[1] != 'zero' ? ', ' . $words_total_m3[1] : '');

                $words_total_weight = Util::ConvertNumber($total_weight, 3);
                $words_total_weight = $words_total_weight[0] . ($words_total_weight[1] != 'zero' ? ', ' . $words_total_weight[1] : '') . ' kilogrames';
            
                $linha = '
                        <tr>
                          <td align="center" colspan="5">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="center">
                                ' . $qtd . '
                            </td>
                            <td align="left">
                                ' . strtoupper($product_name . ' ' . $quality_name) . '
                            </td>
                            <td align="right">
                                ' . number_format($sale_net_vol, 3) . ' M³
                            </td>
                            <td align="right">
                                ' . number_format($value, 2) . ' USD
                            </td>
                            <td align="right">
                                ' . number_format($value * $sale_net_vol, 2) . ' USD
                            </td>
                        </tr>
                        ';
                    return $linha;
            }


            // carrego os produtos
            $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
            $produtos = $lot_transport_item_model->get_by_lot_transport_group_by_product_quality($lot_transport_id);

            $group_key = '';
            $total_m3 = 0;
            $total_weight = 0;
            $total_value = 0;
            $primeiro = false;
            $qtd = 0;
            $product_name = '';
            $quality_name = '';

            //listo blocos
            foreach ($produtos as $key => $produto) {
                $html .= linha($produto['blocks'], $produto['sale_net_vol'], $produto['tot_weight'], $produto['product_name'], $produto['quality_name'], $produto['value']);

                $total_m3 += $produto['sale_net_vol'];
                $total_weight += $produto['tot_weight'];
                $total_value += $produto['sale_net_vol'] * $produto['value'];
            }

            //$blocks .= rodape($total_m3, $total_weight, $product_name, $quality_name);
            //$blocks = refresh_qtd($blocks, $group_key, $qtd);

            $html .= '
                    <tr>
                      <td align="center" colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="center">
                            &nbsp;
                        </td>
                        <td align="left">
                            TOTAL FOB ' . strtoupper($lot_transport_model->shipped_from) . '
                        </td>
                        <td align="right">
                            &nbsp;
                        </td>
                        <td align="right">
                            &nbsp;
                        </td>
                        <td align="right">
                            ' . number_format($total_value, 2) . ' USD
                        </td>
                    </tr>
                    <tr>
                      <td align="center" colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                      <td align="center" colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                      <td align="center" colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="center">
                            &nbsp;
                        </td>
                        <td align="left" colspan="3">
                            TOTAL ....................................................................................................................................
                        </td>
                        <td align="right">
                            ' . number_format($total_value, 2) . ' USD
                        </td>
                    </tr>
                    ';

            $html .= '</table>';
            
            // assinatura
            $html .= '<table border="0" cellpadding="1" cellspacing="0">
                        <tr><td align="center">&nbsp;</td></tr>
                        <tr><td align="center">&nbsp;</td></tr>
                        <tr><td align="center">&nbsp;</td></tr>
                        <tr><td align="center">___________________________________________</td></tr>
                        <tr><td align="center">MONTE SANTO MINERADORA E EXPORTADORA S.A.</td></tr>
                      </table>';


            //print_r($html);
            //exit;
            // gero pdf

            /** Include tcpdf */
            require_once 'sys/libs/tcpdf.php';

            $pdf = new MonteSantoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            //$pdf->setPrintHeader(false);
            //$pdf->setPrintFooter(false);
            $pdf->SetFont('helvetica', 'N', 9);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');

            // altera estado do download no lote
            $lot_transport_model->set_down_commercial_invoice();

            // envia arquivo gerado para o usuario
            $pdf->Output($lot_transport_model->lot_number . '_packing_list.pdf', 'I'); // D = força download
        }
    }
    
}