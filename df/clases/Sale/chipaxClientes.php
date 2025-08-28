<?php

    
require_once('C:\xampp\htdocs\df\clases\Database\connect.php');


function chipaxGetClientes($rut){
    $json_url = 'https://api.chipax.com/v2/clientes?';
 
    $data = array('rut'=>$rut);
 
    $data = http_build_query($data);
 
    // Initializing curl
    $ch = curl_init($json_url.$data);
 
    // Configuring curl options
    
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/json','Authorization: JWT eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MTI3NjgsImV4cGlyYXRpb24iOjE3NDg2MjQ3OTksImlhdCI6MTc0ODYyMTE5OX0.oT7pCuK_G4VdH9o-RUXeFlhTJZ-V0Lb8uSKGKnDOPWs'));
 
    // Getting results
    $result = curl_exec($ch); // Getting jSON result string
    $res = array();
    $res = json_decode($result);
 
    return $res;
}

function chipaxClienteExists($rut){
    $res = chipaxGetClientes($rut);
    if(count($res) == 0)
       return false;
    else 
       return true;
}

function updateOnMeribiaChipaxId($conn,$codigo,$chipaxId){
    $query = 'INSERT INTO CARCLI(CODCAR,CODCLI,VALOR) VALUES(39,'.$codigo.','.$chipaxId.')';
    echo $query."<br>";
    $stmt = $conn->query($query);
}

$conn = connect();
$query = "SELECT * FROM CLIENTES_CHIPAX WHERE (VALOR IS NULL OR VALOR = 0) ORDER BY CODIGO";
$stmt = $conn->query($query);

while($sol = $stmt->fetch(PDO::FETCH_ASSOC)){
    $rut = ltrim(str_replace('.', '', $sol['NIF']),'0');
    $res = chipaxGetClientes($rut);
    if (count($res) > 0)
        updateOnMeribiaChipaxId($conn,$sol['CODIGO'],$res[0]->id);
    elseif($sol['VALOR'] != 0)
        updateOnMeribiaChipaxId($conn,$sol['CODIGO'],0);
    
}
 
 ?>