<?php 

function connect_sistema(){
    $servername = "209.126.103.129";
    $username = "plasupert";
    $password = "az}K2UkWnaym";
    $db = "plasupert_prd";
    $port = "3306";

// Create connection
    $conn = new mysqli($servername, $username, $password,$db,$port);
    $conn->set_charset("utf8");

// Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function connect_meribia(){
    $serverName = "186.67.120.132, 49274";   
    $database = "MT001";  
   
    // Get UID and PWD from application-specific files.   
    $uid = "sa";  
    $pwd = "Servid0r";
    
    try {  
       $conn = new PDO( "sqlsrv:server=$serverName;Database = $database", $uid, $pwd);   
       $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
       $conn->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8); 
    }  
   
    catch( PDOException $e ) {  
      echo 'Falló la conexión: ' . $e->getMessage();
       die( "<br>Error connecting to SQL Server");   
    }  

    return $conn;
}



function adec($str){
    return str_replace('\'','', $str);
}

function pedidos_create(){
    $sql = "SELECT triggers.id as triggers_id, users.codigo_cliente, comunas.CODRUT, pedidos.* FROM pedidos 
    inner join users on users.id = pedidos.user_id
    inner join comunas on comunas.CODCOM = pedidos.comuna_id
    inner join triggers on triggers.pedidos_id = pedidos.id and triggers.tipo = 'I'";

    return $sql;

}

function pedidos_update(){
    $sql = "SELECT triggers.id, triggers.pedidos_id, triggers.tipo, pedidos.pedext_id FROM triggers 
            inner join pedidos on pedidos.id = triggers.pedidos_id 
            WHERE triggers.tipo = 'U' and pedidos.esta_respaldado = 0";

    return $sql;
    
}

function carga_update(){
    $sql = "SELECT pedidos.pedext_id, pedidos.id, CONCAT(users.codigo_cliente,'-',pedidos.numero_documento) as referencia FROM pedidos 
            INNER JOIN users on users.id = pedidos.user_id
            WHERE   (pedidos.codcar is null or pedidos.codcar = 0) 
            and (pedidos.pedext_id <> 0 and pedidos.pedext_id is not null)";
    return $sql;
}

function pedidos_delete(){
    $sql = "SELECT * FROM triggers 
            WHERE triggers.tipo = 'D'";

    return $sql;
    
}

?>
