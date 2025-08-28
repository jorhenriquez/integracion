<?php

function eliminar_acentos($str){
    $str2 = str_replace(array("'",'°','Á','á','É','é','Í','í','Ó','ó','Ú','ú','Ñ','ñ'), array('','','A','a','E','e','I','i','O','o','U','u','N','n'),$str);
    return $str2;
}

function print_errors($err){
    switch ($err){
        case 0:
            echo "OK<br>";
            break;
        case 1:
            echo "Hay ".$err." error<br>";
            break;
        default:
            echo "Hay ".$err." errores<br>";
            break;
    }
}