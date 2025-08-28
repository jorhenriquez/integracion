<?php

require_once('C:\xampp\htdocs\df\clases\Funciones\definiciones.php');
require_once('C:\xampp\htdocs\df\clases\Database\connect.php');
require_once('C:\xampp\htdocs\df\clases\Funciones\funciones.php');
require_once('moduleGetClients.php');
require_once('moduleSaveClient.php');
require_once('moduleSaveSale.php');
require_once('moduleSavePDF.php');
require_once('moduleGetSale.php');
//require_once('moduleSaveTypeCreditNote.php');


$conn = connect(); /* Conecta a la base de datos */

/* ACTUALIZAR LOS CLIENTES */

$err = 0;

$stmt = get_clients($conn);

while($sol = $stmt->fetch(PDO::FETCH_ASSOC)){
    $client = New SaveClient;
    $client->crear($sol);
    $res = $client->post();
    if(!$res->success)
        $client->setError($conn,$res->message);
    else{
        $client->setStatus($conn);
        $err++;
    }
}

/* ACTUALIZAR LOS DOCUMENTOS DE VENTA */

$stmt = get_document($conn); 

$err = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

    
    $saveSale = New SaveSale($row);
    
    /* Verificamos que el cliente existe */
    if (!clientExists($row['clientFile'])){
        $saveSale->setError($conn,ERROR_NO_EXISTE_CLIENTE);
        $err++;
        continue;
    }

    /* Obtenemos el detalle del documento */

    if(!$saveSale->get_detail($conn)){
        $saveSale->setError($conn,ERROR_DETALLE_DISTINTO_DE_FACTURA);
        $err++;
        continue;
    }

    /* Crea el objeto de Chipax */

    $chipaxDetails = $saveSale;

    
    /* Se verifica que el documento no esté ingresado anteriormente */

    if (GetSale($row['CODIGO']) == 1){
        $saveSale->setStatus($conn,OK,$res->message);
        continue;
    }

    /* Se crea el objeto */
    
    $res = $saveSale->post();

    /* Se sube el documento */
    if($res->success)
        $saveSale->setStatus($conn,OK,'.');
    else{
        $saveSale->setStatus($conn,NOT_OK,$res->message);
        $err++;
    }

}

$stmt = getpdf($conn);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    GetPDFDocumentBase64($conn,'FVAELECT',$row['ANO'],$row['CODIGO']);
}

/* ACTUALIZAR LAS NOTAS DE CREDITO */



?>