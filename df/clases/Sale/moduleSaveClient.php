<?php

    function get_clients($conn){
        $query = "SELECT * FROM CLIENTES_PENDIENTES";
        $stmt = $conn->query($query);
        return $stmt;
    }

class SaveClient{
    public $legalCode;
    public $name;
    public $address;
    public $district;
    public $email;
    public $business;
    public $rubroId;
    public $giro;
    public $city;
    
    function crear($row){
        $this->legalCode = $row['legalCode'];
        $this->name = $row['name'];
        $this->address = $row['address'];
        $this->district = $row['district'];
        $this->email = $row['email'];
        $this->business = $row['business'];
        $this->rubroId = "1";
        $this->giro = $row['giro'];
        $this->city = $row['city'];
        $this->codigo = $row['CODTRA'];
    }

    function post(){
        $json_url = 'https://api.defontana.com/api/Sale/SaveClient';
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
        if (strpos($message, 'ya existe')){
            $query = "SELECT * FROM CARCLI WHERE CODCAR = 28 AND CODCLI = ".$this->codigo;
            $stmt = $conn->query($query);
            if ($stmt->rowCount() == 0)
                $query = "INSERT INTO CARCLI(CODCAR,CODCLI,VALOR) VALUES(28,".$this->codigo.",1)";
            else    
                $query = "  UPDATE CARCLI SET VALOR = 1 WHERE CODCAR = 28 and CODCLI = ".$this->codigo;
            $stmt = $conn->query($query);
        }
    }

    function setStatus($conn){
        $query = "SELECT * FROM CARCLI WHERE CODCAR = 28 AND CODCLI = ".$this->codigo;
        $stmt2 = $conn->query($query);
        if ($stmt2->rowCount() == 0)
            $query = "INSERT INTO CARCLI(CODCAR,CODCLI,VALOR) VALUES(28,".$this->codigo.",1)";
        else    
            $query = "  UPDATE CARCLI SET VALOR = 1 WHERE CODCAR = 28 and CODCLI = ".$this->codigo;
        $stmt3 = $conn->query($query);
        
    }

}