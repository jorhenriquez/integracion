<?php

    class analisisDF{
        public $accountNumber;
        public $businessCenter;
        public $classifier01;
        public $classifier02;

        function __construct(){
            $this->accountNumber = "";
            $this->businessCenter = "";
            $this->classifier01 = "";
            $this->classifier02 = "";
        }

        public function number($numero){
            $this->accountNumber = $numero;
        }

        public function business($negocio){
            $this->businessCenter = $negocio;
        }
    }