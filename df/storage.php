<?php
    
    class storageDF{
        public $code;
        public $motive;
        public $storageAnalysis;

        function __construct(){
            $this->code = "";
            $this->motive = "";
            $this->storageAnalysis = new analisisDF();
        }
    }