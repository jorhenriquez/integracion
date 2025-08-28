<?php
    require_once('detailsOC.php');

    class providerSaveDF{
        public $legalCode;
        public $name;
        public $address;
        public $district;
        public $email;
        public $business;
        public $rubroId;
        public $giro;
        public $city;
        public $phone;

        function __construct(){}

        function crear($row){
            $this->legalCode = str_replace(" ","",$row['legalCode']);
            $this->name = $row['name'];
            $this->address = $row['address'];
            $this->district = $row['district'];
            $this->email = $row['email'];
            $this->business = $row['business'];
            $this->rubroId = "1";
            $this->giro = $row['giro'];
            $this->city = $row['city'];
            $this->phone = $row['phone'];
        }

        function post(){
            $json_url = 'https://api.defontana.com/api/PurchaseOrder/SaveProvider';
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

        function GetProvider($rut){
            $json_url = 'https://api.defontana.com/api/PurchaseOrder/GetProviders?';
         
            $data = array( 'status'=>0,
                           'legalcode'=>$rut,
                           'itemsPerPage'=>10,
                           'pageNumber'=>0);
         
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

            if ($res->totalItems >= 1) {
                $this->legalCode = $res->providersList[0]->legalCode;
                $this->name = $res->providersList[0]->name;
                $this->address = $res->providersList[0]->address;
                $this->district = $res->providersList[0]->district;
                $this->email = $res->providersList[0]->email;
                $this->business = $res->providersList[0]->business;
                $this->rubroId = $res->providersList[0]->rubroId;
                $this->city = $res->providersList[0]->city;
                $this->phone = $res->providersList[0]->phone;
                return true;
            }
            else   
                return false;

        }
    }

    class OrdenDeCompraDF{
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
            function __construct(){}

            function add_provider($rut){
                $provider = New providerSaveDF;
                if($provider->GetProvider($rut))
                    $this->providerData = clone $provider;
            }

            function crear($row){
                $this->providerID = $row['rut'];
                $this->serie = "";
                $this->number = 0;
                $this->coinId = "PESO";
                $this->paymentCondition = $row['paymentCondition'];
                $this->amountBeforeTaxes = intval($row['amountBeforeTaxes']);
                $this->taxes = intval($row['taxes']);
                $this->amountTotal = intval($row['amountTotal']);
                $this->emissionDate = $row['emmisionDate'];
                $this->expirationDate = $row['expirationDate'];
                $this->receiptDate = $row['receiptDate'];


                $query = "  SELECT OCTRANSPOR_DETALLE.*,
                                'EMPNEG'+PROYECTOS.CUENTA AS AREANEG 
                            FROM OCTRANSPOR_DETALLE 
                            INNER JOIN PROYECTOS ON PROYECTOS.CODIGO = OCTRANSPOR_DETALLE.CODPRY
                            WHERE
                                OCTRANSPOR_DETALLE.SERIE = '".$row['SERIE']."' AND
                                OCTRANSPOR_DETALLE.ANO = ".$row['ANO']." and
                                OCTRANSPOR_DETALLE.CODIGO = ".$row['number'];

                
                $conn = connect();
                $stmt = $conn->query($query);
                $neto = 0;
                $iva = 0;
                $total = 0;
                while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $detalle = new detailsocDF(true,$row2['CODPRY'],$row2['PRECIO'],0);
                    $this->businessCenter = $row2['AREANEG'];
                    $neto = $row2['PRECIO'] + $neto;
                    $iva = $row2['IVA'] + $iva;
                    array_push($this->details,$detalle);
                }

                $this->amountBeforeTaxes = $neto;
                $this->taxes = $iva;
                $this->amountTotal = $neto+$iva;

                if(isset($res->success)){
                    if($res->success){
                    $query = "UPDATE DOCTRA SET REFEXTERNA = '1' WHERE
                                SERIE = '". $row['SERIE']."' AND
                                ANO = ".$row['ANO']." AND
                                CODIGO = ".$row['number'];
                    $conn->query($query);
                }}

                print_r(json_encode($this));
                echo "<br><br>";

            /* Detalle del documento */
            }
    }
    ?>