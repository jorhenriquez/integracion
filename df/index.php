<?php

    require('documentSale.php');
    require('addons.php');

    $conn = connect();

    $query = "SELECT * FROM DOC_PENDIENTES";

    $stmt = $conn->query($query);

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

        // Revisa que el cliente exista en sistema Defontana
        $datos = GetClient($row['clientFile']);
        if ($datos->totalItems == 0){

            // Si no existe, indica error que no exite el cliente
            $query2 = " SET NOCOUNT ON;
                        UPDATE DOCCLI 
                        SET CONTA = 'N',MEMO ='No existe el cliente'    
                        WHERE   SERIE = '".$row['documentType']."'  
                        AND ANO = ".$row['ANO']."  
                        AND CODIGO = ".$row['CODIGO'];
        
            $stmt2 = $conn->query($query2);
            continue;
        }

        // Para Facturas

        if($row['documentType'] == 'F'){

            $documento = new SaveSaleDF($row);
            $sum = 0;

            for($i=0;$i < sizeof($documento->details);$i++){
                $sum = $sum+$documento->details[$i]->price;
            }

            if(ceil($sum) == $row['BASE'] || floor($sum) == $row['BASE'])     
                $res = $documento->post();
            else {
                if (sizeof($documento->details) == 0){
                    $res = new \stdClass();
                    $res->success = false;
                    $res->message = "No hay cargas en la factura, favor revisar.";
                }
                else {
                    $query3 = " SELECT DOC_DETALLE.CODRUT 
                        FROM DOC_DETALLE 
                        LEFT JOIN PROYECTOS ON PROYECTOS.CODIGO = DOC_DETALLE.CODRUT
                        WHERE PROYECTOS.DESCRIPCION IS NULL 
                            AND DOC_DETALLE.SERIE = '".$row['documentType']."'  
                            AND DOC_DETALLE.ANO = ".$row['ANO']."  
                            AND DOC_DETALLE.CODIGO = ".$row['CODIGO'];
                    $stmt3 = $conn->query($query3);
                    $fila = $stmt3->fetch(PDO::FETCH_ASSOC);
                
                    $res = new \stdClass();
                    $res->success = false;
                    $res->message = "Ruta ".$fila['CODRUT']." no tiene proyecto asociado";
                }
            }
            if($res->success){
                $query2 = " SET NOCOUNT ON;
                        UPDATE DOCCLI 
                        SET CONTA = 'S',MEMO =''    
                        WHERE   SERIE = '".$row['documentType']."'  
                        AND ANO = ".$row['ANO']."  
                        AND CODIGO = ".$row['CODIGO'];

                $stmt2 = $conn->query($query2);
            }
            else{
                $query2 = " SET NOCOUNT ON; 
                        UPDATE DOCCLI 
                        SET MEMO = '".eliminar_acentos($res->message)."'    
                        WHERE   SERIE = '".$row['documentType']."'  
                        AND ANO = ".$row['ANO']."  
                        AND CODIGO = ".$row['CODIGO'];
                $stmt2 = $conn->query($query2);
            }
        }

        if($row['documentType'] == 'R'){
            $documento = new SaveCreditNoteDF($row);
            $sum = 0;

            $documento.post();
            
            if($res->success){
                $query2 = " SET NOCOUNT ON;
                        UPDATE DOCCLI 
                        SET CONTA = 'S',MEMO =''    
                        WHERE   SERIE = '".$row['documentType']."'  
                        AND ANO = ".$row['ANO']."  
                        AND CODIGO = ".$row['CODIGO'];

                $stmt2 = $conn->query($query2);
            }
            else{
                $query2 = " SET NOCOUNT ON; 
                        UPDATE DOCCLI 
                        SET MEMO = '".eliminar_acentos($res->message)."'    
                        WHERE   SERIE = '".$row['documentType']."'  
                        AND ANO = ".$row['ANO']."  
                        AND CODIGO = ".$row['CODIGO'];
                $stmt2 = $conn->query($query2);
            }
        }
    }

?>