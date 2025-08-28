<?php

class DEVLINEXT {
    public $PEDEXT;
    public $ORDEN;
    public $FECHA;
    public $NUMFAC;
    public $KILOS;
    public $UNIDADES;
    public $BULTOS;
    public $VOLUMEN;
    public $UNIVOL;
    public $VALMER;

    function agregar($tipo,$codigo,$pedext){
        switch($tipo){
            case 1:
                $this->PEDEXT = $codigo;
                $this->ORDEN = 1;
                $this->FECHA = $pedext->FECHORORI;
                $this->NUMFAC = $pedext->DOCUMENTOS;
                $this->KILOS = $pedext->PESO;
                $this->UNIDADES = $pedext->ENVASES;
                $this->BULTOS = $pedext->UNIDADES;
                $this->VOLUMEN = $pedext->VOLUMEN;
                $this->VALMER = $pedext->VALMER;
                break;
            case 2:
                $this->PEDEXT = $codigo;
                $this->ORDEN = 2;
                $this->FECHA = $pedext->FECHORDES;
                $this->NUMFAC = $pedext->DOCUMENTOS;
                $this->KILOS = $pedext->PESO;
                $this->UNIDADES = $pedext->ENVASES;
                $this->BULTOS = $pedext->UNIDADES;
                $this->VOLUMEN = $pedext->VOLUMEN;
                $this->VALMER = $pedext->VALMER;
                break;
        }
    }

    function post($conn){
        $sql = "INSERT INTO DEVLINEXT(
        PEDEXT,
        ORDEN,
        FECHA,
        NUMFAC,
        KILOS,
        UNIDADES,
        BULTOS,
        VOLUMEN,
        VALMER)
        VALUES (".$this->PEDEXT.",
                ".$this->ORDEN.",
                '".$this->FECHA."',
                '".$this->NUMFAC."',
                ".$this->KILOS.",
                ".$this->UNIDADES.",
                ".$this->BULTOS.",
                ".$this->VOLUMEN.",
                ".$this->VALMER.")";
    
        $stmt = $conn->query($sql);
    }
}

class PEDEXT{
    public $TIPO;
    public $NIFCLI;
    public $NIFCON;
    public $NIFREM;
    public $NIFDES;
    public $NOMCOM;
    public $FECHA;
    public $FECLIM;
    public $REFERENCIA;
    public $NUMPLA;
    public $CMR;
    public $CRT;
    public $DTMI;
    public $NOMTIPMER;
    public $NOMCAT;
    public $NOMCLA;
    public $CODPRY;
    public $CODRUT;
    public $PGORI = 0;
    public $ORIGEN;
    public $DIRORI1;
    public $DIRORI2;
    public $CPORI;
    public $POBLAORI;
    public $PROVIORI;
    public $NOMPAIORI;
    public $ZONORI;
    public $FECHORORI;
    public $HASFECHORORI;
    public $REFORI;
    public $CONORI;
    public $TELORI;
    public $MOVORI;
    public $PGDES = 0;
    public $DESTINO;
    public $DIRDES1;
    public $DIRDES2;
    public $CPDES;
    public $POBLADES;
    public $PROVIDES;
    public $NOMPAIDES;
    public $ZONDES;
    public $FECHORDES;
    public $HASFECHORDES;
    public $REFDES;
    public $CONDES;
    public $TELDES;
    public $MOVDES;
    public $NOMMER;
    public $PESO = 0;
    public $NOMENV;
    public $ENVASES = 0;
    public $UNIDADES = 0;
    public $VOLUMEN = 0;
    public $METROS = 0;
    public $NUMDOC;
    public $DOCUMENTOS;
    public $REQMAT;
    public $NOMTIPMAT;
    public $TIPFAC;
    public $CANTIDAD;
    public $PREUNI;
    public $VALMER;
    public $TELEFONO;
    public $DESCRIPCION;
    public $CLAVE1;
    public $OBSERVACIONES;
    public $PROCESANDO;
    public $PROCESADO;
    public $CODPDC;
    public $BORRADO;
    public $ERROR;
    public $NOTASERROR;


    
    function __construct(){
        $this->TIPO = 'D';
        $this->NOMCOM = 'API';
        $this->NUMPLA = '0';
        $this->CRT = '0';
        $this->DTMI = '0';
        $this->NOMTIPMER = '1. Estandar';
        $this->NOMCAT = '';
        $this->NOMCLA = '';
        $this->CODPRY = '';
        $this->NOMMER = 'Varios';
        $this->METROS = 0.0;
        $this->NUMDOC = 1;
        $this->REQMAT = '';
        $this->NOMTIPMAT = '';
        $this->CANTIDAD = 0;
        $this->PREUNI = 0;
        $this->TELEFONO = '';
        $this->DESCRIPCION = '';
        $this->CLAVE1  ='';
        $this->PROCESANDO = 0;
        $this->PROCESADO = 0;
        $this->CODPDC = 0;
        $this->BORRADO = 0;
        $this->ERROR = 0;
        $this->NOTASERROR = '';

    }

