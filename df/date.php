<?php

class dateDF{
    public $day;
    public $month;
    public $year;

    function __construct($fecha){
        $this->day = (int)date('d',$fecha);
        $this->month = (int)date('m',$fecha);
        $this->year = (int)date('Y',$fecha);
    }
}