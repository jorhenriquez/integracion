<?php
    require_once("funciones.php");
    require_once("moduleInsert.php");

    $conn = connect_sistema();
    $conn2 = connect_meribia();

    $sql = "SELECT * FROM users WHERE users.rut is NULL and users.deleted_at is NULL and users.id >= 7";
    $result = mysqli_query($conn,$sql);
    /* Buscar el código de cliente que no tenga un usuario creado */
    if (mysqli_num_rows($result) > 0) {
        while($row = $result->fetch_assoc()) {
            $sql2 = "   SELECT CLIENTES.NIF, CLIENTES.NOMFIS AS NOMBRE, CARCLI.VALOR FROM CLIENTES
                        LEFT JOIN CARCLI ON CARCLI.CODCLI = CLIENTES.CODIGO AND CARCLI.CODCAR = 33
                        WHERE
                            CLIENTES.CODIGO = ".$row['codigo_cliente'];

            $stmt = $conn2->query($sql2);
            while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
                if (is_null($row2['VALOR'])){
                    /* agregar a meribia el usuario */
                    $sql = "INSERT INTO CARCLI(CODCAR,CODCLI,VALOR) VALUES (33,".$row['codigo_cliente'].",'".$row['email']."')";
                    $stmt2 = $conn2->query($sql);
                    /* actualizar el valor del rut en plataforma */
                    $sql = "UPDATE users
                            SET nombre = '".$row2['NOMBRE']."', rut = '".$row2['NIF']."'
                            WHERE id = ".$row['id'];
                    $result2 = mysqli_query($conn,$sql);
                }
                else{
                    /* actualizar a meribia el usuario */
                    $sql = "UPDATE CARCLI
                            SET VALOR = '".$row['email']."'
                            WHERE CODCAR = 33 AND CODCLI = ".$row['codigo_cliente'];
                    $stmt2 = $conn2->query($sql);
                    /* actualizar el valor del rut en plataforma */
                    $sql = "UPDATE users
                            SET nombre = '".$row2['NOMBRE']."', rut = '".$row2['NIF']."'
                            WHERE id = ".$row['id'];
                    $result2 = mysqli_query($conn,$sql);
                }
            }
        }
    }
?>