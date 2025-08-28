<?php

require_once('C:\xampp\htdocs\df\clases\Funciones\definiciones.php');
require_once('C:\xampp\htdocs\df\clases\Database\connect.php');
require_once('C:\xampp\htdocs\df\clases\Funciones\funciones.php');

$conn = connect();

/* Buscar facturas sin nota de venta */

$query = "  SELECT	DOCCLI.SERIE,
                    DOCCLI.ANO,
                    DOCCLI.CODIGO,
                    MAX(CARGA.FECHA) AS FECHA,
                    CARCLI.VALOR 
            FROM DOCCLI
            LEFT JOIN CARCLI ON CARCLI.CODCLI = DOCCLI.CODCLI AND CODCAR = 39  
            inner join CARGA ON CARGA.SERIE = DOCCLI.SERIE AND CARGA.ANO = DOCCLI.ANO AND CARGA.FACTURA = DOCCLI.CODIGO
            WHERE	DOCCLI.CONTA = 'S' AND DOCCLI.USOINTERNO = '' AND 
                    DOCCLI.SERIE = 'F' AND 
                    DOCCLI.ANO >= 2025
            GROUP BY
                    DOCCLI.SERIE,
                    DOCCLI.ANO,
                    DOCCLI.CODIGO,
                    CARCLI.VALOR
            ORDER BY DOCCLI.CODIGO DESC";
$stmt = $conn->query($query);

/* Crear detalle de la nota de venta */

$contador = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $contador++;
    $chipax['exenta'] = false;
    $chipax['fecha'] = $row['FECHA'];
    $chipax['fecha_pago'] = $row['FECHA'];
    $chipax['cliente_id'] = intval($row['VALOR']);
    $chipax['otro_ingreso'] = false;
    $chipax['nota'] = 'Ingreso Automaticamente por Meribia';
    $chipax['items'] = array();

    $query = "SELECT	DOC_DETALLE.*, 
                        PROYECTOS.TELEFONO AS ID,
                        RUTAS.DESTINO
                        FROM DOC_DETALLE 
                        INNER JOIN RUTAS ON RUTAS.CODIGO = DOC_DETALLE.CODRUT 
                        INNER JOIN PROYECTOS ON PROYECTOS.CODIGO = RUTAS.CODPRY
                        WHERE   DOC_DETALLE.SERIE = '".$row['SERIE']."' 
                            AND DOC_DETALLE.ANO = ".$row['ANO']."  
                            AND DOC_DETALLE.CODIGO = ".$row['CODIGO'];

    $stmt2 = $conn->query($query);
    while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
        $chipax_det['fecha'] = $row['FECHA'];
        $chipax_det['linea_negocio_id'] = intval($row2['ID']);
        $chipax_det['producto_id'] = 129413;
        $chipax_det['descripcion'] = 'Distribucion '.eliminar_acentos($row2['DESTINO']);
        $chipax_det['cantidad'] = 1;
        $chipax_det['precio_unitario'] = floatval($row2['PRECIO']);
        $chipax_det['moneda_id'] = 1000;
        $chipax_det['descuento'] = 0;
        $chipax_det['valor_moneda'] = 1;

        array_push($chipax['items'],$chipax_det);
    }

    /* Subir nota de venta */

    $json_url = 'https://api.chipax.com/v2/notas-venta';
    $data = json_encode($chipax);

    // Initializing curl
    $ch = curl_init($json_url);

    // Configuring curl options

    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/json','Authorization: JWT eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MTI3NjgsImV4cGlyYXRpb24iOjE3NDg2MjQ3OTksImlhdCI6MTc0ODYyMTE5OX0.oT7pCuK_G4VdH9o-RUXeFlhTJZ-V0Lb8uSKGKnDOPWs'));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);

    // Getting results
    $result = curl_exec($ch); // Getting jSON result string
    $res = array();
    $res = json_decode($result);

    if (isset($res->id)){
        $query = "  UPDATE DOCCLI 
                    SET USOINTERNO = '".$res->folio."', MEMO = 'Nota de Venta asociada: ".$res->folio."' 
                    WHERE SERIE = 'F' AND ANO = ".$row['ANO']." AND CODIGO = ".$row['CODIGO'];
        echo "<br><br>".$query;
        $stmt3 = $conn->query($query);
    }
    else   {
        echo "<br><br>";
        print_r($res);
    }

    if ($contador == 10)
        break;
}