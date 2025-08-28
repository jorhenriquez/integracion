<?php

    function get_document($conn){
        /* Obtiene el detalle de los documentos pendientes por subir */
        $query = "SELECT * FROM DOC_PENDIENTES WHERE documentType = 'F'";
        $stmt = $conn->query($query);
        return $stmt;}
    class dateSaveSale{
        public $day;
        public $month;
        public $year;

        function __construct($fecha){
            $this->day = (int)date('d',$fecha);
            $this->month = (int)date('m',$fecha);
            $this->year = (int)date('Y',$fecha);
        }}

    class attachedDocumentsSaveSale{
        public $date;
        public $documentTypeId;
        public $folio;
        public $reason;

        function __construct($fecha,$tipo,$folio,$razon){
            $this->date = new dateSaveSale(strtotime($fecha));
            $this->documentTypeId = $tipo;
            $this->folio = $folio;
            $this->reason = $razon;
        }
    }
    class analisisSaveSale{
        public $accountNumber;
        public $businessCenter;
        public $classifier01;
        public $classifier02;

        function __construct(){
            $this->accountNumber = "";
            $this->businessCenter = "";
            $this->classifier01 = "";
            $this->classifier02 = "";
        }

        public function number($numero){
            $this->accountNumber = $numero;
        }

        public function business($negocio){
            $this->businessCenter = $negocio;
        }}

    class storageSaveSale{
        public $code;
        public $motive;
        public $storageAnalysis;
    
        function __construct(){
            $this->code = "";
            $this->motive = "";
            $this->storageAnalysis = new analisisSaveSale();
        }}
    
    class detailsSaveSale{
        public $type;
        public $code;
        public $count;
        public $productName;
        public $productNameBarCode;
        public $price;
        public $unit;
        public $analysis;

        function __construct($codigo,$servicio,$precio,$cuenta,$centroneg){
            $this->type = "S";
            $this->code = $codigo;
            $this->count = 1;
            $this->productName = $servicio;
            $this->productNameBarCode = "";
            $this->price = $precio;
            $this->unit = "UN";
            $this->analysis = new analisisSaveSale();
            $this->analysis->number($cuenta);
            $this->analysis->business($centroneg);

        }}
    class taxesDF{
        public $code;
        public $value;
        public $taxeAnalysis;

        function __construct(){
            $this->code = "";
            $this->value = 0;
            $this->taxeAnalysis = new analisisSaveSale();
        }
        
        public function iva($valor){
            $this->code = "IVA";
            $this->value = (double)$valor;
            $this->taxeAnalysis->number("2120301001");
        }}
    class SaveSale{
        public $documentType;
        public $firstFolio = 0;
        public $lastFolio = 0;
        public $externalDocumentID;
        public $emissionDate;
        public $firstFeePaid;
        public $clientFile;
        public $contactIndex;
        public $paymentCondition;
        public $sellerFileId;
        public $clientAnalysis;
        public $saleAnalysis;
        public $billingCoin;
        public $billingRate;
        public $shopId;
        public $priceList;
        public $giro;
        public $district;
        public $city;
        public $contact;
        public $attachedDocuments = array();
        public $storage;
        public $details = array();
        public $saleTaxes = array();
        public $ventaRecDesGlobal = array();
        public $customFields = array();
        public $gloss;
        public $isTransferDocument;
        public $serie;
        public $ano;
        public $numero;
        public $base;

        function __construct($sol){

            $datos = GetClients($sol['clientFile']);

            $this->serie = $sol['documentType'];
            $this->ano = $sol['ANO'];
            $this->numero = $sol['CODIGO'];
            $this->base = $sol['BASE'];
            $this->documentType = 'FVAELECT';
            $this->firstFolio = $sol['CODIGO'];
            $this->emissionDate = new dateSaveSale(strtotime($sol['emissionDate']));
            $this->firstFeePaid = new dateSaveSale(strtotime($sol['firstFeePaid']));
            $this->externalDocumentID = "";
            $this->clientFile = $sol['clientFile'];
            $this->contactIndex = eliminar_acentos($datos->clientList[0]->address);
            $this->paymentCondition = eliminar_acentos($sol['paymentCondition']);
            $this->sellerFileId = "VENDEDOR";
            $this->clientAnalysis = new analisisSaveSale();
            $this->clientAnalysis->number($sol['CUEVEN']);
            $this->billingCoin = "PESO";
            $this->billingRate = 1.0000;
            $this->shopId = "Local";
            $this->priceList = "1";
            $this->giro = eliminar_acentos($datos->clientList[0]->business);
            $this->city = eliminar_acentos($datos->clientList[0]->city);
            $this->district = eliminar_acentos($datos->clientList[0]->district);
            $this->isTransferDocument = false; 
            $this->contact = -1;
            $this->storage = new storageSaveSale();
            /* Ingreso de IVA */
            $impuestos = new taxesDF();
            $impuestos->iva($sol['IVA']);
            array_push($this->saleTaxes,$impuestos);

            
            if (isset($sol['folioOC'])){

                $oc = new attachedDocumentsSaveSale($sol['fechaOC'],'801',$sol['folioOC'],$sol['motivoOC']);
                array_push($this->attachedDocuments,$oc);
            }
            

        }

        function sumDetail(){
            $query = "  SELECT	DOC_DETALLE.*, 
                                RUTAS.ORIGEN+'-'+RUTAS.DESTINO AS DESCRIPCION, 
                                'EMPNEG'+PROYECTOS.CUENTA AS AREANEG
                        FROM DOC_DETALLE 
                        INNER JOIN RUTAS ON RUTAS.CODIGO = DOC_DETALLE.CODRUT 
                        INNER JOIN PROYECTOS ON PROYECTOS.CODIGO = RUTAS.CODPRY
                        WHERE   DOC_DETALLE.SERIE = '".$this->serie."'  
                                AND DOC_DETALLE.ANO = ".$this->ano."  
                                AND DOC_DETALLE.CODIGO = ".$this->numero;
            $sum = 0;
            $stmt = $conn->query($query);}
        
            function setError($conn,$error){
            switch($error){
            case ERROR_NO_EXISTE_CLIENTE:
                $err = 'No existe el cliente';
                break;
            case ERROR_DETALLE_DISTINTO_DE_FACTURA:
                $err = 'No cuadra detalle con valor de la factura';
                break;
            default:
                break;
            }
            $query = " SET NOCOUNT ON;
                    UPDATE DOCCLI 
                    SET CONTA = 'N',MEMO ='".$err."'    
                    WHERE   SERIE = '".$this->serie."'  
                    AND ANO = ".$this->ano."  
                    AND CODIGO = ".$this->numero;
            $stmt = $conn->query($query);
        }
        function setStatus($conn,$status,$message){
            switch($status){
                case OK:
                $query = "  SET NOCOUNT ON;
                            UPDATE DOCCLI 
                            SET CONTA = 'S', MEMO =''  
                            WHERE   SERIE = '".$this->serie."'  
                            AND ANO = ".$this->ano."  
                            AND CODIGO = ".$this->numero;
                break;     
                case NOT_OK:
                $query = " SET  NOCOUNT ON;
                                UPDATE DOCCLI 
                                SET CONTA = 'N',MEMO ='".str_replace('\'','',$message)."'    
                                WHERE   SERIE = '".$this->serie."'  
                                AND ANO = ".$this->ano."  
                                AND CODIGO = ".$this->numero; 
                $fWrite = fopen($this->numero.".txt","w"); 
                $log = json_encode($this).PHP_EOL;
                $wrote = fwrite($fWrite, $log);
                break;

            }
		echo $query."<br><br>";
            $stmt = $conn->query($query);
        }

        
        function get_detail($conn){
        
            /* Detalle de las cargas asociadas a la factura */
            $query = "  SELECT	DOC_DETALLE.*, 
                            RUTAS.ORIGEN+'-'+RUTAS.DESTINO AS DESCRIPCION, 
                            'EMPNEG'+PROYECTOS.CUENTA AS AREANEG
                        FROM DOC_DETALLE 
                        INNER JOIN RUTAS ON RUTAS.CODIGO = DOC_DETALLE.CODRUT 
                        INNER JOIN PROYECTOS ON PROYECTOS.CODIGO = RUTAS.CODPRY
                        WHERE   DOC_DETALLE.SERIE = '".$this->serie."'  
                            AND DOC_DETALLE.ANO = ".$this->ano."  
                            AND DOC_DETALLE.CODIGO = ".$this->numero;

            $stmt = $conn->query($query);
            $sum = 0;
            /* Se ingresa el detalle a la factura */
            while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
                $detalle = new detailsSaveSale($row2['CODRUT'],'',$row2['PRECIO'],'3110101001',$row2['AREANEG']);
                $sum = $sum + $detalle->price;
                array_push($this->details,$detalle);
            }

            /* Detalle de los productos asociados a la factura */
            $query = "  SELECT	* 
                        FROM DOC_DETALLE2
                        WHERE   DOC_DETALLE2.SERIE = '".$this->serie."'  
                            AND DOC_DETALLE2.ANNO = ".$this->ano."  
                            AND DOC_DETALLE2.CODIGO = ".$this->numero;
            
            $stmt = $conn->query($query);

            /* Se ingresa el detalle a la factura */
            while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
                $detalle = new detailsSaveSale($row2['CODPRO'],$row2['DESCRIPCION'],$row2['PRECIO'],'3110101001',$row2['AREANEG']);
                $sum = $sum + $detalle->price;
                array_push($this->details,$detalle);
            }
            /* Retorna el valor total de la suma de los producto */    
            if ($this->base == ceil($sum) || $this->base == floor($sum))
                return true;
            else {
                $fWrite = fopen($this->numero.".txt","w"); 
                $log = "Valor factura: ".$this->base." - Valor detalle: ".$sum.PHP_EOL;
                $wrote = fwrite($fWrite, $log);   
                return false;
            }
        }

        function post(){
            $json_url = 'https://api.defontana.com/api/Sale/SaveSale';
            $data = json_encode($this);

            // Initializing curl
            $ch = curl_init($json_url);

            // Configuring curl options
        
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type: application/json','Authorization: bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1laWQiOiJBRDEyM0ZULUhHREY1Ni1LSTIzS0wtS0pUUDk4NzYtSEdUMTIiLCJ1bmlxdWVfbmFtZSI6ImNsaWVudC5sZWdhY3lAZGVmb250YW5hLmNvbSIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vYWNjZXNzY29udHJvbHNlcnZpY2UvMjAxMC8wNy9jbGFpbXMvaWRlbnRpdHlwcm92aWRlciI6IkFTUC5ORVQgSWRlbnRpdHkiLCJBc3BOZXQuSWRlbnRpdHkuU2VjdXJpdHlTdGFtcCI6IkdIVEQyMzQtS0xISjc4NjgtRkc0OTIzLUhKRzA4RlQ1NiIsImNvbXBhbnkiOiIyMDIxMDgxMzIwNTMyOTA1MDAwNCIsImNsaWVudCI6IjIwMjEwODEzMjA1MzI5MDUwMDA0Iiwib2xkc2VydmljZSI6InNpbHZlciIsInVzZXIiOiJnZXJlbmNpYSIsInNlc3Npb24iOiIxNjMyNzc0OTA5Iiwic2VydmljZSI6InNpbHZlciIsImNvdW50cnkiOiJDTCIsImNvbXBhbnlfbmFtZSI6IlRyYW5zcG9ydGVzIFN1cGVydHJhbnMgTHRkYSIsInVzZXJfbmFtZSI6IkpvcmdlIEhlbnLDrXF1ZXogTXXDsW96Iiwicm9sZXNQb3MiOiJbXCJ1c3VhcmlvXCIsXCJ1c3VhcmlvZXJwXCJdIiwicnV0X3VzdWFyaW8iOiIxNi4yODMuODkxLTEiLCJpc3MiOiJodHRwczovLyouZGVmb250YW5hLmNvbSIsImF1ZCI6IjA5OTE1M2MyNjI1MTQ5YmM4ZWNiM2U4NWUwM2YwMDIyIiwiZXhwIjoyMDExMzgwMjg2LCJuYmYiOjE2MzI3NzU0ODZ9.PTHvSm-iG82DiYmQYFjFTuqYHvAicWfhZHCmxV12NwE'));
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);

            // Getting results
            $result = curl_exec($ch); // Getting jSON result string
            $res = array();
            $res = json_decode($result);

            return $res;}}
  