<?php

/* Credit Card Class */
class CreditCard {

    function __construct($name, $expMonth, $expYear, $number, $cvc, $address) {
      $this->name = $name;
      $this->expMonth = $expMonth;
      $this->expYear = $expYear;
      $this->address = $address;
      $this->cvc = $cvc;
      $this->number = $number;
      $this->expiration = $expMonth."/".$expYear;
      
      $this->validate();
    }

    function validate($element = "all") {
      
      $_errors = array();
    
      if (!preg_match('/^\d{2}\/\d{4}$/', $this->expiration)) {
        $_errors[] = 'CreditCard - Validation - Expiration Date (invalid, must be in format mm/yyyy) (' . $this->expiration . ')';
      }
      
      if(!preg_match('/^\d{3,4}$/', $this->cvc)) {
        $_errors[] = 'CreditCard - Validation - CVC (invalid) (' . $this->cvc . ')';
      }
      
      if (!CreditCard::LuhnTest($number)) {
        $_errors[] = 'CreditCard - Validation - Number (invalid) (' . $this->number . ')';
      }
      
      try {
          $this->address->validate();
      } catch (OrdrinExceptionBadValue $ex) {
          $_errors[] = $ex.__toString();
      }
      
      if(!empty($_errors)) {
        throw new OrdrinExceptionBadValue($_errors);
      }
    }

    public static function LuhnTest($number) {
      $number1 = substr($numebr, 0, strlen($number) - 1);
      
      $sum = 0;
      for($i = 0; $i < strlen($number1); $i++) {
        $sum += intval(substr($number1, $i, $i + 1));
      }
      
      $delta = array(0,1,2,3,4,-4,-3,-2,-1,0);
      for($i = strlen($number1) - 1; $i >= 0; $i-=2) {
        $sum += $delta[intval(substr($number1, $i, $i + 1))];
      }
      
      $mod10 = 10 - sum % 10;
      if($mod10 == 10) {
        $mod10 = 0;
      }
      
      if ($mod10 == intval(substr($number, strlen($number) - 1, strlen($number)))) {
        return true;
      } 
      return false;
    }
    
    function __set($name, $value) {
        $this->$name = $value;
    }

    function __get($name) {
        return $this->$name;
    }
}

?>