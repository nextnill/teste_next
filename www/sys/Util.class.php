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
}