<?php
namespace Sys;

use PDO;

class Util
{    

    static function DateNow() {
        $dt_now = new \DateTime('now');
        return $dt_now->format('Y-m-d H:i:s');
    }
    
    static function FilesArray($arquivos)
    {
        $ret = array();
        
        if (isset($arquivos) && is_array($arquivos))
        {
            $count = sizeof($arquivos['name']);

            for ($i = 0; $i < $count; $i++)
            {
                $ret[] = array( 'name'      => $arquivos['name'][$i],
                                'type'      => $arquivos['type'][$i],
                                'tmp_name'  => $arquivos['tmp_name'][$i],
                                'error'     => $arquivos['error'][$i],
                                'size'      => $arquivos['size'][$i]
                );
            }

        }

        return $ret;
    }

    static function FileShortName($arquivo)
    {
        $max_prefixo = 20;
        $nome = substr($arquivo, 0, strripos($arquivo, '.'));
        $extensao = explode('.', $arquivo);
        $extensao = end($extensao);
        
        return substr($nome, 0, $max_prefixo) . (strlen($nome) > $max_prefixo ? '...' : '') . '.' . $extensao;
    }

    static function JoinFiles($arquivos, $separador=null)
    {
        foreach ($arquivos as $key => $file_name) {
            if (file_exists($file_name)) {
                ob_start();
                require($file_name);
                $buffer .= ob_get_clean();
                if (!is_null($separador)) {
                    $buffer .= $separador;
                }
            }
        }
    }
    static function ConvertNumber($number, $decimals)
    {
        $decimal = number_format($number, $decimals, '.', ''); // put it in decimal format, rounded 

        $explode = explode('.', $decimal);

        $return[] = self::ConvertInteger($explode[0]);
        $return[] = self::ConvertInteger($explode[1]);

        return $return;
    }

    static function ConvertInteger($number)  
    {  
        if (($number < 0) || ($number > 999999999))  
        {  
            return "$number";  
        }  

        $Gn = floor($number / 1000000);  /* Millions (giga) */  
        $number -= $Gn * 1000000;  
        $kn = floor($number / 1000);     /* Thousands (kilo) */  
        $number -= $kn * 1000;  
        $Hn = floor($number / 100);      /* Hundreds (hecto) */  
        $number -= $Hn * 100;  
        $Dn = floor($number / 10);       /* Tens (deca) */  
        $n = $number % 10;               /* Ones */  

        $res = "";  

        if ($Gn)  
        {  
            $res .= self::ConvertInteger($Gn) . " Million";  
        }  

        if ($kn)  
        {  
            $res .= (empty($res) ? "" : " ") .  
                self::ConvertInteger($kn) . " Thousand";  
        }  

        if ($Hn)  
        {  
            $res .= (empty($res) ? "" : " ") .  
                self::ConvertInteger($Hn) . " Hundred";  
        }  

        $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",  
            "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",  
            "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen",  
            "Nineteen");  
        $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty",  
            "Seventy", "Eigthy", "Ninety");  

        if ($Dn || $n)  
        {  
            if (!empty($res))  
            {  
    //            $res .= " and ";  
               $res .= " ";  
            }  

            if ($Dn < 2)  
            {  
                $res .= $ones[$Dn * 10 + $n];  
            }  
            else  
            {  
                $res .= $tens[$Dn];  

                if ($n)  
                {  
                    $res .= "-" . $ones[$n];  
                }  
            }  
        }  

        if (empty($res))  
        {  
            $res = "zero";  
        }  

        return $res;  
    }

    static function Colors($index=null)
    {
        $arr_cores = array(

            '#FFCCCC' => array('Vermelho 1', '#000'),
            '#FF6666' => array('Vermelho 2', '#000'),
            '#FF0000' => array('Vermelho 3', '#fff'),
            '#CC0000' => array('Vermelho 4', '#fff'),
            '#990000' => array('Vermelho 5', '#fff'),
            '#660000' => array('Vermelho 6', '#fff'),
            '#330000' => array('Vermelho 7', '#fff'),

            '#FFCC99' => array('Laranja 1', '#000'),
            '#FFCC33' => array('Laranja 2', '#000'),
            '#FF9900' => array('Laranja 3', '#fff'),
            '#FF6600' => array('Laranja 4', '#fff'),
            '#CC6600' => array('Laranja 5', '#fff'),
            '#993300' => array('Laranja 6', '#fff'),
            '#663300' => array('Laranja 7', '#fff'),

            '#FFFFCC' => array('Amarelo 1', '#000'),
            '#FFFF99' => array('Amarelo 2', '#000'),
            '#FFFF00' => array('Amarelo 3', '#000'),
            '#FFCC00' => array('Amarelo 4', '#000'),
            '#999900' => array('Amarelo 5', '#fff'),
            '#666600' => array('Amarelo 6', '#fff'),
            '#333300' => array('Amarelo 7', '#fff'),

            '#99FF99' => array('Verde 1', '#000'),
            '#66FF99' => array('Verde 2', '#000'),
            '#33ff33' => array('Verde 3', '#000'),
            '#00CC00' => array('Verde 4', '#fff'),
            '#009900' => array('Verde 5', '#fff'),
            '#006600' => array('Verde 6', '#fff'),
            '#003300' => array('Verde 7', '#fff'),

            '#CCFFFF' => array('Azul 1', '#000'),
            '#66FFFF' => array('Azul 2', '#000'),
            '#33CCFF' => array('Azul 3', '#000'),
            '#3366FF' => array('Azul 4', '#fff'),
            '#3333FF' => array('Azul 5', '#fff'),
            '#000099' => array('Azul 6', '#fff'),
            '#000066' => array('Azul 7', '#fff'),

            '#FFCCFF' => array('Roxo 1', '#000'),
            '#FF99FF' => array('Roxo 2', '#000'),
            '#CC66CC' => array('Roxo 3', '#000'),
            '#CC33CC' => array('Roxo 4', '#fff'),
            '#993366' => array('Roxo 5', '#fff'),
            '#663366' => array('Roxo 6', '#fff'),
            '#330033' => array('Roxo 7', '#fff'),

            '#FFFFFF' => array('Branco', '#000'),
            '#CCCCCC' => array('Cinza 1', '#000'),
            '#C0C0C0' => array('Cinza 2', '#000'),
            '#999999' => array('Cinza 3', '#fff'),
            '#666666' => array('Cinza 4', '#fff'),
            '#333333' => array('Cinza 5', '#fff'),
            '#000000' => array('Preto', '#fff'),
        );

        return is_null($index) ? $arr_cores : $arr_cores[$index];
    }
}