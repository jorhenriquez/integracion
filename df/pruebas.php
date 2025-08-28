<?php

require_once('addons.php');
require_once('clientes.php');
require_once('provider.php');
require_once('date.php');
require_once('taxes.php');
require_once('details.php');
require_once('desc.php');
require_once('attachment.php');
require_once('analisis.php');
require_once('storage.php');


$conn = connect();

$query = "SELECT * FROM OCTRANSPOR_PENDIENTES";


$stmt = $conn->query($query);

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

    // Revisa que el cliente exista en sistema Defontana
    $oc = New OrdenDeCompraDF();
    $provider = New providerSaveDF();
    

    if ($provider->GetProvider($row['rut'])){

        $oc->crear($row);
        continue;
    }
}
