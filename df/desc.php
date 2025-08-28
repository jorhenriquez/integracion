<?php

    class descDF{
        public $amount;
        public $modifierClass;
        public $name;
        public $percentage;
        public $value;

        function __construct(){
            $this->amount = 0;
            $this->modifierClass = "PV";
            $this->name = "DESCUENTO";
            $this->percentage = 0;
            $this->value = 0;
        }
    }