    function cliente($cliente){
        $this->NIFCLI = $cliente;
        $this->NIFREM = $cliente;
        $this->NIFREM = $cliente;
    }

    function consignatario($cliente,$ag){
        switch($ag){
        case 1:
		     $agencia = 809;
		        break;
	    case 2:
		     $agencia = 810;
		        break;
        case 3:
		     $agencia = 2;
		        break;
        case 4:
                $agencia = 3;
                break;
	    case 5:
                $agencia = 527;
                break;
	    case 6:
                $agencia = 727;
                break;
        case 7:
                $agencia = 4;
                break;
        case 8:
                $agencia = 6;
                break;
        case 9:
                $agencia = 8;
                break;
        case 10:
                $agencia = 10;
                break;
        case 11:
                $agencia = 11;
                break;
        case 13:
                $agencia = 807;
                break;
        case 14:
                $agencia = 9;
                break;
        case 15:
                $agencia = 809;
                break;
        case 16:
                $agencia = 5;
                break;
        case 18:
                $agencia = 782;
                break;
            
        }

        $this->NIFCON = $cliente[$agencia];
    }

    function nomenv($tipo){
        if ($tipo == 1) return "Cajas";
        if ($tipo == 2) return "Palet";
        return "Cajas";
    }

    function cantidad_envases($cantidad, $tipo){
        if ($tipo == 2) return $cantidad;
        return 0;
    }

    function cantidad_unidades($cantidad, $tipo){
        if ($tipo == 1) return $cantidad;
        return 0;
    }

    function pedidos($conn,$sol){

        $this->REFERENCIA = $sol['codigo_cliente']."-".adec($sol['numero_documento']);
        $this->CODRUT = $sol['CODRUT'];
        $this->CMR = adec($sol['folio_interno']);
        $this->DTMI = '';
        $this->FECHA = $sol['fecha_entrega'];
        $this->FECHORORI =  $sol['fecha_entrega'];
        $this->HASFECHORORI =  $sol['fecha_entrega'];
        $this->FECHACLIM = $sol['fecha_entrega'];
        $this->NOMENV = $this->nomenv($sol['tipo']);
        $this->ENVASES = $this->cantidad_envases($sol['cantidad'],$sol['tipo']);
        $this->UNIDADES = $this->cantidad_unidades($sol['cantidad'],$sol['tipo']);

        if(!is_null($sol['peso']))
            $this->PESO = $sol['peso'];
        
        if(!is_null($sol['volumen']))
            $this->VOLUMEN = $sol['volumen'];

        $this->VALMER = $sol['valor_neto'];
        $this->OBSERVACIONES = adec($sol['observaciones']);
        $this->DOCUMENTOS = adec($sol['numero_documento']);

    }

    function nuevo($conn,$conn2,$cliente,$tipfac,$row){

        $this->dias_despacho($conn2,$row['fecha_entrega'],$row);
        $this->cliente($cliente[$row['codigo_cliente']]);
        $this->consignatario($cliente,$row['agencia_id']);
        $this->tipfac($tipfac[$row['codigo_cliente']]);
        $this->pedidos($conn,$row);
        $this->direccion('',"ORIGEN");
	$this->direccion($row,"DESTINO2");
	print_r($this);
    }
    function tipfac($tipo){
        // Obtyener tipo de facturacion //
        $this->TIPFAC = $tipo;
    }

