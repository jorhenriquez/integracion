<?php
    require_once("funciones.php");
    require_once("moduleInsert.php");

    $conn = connect_sistema();
    $conn2 = connect_meribia();

    $sql = pedidos_create(); 
    
    /* Busca todos los pedidos que no estén ingresados en cargas en sistema */
    
    $result = mysqli_query($conn,$sql);

    if (mysqli_num_rows($result) > 0) {

        // Este movimiento se realiza una vez por integración
        // para obtener la información de cada cliente de forma rápida y eficiente.

        $clientes = array();
        $tipfac = array();
        $consignatario = array();

        $query = "SELECT CODIGO, NIF, TIPFAC FROM CLIENTES ORDER BY CODIGO";
        $stmt = $conn2->query($query);

        while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
            $clientes[$row2['CODIGO']] = $row2['NIF'];
            $tipfac[$row2['CODIGO']] = $row2['TIPFAC'];
        }



        while($row = $result->fetch_assoc()) {

            $pedext = new PEDEXT();
            $pedext->nuevo($conn,$conn2,$clientes,$tipfac,$row);
            $sql = $pedext->insert();
    

            try {
                $stmt3 = $conn2->query($sql);
                $ultimo_codigo = $conn2->lastInsertId();
                
                $sql2 = "   UPDATE pedidos
                            SET pedext_id = ".$ultimo_codigo."
                            WHERE id = ".$row['id'];

                $result2 = mysqli_query($conn,$sql2);
                $devlinext_abat = new DEVLINEXT();
                $devlinext_abat->agregar(1,$ultimo_codigo,$pedext);
                $devlinext_abat->post($conn2);
                
                $sql_delete_trigger = "DELETE FROM triggers WHERE pedidos_id = ".$row['id'];
                $stmt_delete_trigger = mysqli_query($conn,$sql_delete_trigger);

            } catch (PDOException $e) {
                echo "Error de al insertar: " . $e->getMessage();   
                echo "<br>";
                print_r($sql);
                echo "<br>";
                echo "<br>";
            }
        }
    }

    /* Borrar los pedidos */

    $sql = pedidos_delete(); 
    $result = mysqli_query($conn,$sql);

    if (mysqli_num_rows($result) > 0) {
        while($row = $result->fetch_assoc()) {
            $delete_linpdc = "  DELETE LINPDC
                                FROM LINPDC
                                INNER JOIN PEDCLI ON LINPDC.CODPDC = PEDCLI.CODIGO
                                INNER JOIN PEDEXT ON PEDCLI.CODIGO = PEDEXT.CODPDC
                                inner join DEVLINEXT on DEVLINEXT.PEDEXT = PEDEXT.CODIGO 
                                WHERE PEDEXT.CODIGO = ".$row['pedext_id'];
            $delete_pedcli = "  DELETE PEDCLI
                                FROM PEDCLI
                                INNER JOIN PEDEXT ON PEDCLI.CODIGO = PEDEXT.CODPDC
                                inner join DEVLINEXT on DEVLINEXT.PEDEXT = PEDEXT.CODIGO 
                                WHERE PEDEXT.CODIGO = ".$row['pedext_id'];
            $delete_devlinext = "   DELETE DEVLINEXT
                                    FROM DEVLINEXT
                                    inner join PEDEXT on DEVLINEXT.PEDEXT = PEDEXT.CODIGO 
                                    WHERE PEDEXT.CODIGO = ".$row['pedext_id'];
            $delete_pedext = "  DELETE PEDEXT
                                FROM PEDEXT
                                WHERE PEDEXT.CODIGO = ".$row['pedext_id'];
            
            if (isset($row['pedext_id'])){
                $stmt = $conn2->query($delete_linpdc.$delete_pedcli.$delete_devlinext.$delete_pedext);
                $sql_delete_trigger = "DELETE FROM triggers WHERE id = ".$row['id'];
                $stmt_delete_trigger = mysqli_query($conn,$sql_delete_trigger);
            }
        }
    }

    /* Actualiza todos los pedidos */

    $sql = pedidos_update(); 
    $result = mysqli_query($conn,$sql);

    if (mysqli_num_rows($result) > 0) {
        
        while($row = $result->fetch_assoc()) {
            
            if (!isset($row['pedext_id'])){
                print_r($row);
                echo "<br>";
                print_r("error en carga: ".$row);
                continue;
            }    

            $delete_linpdc = "  DELETE LINPDC
                                FROM LINPDC
                                INNER JOIN PEDCLI ON LINPDC.CODPDC = PEDCLI.CODIGO
                                INNER JOIN PEDEXT ON PEDCLI.CODIGO = PEDEXT.CODPDC
                                inner join DEVLINEXT on DEVLINEXT.PEDEXT = PEDEXT.CODIGO 
                                WHERE PEDEXT.CODIGO = ".$row['pedext_id'];
            $delete_pedcli = "  DELETE PEDCLI
                                FROM PEDCLI
                                INNER JOIN PEDEXT ON PEDCLI.CODIGO = PEDEXT.CODPDC
                                inner join DEVLINEXT on DEVLINEXT.PEDEXT = PEDEXT.CODIGO 
                                WHERE PEDEXT.CODIGO = ".$row['pedext_id'];
            $delete_devlinext = "   DELETE DEVLINEXT
                                    FROM DEVLINEXT
                                    inner join PEDEXT on DEVLINEXT.PEDEXT = PEDEXT.CODIGO 
                                    WHERE PEDEXT.CODIGO = ".$row['pedext_id'];
            $delete_pedext = "  DELETE PEDEXT
                                FROM PEDEXT
                                WHERE PEDEXT.CODIGO = ".$row['pedext_id'];
            print_r($delete_linpdc.$delete_pedcli.$delete_devlinext.$delete_pedext);
            echo "<br>";
            $stmt = $conn2->query($delete_linpdc.$delete_pedcli.$delete_devlinext.$delete_pedext);
            $delete_pedidos = "DELETE FROM triggers WHERE pedidos_id = ".$row['pedidos_id'];
            $stmt2 = mysqli_query($conn,$delete_pedidos);
            $pedido_insert = "INSERT INTO triggers(pedidos_id,tipo) VALUES(".$row['pedidos_id'].",'I')";
            $stmt2 = mysqli_query($conn,$pedido_insert);
        }
    }

    /* Bloquea todos los pedidos que ya están ingresados al sistema como CARGA */

    $sql = carga_update();
    $result = mysqli_query($conn,$sql);

    print_r($sql);
    if (mysqli_num_rows($result) > 0) {
        while($row = $result->fetch_assoc()) {
            $sql = "SELECT TOP 1 CODIGO as carga FROM CARGA WHERE REFERENCIA = '".$row['referencia']."' ORDER BY CODIGO ASC";
            
            $stmt = $conn2->query($sql);
            while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
                $sql2 = "   UPDATE pedidos
                            SET esta_respaldado = 1, codcar = ".$row2['carga']."
                            WHERE id = ".$row['id'];
                $stmt2 = mysqli_query($conn,$sql2);

                $sql2 = "   DELETE FROM triggers WHERE pedidos_id = ".$row['id'];
                $stmt2 = mysqli_query($conn,$sql2);
            }
        }
    }

