<?php


    class detailsOCDF{
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