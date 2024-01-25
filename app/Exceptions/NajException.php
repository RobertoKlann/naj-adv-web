<?php

namespace App\Exceptions;

use Exception;

class NajException extends Exception { 
    
    /**
     * Constructor of NajException
     * @param string  $message Menssagem
     * @param int     $code    Código
     */
    function __construct($message, $code = 400) {
        parent::__construct($message, $code);
    }

    public function setCode($code){
        $this->code = $code;
    }
    
    public function setMessage($message){
        $this->message = $message;
    }
}
