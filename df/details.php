<?php


    class detailsDF{
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
            $this->analysis = new analisisDF();
            $this->analysis->number($cuenta);
            $this->analysis->business($centroneg);

        }
    }
