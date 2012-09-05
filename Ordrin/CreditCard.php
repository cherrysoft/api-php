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
    	$validation = new Validation();
    	$validation->validateExpirationDate($this->expiration);
    	$validation->validateCVC($this->cvc);
    	$validation->validateCardNumber($this->$number);
    	$_errors = $validation -> errors;
    	try {
    		$this->address->validate();
    	} catch (OrdrinExceptionBadValue $ex) {
    		$_errors[] = $ex.__toString();
    	}
      if(!empty($_errors)) {
        throw new OrdrinExceptionBadValue($_errors);
      }
    }
    
    function __set($name, $value) {
        $this->$name = $value;
    }

    function __get($name) {
        return $this->$name;
    }
}

?>