<?php

    function get_purchase_orders($conn){
        $query = "SELECT * FROM OCTRANSPOR_PENDIENTES";
        $stmt = $conn->query($query);
        return $stmt;
    }


    class detailsInsertPurchaseOrder{
        public $isService;
        public $productID;
        public $quantity;
        public $total;
        public $discount;
        public $discountType;
        public $price;
        public $comment;

        function __construct($servicio,$codigo,$precio,$desc){
            $this->isService = $servicio;
            $this->productID = $codigo;
            $this->quantity = 1;
            $this->total = $precio;
            $this->discount = 0;
            $this->discountType = 0;
            $this->price = $precio;
            $this->comment = "UN";
    }
}
    class InsertPurchaseOrder{
        public $providerID;
        public $serie;
        public $number = 0;
        public $businessCenter;
        public $coinId = "PESO";
        public $paymentCondition;
        public $exchangeRate = 0;
        public $receiptDate;
        public $expirationDate;
        public $emissionDate;
        public $amountBeforeTaxes = 0;
        public $modifiers = 0;
        public $amountExempt = 0;
        public $amountTotal = 0;
        public $taxes = 0;
        public $details = array(); 
        public $dispatchContact;
        public $dispatchAddress;
        public $dispatchDistrict;
        public $dispatchState;
        public $dispatchCity;
        public $dispatchCountry;
        public $comment;
        public $serieN;
        public $ano;
        public $codigo;
        public $tipo;


        function create($row){
            $this->providerID = $row['rut'];
            $this->serie = $row['SERIEDF'];
            $this->number = intval($row['COD']);
            $this->coinId = "PESO";
            $this->paymentCondition = $row['paymentCondition'];
            $this->amountBeforeTaxes = intval($row['amountBeforeTaxes']);
            $this->taxes = intval($row['taxes']);
            $this->amountTotal = intval($row['amountTotal']);
            $this->emissionDate = $row['emmisionDate'];
            $this->expirationDate = $row['expirationDate'];
            $this->receiptDate = $row['receiptDate'];
            $this->serieN = $row['SERIE'];
            $this->ano = $row['ANO'];
            $this->codigo = $row['number'];
            $this->tipo=$row['TIPO'];
    }
        function post(){
            $json_url = 'https://api.defontana.com/api/PurchaseOrder/InsertPurchaseOrder';
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

        function get_detail($conn){
                
            $query = "  SELECT  OCTRANSPOR_DETALLE.*,
                                'EMPNEG'+PROYECTOS.CUENTA AS AREANEG 
                        FROM OCTRANSPOR_DETALLE 
                        INNER JOIN PROYECTOS ON PROYECTOS.CODIGO = OCTRANSPOR_DETALLE.CODPRY
                        WHERE
                            OCTRANSPOR_DETALLE.SERIE = '".$this->serieN."' AND
                            OCTRANSPOR_DETALLE.ANO = ".$this->ano." and
                            OCTRANSPOR_DETALLE.CODIGO = ".$this->codigo;
            
            $stmt = $conn->query($query);
            $sum = 0;
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $detail = New detailsInsertPurchaseOrder(true,$row['CODPRY'],intval($row['PRECIO']),0);
                $this->businessCenter = $row['AREANEG'];
                $sum += $detail->price;
                array_push($this->details,$detail);
            }

            $this->amountBeforeTaxes = $sum;
            $this->amountTotal = 1.19*$sum;
            $this->taxes = 0.19*$sum;
            return true;
            /*
            if ($this->amountBeforeTaxes == ceil($sum) || $this->amountBeforeTaxes == floor($sum))
                return true;
            else    
                return false;
                */
        }
            
        function setStatus($conn,$status,$message){
            switch($status){
                case OK:{
                    switch($this->tipo){
                        case 'T':
                            $query = "SET NOCOUNT ON; UPDATE DOCTRA SET REFEXTERNA = '".$this->number."' WHERE   SERIE = '".$this->serieN."' AND ANO = ".$this->ano." AND CODIGO = ".$this->codigo;
                            break;
                    }   } 
                    break;
                case NOT_OK:
                    switch($this->tipo){
                        case 'T':
                            $query = "SET NOCOUNT    ON; UPDATE DOCTRA SET MEMO ='".$message."' WHERE   SERIE = '".$this->serieN."' AND ANO = ".$this->ano." AND CODIGO = ".$this->codigo;
                            break;
                    }
                    break;
            }

            $stmt = $conn->query($query);
        }
}