<?php

    class taxesDF{
        public $code;
        public $value;
        public $taxeAnalysis;

        function __construct(){
            $this->code = "";
            $this->value = 0;
            $this->taxeAnalysis = new analisisDF();
        }
        
        public function iva($valor){
            $this->code = "IVA";
            $this->value = (double)$valor;
            $this->taxeAnalysis->number("2120301001");
        }
    }