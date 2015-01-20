<?php

require_once 'sys/libs/tcpdf/tcpdf.php';

// Extend the TCPDF class to create custom Header and Footer
class MonteSantoPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $image_file =  'assets/img/logo_relatorio.png';
        $this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 10);
        // Title
        $header = '
            <table border="0">
                <tr>
                    <td width="100%" align="center"><h1>MONTE SANTO Mineradora e Exportadora S.A.</h1></td>
                </tr>
                <tr>
                    <td align="center"><small>Fazenda Bom Retiro, S/N Zona Rural - Dores de Guanhaes - Minas Gerais Brasil</small></td>
                </tr>
                <tr>
                    <td align="center"><small>CEP: 35.894-000</small></td>
                </tr>
            </table>
        ';
        $this->writeHTML($header, true, false, true, false, '');
        //$this->Cell(0, 15, 'MONTE SANTO Mineradora e Exportadora S.A.', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        //$this->Cell(0, 15, 'MONTE SANTO Mineradora e Exportadora S.A.', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}