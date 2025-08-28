<?php

require_once('C:\xampp\htdocs\df\clases\Funciones\definiciones.php');
require_once('C:\xampp\htdocs\df\clases\Database\connect.php');
require_once('C:\xampp\htdocs\df\clases\Funciones\funciones.php');
require_once('moduleGetProviders.php');
require_once('moduleSaveProvider.php');
require_once('moduleInsertPurchaseOrder.php');


$conn = connect(); /* Conecta a la base de datos */

/* ACTUALIZAR LOS CLIENTES */
echo "Cargando proveedores....";
$stmt = get_providers($conn); // Por ahora solo hay TRANSPORTISTAS
$err = 0;
while($sol = $stmt->fetch(PDO::FETCH_ASSOC)){
    $provider = New SaveProvider($sol);
    $res = $provider->post();
    if(!$res->success)
        $provider->setError($conn,$res->message);
    else{
        $provider->setStatus($conn);
        $err++;
    }
}

print_errors($err);

/* CARGAR ORDENES DE COMPRA */
echo "Cargando ordenes de compra....";

$stmt = get_purchase_orders($conn);
$err = 0;

$purchaseOrders = array();
$i = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

    $purchaseOrder = New InsertPurchaseOrder;
    $purchaseOrder->create($row);

    if (!providerExists($row['rut'])){
        $err++;
        continue;
    }

    /* Obtenemos el detalle de la Orden de Compra */

    if(!$purchaseOrder->get_detail($conn)){
        $err++;
        continue;
    }

    
    array_push($purchaseOrders,$purchaseOrder);
    $i++;
    /*
    $res = $purchaseOrder->post();

    echo "<br><br>";
    
    if($res->success)
        $purchaseOrder->setStatus($conn,OK,$res->message);
    else{
        $purchaseOrder->setStatus($conn,NOT_OK,$res->message);
        $err++;
    }
    */
}

$j = 0;

while ($j < $i){

    $res = $purchaseOrders[$j]->post();
    if($res->success)
        $purchaseOrder->setStatus($conn,OK,$res->message);
    else{
        $purchaseOrder->setStatus($conn,NOT_OK,$res->message);
    }
    $j++;
}

print_errors($err);
