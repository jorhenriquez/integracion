<?php

function get_providers($conn){
    $query = "SELECT * FROM TRANSPOR_PENDIENTES";
    $stmt = $conn->query($query);
    return $stmt;
}

class SaveProvider{
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
    public $tipo;
    public $codigo;

    function __construct($row){
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
        $this->tipo = $row['TIPO'];
        $this->codigo = $row['CODIGO'];
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

    function setError($conn,$message){
        /* Define el tipo de proveedor */
        switch($this->tipo){
            case 'T':
                $tipo = 'TRANSPOR';
                $tabla = 'CARTRA';
                $codigo = 'CODTRA';
                $codcar = 29;
                break;
            case 'P':
                $tipo = 'PROVEEDOR';
                $tabla = 'CARPRO';
                $codigo = 'CODPRO';
                $codcar = 0;
                break;
            case 'A':
                $tipo = 'ACREEDOR';
                $tabla = 'CARACR';
                $codigo = 'CODACR';
                $codcar = 0;
                break;
        }

        if (strpos($message, 'ya existe')){
            $query = "SELECT * FROM ".$tabla." WHERE CODCAR = ".$codcar." AND ".$codigo." = ".$this->codigo;
            $stmt2 = $conn->query($query);
            if ($stmt2->rowCount() == 0)
                $query = "INSERT INTO ".$tabla."(CODCAR,".$codigo.",VALOR) VALUES(".$codcar.",".$this->codigo.",1)";
            else    
                $query = "  UPDATE ".$tabla." SET VALOR = 1 WHERE CODCAR = ".$codcar." and ".$codigo." = ".$this->codigo;
        }
        else {
            $query = "UPDATE ".$tipo." SET OBSERVA = '".$message."' WHERE CODIGO = ".$this->codigo;
        }
        
        $stmt = $conn->query($query);

    }

    function setStatus($conn){
        switch($this->tipo){
            case 'T':
                $tipo = 'TRANSPOR';
                $tabla = 'CARTRA';
                $codigo = 'CODTRA';
                $codcar = 29;
                break;
            case 'P':
                $tipo = 'PROVEEDOR';
                $tabla = 'CARPRO';
                $codigo = 'CODPRO';
                $codcar = 0;
                break;
            case 'A':
                $tipo = 'ACREEDOR';
                $tabla = 'CARACR';
                $codigo = 'CODACR';
                $codcar = 0;
                break;
        }
        $query = "SELECT * FROM ".$tabla." WHERE CODCAR = ".$codcar." AND ".$codigo." = ".$this->codigo;
        $stmt = $conn->query($query);
        if ($stmt->rowCount() == 0)
            $query = "INSERT INTO ".$tabla."(CODCAR,".$codigo.",VALOR) VALUES(".$codcar.",".$this->codigo.",1)";
        else    
            $query = "  UPDATE ".$tabla." SET VALOR = 1 WHERE CODCAR = ".$codcar." and ".$codigo." = ".$this->codigo;
            
        $stmt = $conn->query($query);

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