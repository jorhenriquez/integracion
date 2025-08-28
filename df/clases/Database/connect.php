<?php
function connect(){
    $serverName = "localhost, 49274";   
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
       die( "<br>Error connecting to SQL Server aqui");   
    }  

    return $conn;
}
?>