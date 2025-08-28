<?php

require_once('C:\xampp\htdocs\df\clases\Funciones\definiciones.php');
require_once('C:\xampp\htdocs\df\clases\Database\connect.php');
require_once('C:\xampp\htdocs\df\clases\Funciones\funciones.php');
require_once('moduleGetClients.php');
require_once('moduleSaveClient.php');
require_once('moduleSaveSale.php');
require_once('moduleSaveTypeCreditNote.php');
require_once('moduleSavePDF.php');


$nc = new SaveTypeCreditNote();

print_r(json_encode($nc));

?>