    function direccion($ori,$tipo){
        switch($tipo){
            case "ORIGEN":
                $this->PGORI = 13677;
                $this->ORIGEN = 'API';
                $this->DIRORI1 = 'CARGA POR SISTEMA';
                $this->DIRORI2 = '';
                $this->CPORI = 'API';
                $this->POBLAORI = 'Comuna API';
                $this->PROVIORI = 'API';
                $this->NOMPAIORI = 'CHILE';
                $this->ZONORI = 'API';
                $this->REFORI = '';
                $this->CONORI = '';
                $this->TELORI = '';
                $this->MOVORI = '';
                break;
            case "DESTINO":
                if (!is_null($ori['CODDIR']))
                    $this->PGDES = $ori['CODDIR'];
            
                $this->DESTINO = adec($ori['NOMBRE']);
                $this->DIRDES1 = adec($ori['DIRECCION1']);
                $this->DIRDES2 = adec($ori['DIRECCION2']);
                $this->CPDES = $ori['CODPOS'];
                $this->POBLADES = $ori['LOCALIDAD'];
                $this->PROVIDES = $ori['PROVINCIA'];
                $this->NOMPAIDES = $ori['PAIS'];
                $this->ZONDES = $ori['ZONA'];
                $this->REFDES = $ori['REFERENCIA'];
                $this->CONDES = $ori['CONTACTO'];
                $this->TELDES = $ori['TELEFONO'];
                $this->MOVDES = $ori['MOVIL'];
                break;
            case "DESTINO2":
                $this->DESTINO = adec($ori['destino']);
                $this->DIRDES1 = adec($ori['direccion']);
                $this->DIRDES2 = '';
                $this->CPDES = $ori['comuna_id'].'0000';
                $this->POBLADES = $ori['comuna'];
                $this->PROVIDES = $ori['comuna'];
                $this->NOMPAIDES = 'CHILE';
                $this->ZONDES = $ori['comuna_id'];
                $this->REFDES = '';
                $this->CONDES = '';
                $this->TELDES = '';
                $this->MOVDES = '';
                break;
        }
    }

