<?php
    require_once('date.php');
    require_once('taxes.php');
    require_once('details.php');
    require_once('desc.php');
    require_once('attachment.php');
    require_once('analisis.php');
    require_once('storage.php');

    function eliminar_acentos($str){
        $str2 = str_replace(array("'",'°','Á','á','É','é','Í','í','Ó','ó','Ú','ú','Ñ','ñ'), array('','','A','a','E','e','I','i','O','o','U','u','N','n'),$str);
        return $str2;
    }

    class SaveSaleDF{
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

        function __construct($row){

            $datos = GetClient($row['clientFile']);
            
            $this->documentType = 'FVAELECT';
            $this->firstFolio = $row['CODIGO'];
            $this->emissionDate = new dateDF(strtotime($row['emissionDate']));
            $this->firstFeePaid = new dateDF(strtotime($row['firstFeePaid']));
            $this->externalDocumentID = "";
            $this->clientFile = $row['clientFile'];
            $this->contactIndex = eliminar_acentos($datos->clientList[0]->address);
            $this->paymentCondition = eliminar_acentos($row['paymentCondition']);
            $this->sellerFileId = "VENDEDOR";
            $this->clientAnalysis = new analisisDF();
            $this->clientAnalysis->number($row['CUEVEN']);
            $this->billingCoin = "PESO";
            $this->billingRate = 1.0000;
            $this->shopId = "Local";
            $this->priceList = "1";
            $this->giro = eliminar_acentos($datos->clientList[0]->business);
            $this->city = eliminar_acentos($datos->clientList[0]->city);
            $this->district = eliminar_acentos($datos->clientList[0]->district);

            $this->contact = -1;
            $this->storage = new storageDF();

        
            $query = "  SELECT	DOC_DETALLE.*, 
                                RUTAS.ORIGEN+'-'+RUTAS.DESTINO AS DESCRIPCION, 
                                'EMPNEG'+PROYECTOS.CUENTA AS AREANEG
                        FROM DOC_DETALLE 
                        INNER JOIN RUTAS ON RUTAS.CODIGO = DOC_DETALLE.CODRUT 
                        INNER JOIN PROYECTOS ON PROYECTOS.CODIGO = RUTAS.CODPRY
                        WHERE   DOC_DETALLE.SERIE = '".$row['documentType']."'  
                                AND DOC_DETALLE.ANO = ".$row['ANO']."  
                                AND DOC_DETALLE.CODIGO = ".$row['CODIGO'];
            $conn = connect();
            $stmt = $conn->query($query);

            /* Detalle del documento */

            while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
                $detalle = new detailsDF($row2['CODRUT'],'',$row2['PRECIO'],'3110101001',$row2['AREANEG']);
                array_push($this->details,$detalle);
            }


            /* Detalles Adicionales */
            
            $query = "  SELECT	* FROM DOC_DETALLE2
                        WHERE   DOC_DETALLE2.SERIE = '".$row['documentType']."'  
                                AND DOC_DETALLE2.ANNO = ".$row['ANO']."  
                                AND DOC_DETALLE2.CODIGO = ".$row['CODIGO'];

            $conn = connect();
            $stmt = $conn->query($query);

            /* Detalle del documento */

            while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
                $detalle = new detailsDF($row2['CODPRO'],$row2['DESCRIPCION'],$row2['PRECIO'],'3110101001',$row2['AREANEG']);
                array_push($this->details,$detalle);
            }

            /* Impuestos del documento */
            $impuestos = new taxesDF();
            $impuestos->iva($row['IVA']);
            array_push($this->saleTaxes,$impuestos);

            $this->gloss = $row['gloss'];
            $this->isTransferDocument = true;
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

            return $res;
        }
}

    ?>