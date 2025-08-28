<?php

require('addons.php');
require('clientes.php');
require('provider.php');

$conn = connect();
$query = "SELECT * FROM CLIENTES_PENDIENTES";
$stmt = $conn->query($query);

$i = 0;
while($sol = $stmt->fetch(PDO::FETCH_ASSOC)){
    $clientes = New clientSaveDF($sol);
    $res = $clientes->post();
    if (strpos($res->message, 'ya existe')){
        $query = "SELECT * FROM CARCLI WHERE CODCAR = 28 AND CODCLI = ".$sol['CODCLI'];
        $stmt2 = $conn->query($query);
        if ($stmt2->rowCount() == 0)
            $query = "INSERT INTO CARCLI(CODCAR,CODCLI,VALOR) VALUES(28,".$sol['CODCLI'].",1)";
        else    
            $query = "  UPDATE CARCLI SET VALOR = 1 WHERE CODCAR = 28 and CODCLI = ".$sol['CODCLI'];
        $stmt3 = $conn->query($query);
    }
    if ($res->success){
        $query = "SELECT * FROM CARCLI WHERE CODCAR = 28 AND CODCLI = ".$sol['CODCLI'];
        $stmt2 = $conn->query($query);
        if ($stmt2->rowCount() == 0)
            $query = "INSERT INTO CARCLI(CODCAR,CODCLI,VALOR) VALUES(28,".$sol['CODCLI'].",1)";
        else    
            $query = "  UPDATE CARCLI SET VALOR = 1 WHERE CODCAR = 28 and CODCLI = ".$sol['CODCLI'];
        $stmt3 = $conn->query($query);
    }
}

$query = "SELECT * FROM TRANSPOR_PENDIENTES";
$stmt = $conn->query($query);

$i = 0;
while($sol = $stmt->fetch(PDO::FETCH_ASSOC)){

    $provider = New providerSaveDF();
    $provider.crear($sol);
    $res = $provider->post();


    if (strpos($res->message, 'ya existe')){
        $query = "SELECT * FROM CARTRA WHERE CODCAR = 29 AND CODTRA = ".$sol['CODTRA'];
        $stmt2 = $conn->query($query);
        if ($stmt2->rowCount() == 0)
            $query = "INSERT INTO CARTRA(CODCAR,CODTRA,VALOR) VALUES(29,".$sol['CODTRA'].",1)";
        else    
            $query = "  UPDATE CARTRA SET VALOR = 1 WHERE CODCAR = 29 and CODTRA = ".$sol['CODTRA'];
        $stmt3 = $conn->query($query);
    }

    if ($res->success){
        $query = "SELECT * FROM CARTRA WHERE CODCAR = 29 AND CODTRA = ".$sol['CODTRA'];
        $stmt2 = $conn->query($query);
        if ($stmt2->rowCount() == 0)
            $query = "INSERT INTO CARTRA(CODCAR,CODTRA,VALOR) VALUES(29,".$sol['CODTRA'].",1)";
        else    
            $query = "  UPDATE CARTRA SET VALOR = 1 WHERE CODCAR = 29 and CODTRA = ".$sol['CODTRA'];
        $stmt3 = $conn->query($query);
    }
    else {
        $query = "UPDATE TRANSPOR SET OBSERVA = '".$res->message."' WHERE CODIGO = ".$sol['CODTRA'];
        $stmt2 = $conn->query($query);
    }
}


?>