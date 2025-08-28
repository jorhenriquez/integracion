<?php

function getpdf($conn){
    $query = "select DOCCLI.SERIE, DOCCLI.ANO, DOCCLI.CODIGO, GD.ID from DOCCLI
                LEFT JOIN GD ON GD.SERIE = DOCCLI.SERIE AND GD.ANO = DOCCLI.ANO AND GD.CODIGO = DOCCLI.CODIGO AND GD.CLASE = 'FC' AND GD.TIPO = 1
                WHERE GD.ID IS NULL AND DOCCLI.FECHA >= '2022-02-18' AND DOCCLI.SERIE = 'F' and DOCCLI.CONTA = 'S'";
    $stmt = $conn->query($query);
    return $stmt;

}
function GetPDFDocumentBase64($conn,$tipo,$ano,$folio){

    $json_url = 'https://api.defontana.com/api/Sale/GetPDFDocumentBase64?';
 
    $data = array(  'documentType'=>$tipo,
                    'folio'=>$folio);
 
    $data = http_build_query($data);
 
    // Initializing curl
    $ch = curl_init($json_url.$data);
 
    // Configuring curl options
    
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/json','Authorization: bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1laWQiOiJBRDEyM0ZULUhHREY1Ni1LSTIzS0wtS0pUUDk4NzYtSEdUMTIiLCJ1bmlxdWVfbmFtZSI6ImNsaWVudC5sZWdhY3lAZGVmb250YW5hLmNvbSIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vYWNjZXNzY29udHJvbHNlcnZpY2UvMjAxMC8wNy9jbGFpbXMvaWRlbnRpdHlwcm92aWRlciI6IkFTUC5ORVQgSWRlbnRpdHkiLCJBc3BOZXQuSWRlbnRpdHkuU2VjdXJpdHlTdGFtcCI6IkdIVEQyMzQtS0xISjc4NjgtRkc0OTIzLUhKRzA4RlQ1NiIsImNvbXBhbnkiOiIyMDIxMDgxMzIwNTMyOTA1MDAwNCIsImNsaWVudCI6IjIwMjEwODEzMjA1MzI5MDUwMDA0Iiwib2xkc2VydmljZSI6InNpbHZlciIsInVzZXIiOiJnZXJlbmNpYSIsInNlc3Npb24iOiIxNjMyNzc0OTA5Iiwic2VydmljZSI6InNpbHZlciIsImNvdW50cnkiOiJDTCIsImNvbXBhbnlfbmFtZSI6IlRyYW5zcG9ydGVzIFN1cGVydHJhbnMgTHRkYSIsInVzZXJfbmFtZSI6IkpvcmdlIEhlbnLDrXF1ZXogTXXDsW96Iiwicm9sZXNQb3MiOiJbXCJ1c3VhcmlvXCIsXCJ1c3VhcmlvZXJwXCJdIiwicnV0X3VzdWFyaW8iOiIxNi4yODMuODkxLTEiLCJpc3MiOiJodHRwczovLyouZGVmb250YW5hLmNvbSIsImF1ZCI6IjA5OTE1M2MyNjI1MTQ5YmM4ZWNiM2U4NWUwM2YwMDIyIiwiZXhwIjoyMDExMzgwMjg2LCJuYmYiOjE2MzI3NzU0ODZ9.PTHvSm-iG82DiYmQYFjFTuqYHvAicWfhZHCmxV12NwE'));
 
    // Getting results
    $result = curl_exec($ch); // Getting jSON result string
    $res = array();
    $res = json_decode($result);
 
    

    $pdf_content = $res->document;;
    //Decode pdf content
    $pdf_decoded = base64_decode ($pdf_content);


    $query = "  INSERT INTO GD(ID,CLASE,TIPO,SERIE,ANO,CODIGO,DESCRIPCION,ARCHIVO,FORMATO,PAG,FECHA,CODUSU,PUBWEB) 
                VALUES((SELECT MAX(ID)+1 FROM GD),'FC',1,'F',".$ano.",".$folio.",'FACTURA ORIGINAL','','P',1,GETDATE(),1,'N')";
    
    $stmt = $conn->query($query);

    $query = "SELECT ID FROM GD WHERE CLASE = 'FC' AND TIPO = 1 AND SERIE = 'F' AND ANO = ".$ano." AND CODIGO = ".$folio;
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $row['ID'];
    
    //Write data back to pdf file
    $pdf = fopen ('C:\Transporte\ARIS\TIRSQL\GD\FAC CLI\ORIGINAL\001FC_001_000'.$id.'.pdf','w');
    $query = "  UPDATE GD
                SET ARCHIVO = '\ARIS\TIRSQL\GD\FAC CLI\ORIGINAL\\001FC_001_000".$id.".pdf'
                WHERE ID = ".$id;
    $stmt = $conn->query($query);
    //$pdf = fopen ($id.'.pdf','w');
    fwrite ($pdf,$pdf_decoded);
    //close output file
    fclose ($pdf);
    }
?>