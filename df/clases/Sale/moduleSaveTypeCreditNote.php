<?php

    function get_creditnote($conn){
        $query = "SELECT DOCCLI.SERIE, DOCCLI.ANO, DOCCLI.CODIGO, DOCCLI.CODCLI, DOCREC.NUMORI, DOCCLI.FECHA AS emissionDate from DOCCLI 
                INNER JOIN DOCREC ON DOCREC.SERDES = DOCCLI.SERIE AND DOCREC.ANODES = DOCCLI.ANO AND DOCREC.NUMDES = DOCCLI.CODIGO
                INNER JOIN ENVDOC ON ENVDOC.SERIE = DOCCLI.SERIE AND ENVDOC.ANO = DOCCLI.ANO AND ENVDOC.CODIGO = DOCCLI.CODIGO
                WHERE DOCCLI.SERIE = 'R' AND DOCCLI.ANO >= 2022 AND DOCCLI.BASE <> 0 AND (DOCCLI.CONTA = '' OR DOCCLI.CONTA IS NULL OR DOCCLI.CONTA = 'N')";
        $stmt = $conn->query($query);
        return $stmt;
    }
    
    class dateSaveTypeCreditNote{
        public $day;
        public $month;
        public $year;

        function __construct($fecha){
            $this->day = (int)date('d',$fecha);
            $this->month = (int)date('m',$fecha);
            $this->year = (int)date('Y',$fecha);
        }
    }

    class discountSaveTypeCreditNote{
        public $type;
        public $value;
    }

    class analysisSaveTypeCreditNote{
        public $accountNumber;
        public $businessCenter;
        public $classifier01;
        public $classifier02;
    }

    class detailsSaveTypeCreditNote{
          public $type;
          public $isExempt;
          public $code;
          public $count;
          public $comment;
          public $productName;
          public $productNameBarCode;
          public $price;
          public $discount;
          public $unit;
          public $analysis;
    }

    class customFieldsSaveTypeCreditNote{
        public $name;
        public $value;
    }

    class ventaRecDesGlobalSaveTypeCreditNote{
        public $amount;
        public $modifierClass;
        public $name;
        public $percentage;
        public $value;
    }

    class SaveTypeCreditNote{
        public $creditNoteTypeId;
        public $creditNoteType;
        public $folio;
        public $externalDocumentID;
        public $gloss;
        public $emmisionDate;
        public $details = array();
        public $modifiedGloss;
        public $isTransferDocument = false;
        public $customFields = array();
        public $ventaRecDesGlobal = array();

        function __construct($sol){
            $this->creditNoteTypeId = "NCVELECT";
            $this->creditNoteType = 2;
            $this->documentType = "FVAELECT";
            $this->folio =  $sol['NUMORI'];
            $this->emmisionDate = new dateSaveTypeCreditNote(strtotime($sol['emissionDate']));
            $this->modifiedGloss = '';
        }
        
        function post(){
            $json_url = 'https://api.defontana.com/api/Sale/SaveTypeCreditNote';
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
        
          /* Detalle de las cargas asociadas a la factura */
          $query = "SELECT DOCCLI.SERIE, DOCCLI.ANO, DOCCLI.CODIGO, DOCCLI.CODCLI, CARGA.CODRUT, -SUM(LIDOCL.PRECIO) AS PRECIO, DOCREC.NUMORI from LIDOCL 
          inner join CARGA ON CARGA.CODIGO = LIDOCL.CARREC
          INNER JOIN DOCREC ON DOCREC.SERDES = LIDOCL.SERIE AND DOCREC.ANODES = LIDOCL.ANO AND DOCREC.NUMDES = LIDOCL.CODIGO
          INNER JOIN DOCCLI ON DOCCLI.SERIE = LIDOCL.SERIE AND DOCCLI.ANO = LIDOCL.ANO AND DOCCLI.CODIGO = LIDOCL.CODIGO
          INNER JOIN ENVDOC ON ENVDOC.SERIE = DOCCLI.SERIE AND ENVDOC.ANO = DOCCLI.ANO AND ENVDOC.CODIGO = DOCCLI.CODIGO
          WHERE LIDOCL.PRECIO <> 0 AND DOCREC.NUMORI = ".$this->folio."
          GROUP BY CARGA.CODRUT, DOCCLI.CODCLI, DOCCLI.SERIE, DOCCLI.ANO, DOCCLI.CODIGO, DOCREC.NUMORI";

          $stmt = $conn->query($query);
          $sum = 0;
          /* Se ingresa el detalle a la factura */
          while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
              $detalle = new detailsSaveTypeCreditNote($row2);
              array_push($this->details,$detalle);
          }
              $fWrite = fopen($this->numero.".txt","w"); 
              $log = "Valor factura: ".$this->base." - Valor detalle: ".$sum.PHP_EOL;
              $wrote = fwrite($fWrite, $log);   
              return false;
          }
      }
    }
}
      