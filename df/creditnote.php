<?php

class SaveCreditNoteDF{
    public $creditNoteTypeId;
    public $documentType;
    public $folio;
    public $externalDocumentID;
    public $gloss;
    public $emissionDate;
    public $details = array();
    public $isTransferDocument;


    function __construct($row){
        $this->creditNoteTypeId = 'NCVELECT';
        $this->documentType = 'FVAELECT';
        $this->folio = $row['CODIGO'];
        $this->externalDocumentID = '';
        $this->gloss = '';
        $this->emissionDate = new dateDF(strtotime($row['emissionDate']));
        $this->isTransferDocument = true;

        /* Detalles Adicionales */
            
        $query = "  SELECT	* FROM DOC_DETALLE2
                        WHERE   DOC_DETALLE2.SERIE = '".$row['documentType']."'  
                                AND DOC_DETALLE2.ANNO = ".$row['ANO']."  
                                AND DOC_DETALLE2.CODIGO = ".$row['CODIGO'];

            $conn = connect();
            $stmt = $conn->query($query);

            /* Detalle del documento */

            while($row2 = $stmt->fetch(PDO::FETCH_ASSOC)){
                $detalle = new detailsDF($row2['CODPRO'],$row2['DESCRIPCION'],-1*$row2['PRECIO'],'3110101001',$row2['AREANEG']);
                array_push($this->details,$detalle);
            }



    }

    function add_detail($det){
      array_push($this->details,$det);
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
}