    function insert(){

        $this->DIRORI1 = substr($this->DIRORI1,0,40);
        $this->DIRDES1 = substr($this->DIRDES1,0,40);

        $sql = "INSERT INTO PEDEXT(
                                    TIPO,NIFCLI,NIFCON,NIFREM,
                                    NIFDES,NOMCOM,FECHA,FECLIM,
                                    REFERENCIA,NUMPLA,CMR,CRT,
                                    DTMI,NOMTIPMER,NOMCAT,NOMCLA,
                                    CODPRY,CODRUT,PGORI,ORIGEN,
                                    DIRORI1,DIRORI2,CPORI,POBLAORI,
                                    PROVIORI,NOMPAIORI,ZONORI,FECHORORI,
                                    HASFECHORORI,REFORI,CONORI,TELORI,
                                    MOVORI,PGDES,DESTINO,DIRDES1,
                                    DIRDES2,CPDES,POBLADES,PROVIDES,
                                    NOMPAIDES,ZONDES,FECHORDES,HASFECHORDES,
                                    REFDES,CONDES,TELDES,MOVDES,
                                    NOMMER,PESO,NOMENV,ENVASES,
                                    UNIDADES,VOLUMEN,METROS,NUMDOC,
                                    DOCUMENTOS,REQMAT,NOMTIPMAT,TIPFAC,
                                    CANTIDAD,PREUNI,VALMER,TELEFONO,
                                    DESCRIPCION,CLAVE1,OBSERVACIONES,PROCESANDO,
                                    PROCESADO,CODPDC,BORRADO,ERROR,
                                    NOTASERROR)
                VALUES('".$this->TIPO."','".$this->NIFCLI."','".$this->NIFCON."','".$this->NIFREM."',
                                    '".$this->NIFDES."','".$this->NOMCOM."','".$this->FECHA."','".$this->FECLIM."',
                                    '".$this->REFERENCIA."','".$this->NUMPLA."','".$this->CMR."','".$this->CRT."',
                                    '".$this->DTMI."','".$this->NOMTIPMER."','".$this->NOMCAT."','".$this->NOMCLA."',
                                    '".$this->CODPRY."','".$this->CODRUT."',".$this->PGORI.",'".$this->ORIGEN."',
                                    '".$this->DIRORI1."','".$this->DIRORI2."','".$this->CPORI."','".$this->POBLAORI."',
                                    '".$this->PROVIORI."','".$this->NOMPAIORI."','".$this->ZONORI."','".$this->FECHORORI."',
                                    '".$this->HASFECHORORI."','".$this->REFORI."','".$this->CONORI."','".$this->TELORI."',
                                    '".$this->MOVORI."',".$this->PGDES.",'".$this->DESTINO."','".$this->DIRDES1."',
                                    '".$this->DIRDES2."','".$this->CPDES."','".$this->POBLADES."','".$this->PROVIDES."',
                                    '".$this->NOMPAIDES."','".$this->ZONDES."','".$this->FECHORDES."','".$this->HASFECHORDES."',
                                    '".$this->REFDES."','".$this->CONDES."','".$this->TELDES."','".$this->MOVDES."',
                                    '".$this->NOMMER."',".$this->PESO.",'".$this->NOMENV."',".$this->ENVASES.",
                                    ".$this->UNIDADES.",".$this->VOLUMEN.",".$this->METROS.",".$this->NUMDOC.",
                                    '".$this->DOCUMENTOS."','".$this->REQMAT."','".$this->NOMTIPMAT."','".$this->TIPFAC."',
                                    ".$this->CANTIDAD.",".$this->PREUNI.",".$this->VALMER.",'".$this->TELEFONO."',
                                    '".$this->DESCRIPCION."','".$this->CLAVE1."','".$this->OBSERVACIONES."',".$this->PROCESANDO.",
                                    ".$this->PROCESADO.",".$this->CODPDC.",".$this->BORRADO.",".$this->ERROR.",
                                    '".$this->NOTASERROR."')";
            
            

            return $sql;
    }

    function post($conn){
        $sql = $pedext->insert();
        $stmt = $conn->query($sql);
    }

    function dias_despacho($conn,$fecha,$row){

        $sql_calendario = "SELECT COMUNAS_AU.CODCOM, RUTAS.DIATRA, ZONAS.L, ZONAS.M, ZONAS.X, ZONAS.J, ZONAS.V FROM COMUNAS_AU
                                    INNER JOIN  RUTAS ON RUTAS.CODIGO = COMUNAS_AU.CODRUT
                                    INNER JOIN ZONAS ON ZONAS.CODIGO = COMUNAS_AU.CODCOM
                                    WHERE COMUNAS_AU.CODCOM = '".$row['comuna_id']."'";
                
        $stmt = $conn->query($sql_calendario);
       
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $fecha_nueva = date('Y-m-d', strtotime($fecha. ' + '.($row['DIATRA']+1).' days'));
        $dia_sem_actual =date('N',strtotime($fecha_nueva));
        $dias = array(1 => $row['L'],2 => $row['M'],3 => $row['X'],4 => $row['J'],5 => $row['V']);
        $i = $dia_sem_actual;
        $j = 0;
    
        switch($i){
            case 6:
                $j = $j+2;
                $i = 1;
                break;
            case 7:
                $j++;
                $i = 1;
                break;
        }
        
        while(1){
            if ($dias[$i]){
                $fecha_nueva = date('Y-m-d', strtotime($fecha_nueva. ' + '.$j.' days'));
                break;
            }
            else{
                $j++;
                $i++;
            }
    
            if ($i == 6){
                $i = 1;
                $j = $j+2;
            }
            
            if ($j > 7){
                $fecha_nueva = date('Y-m-d', strtotime($fecha_nueva. ' + 15 days'));
                break;
            }
        }
    
        $this->FECHORDES = $fecha_nueva;
        $this->HASFECHORDES = $fecha_nueva;
    }
}
