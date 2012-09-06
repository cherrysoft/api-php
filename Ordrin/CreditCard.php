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

    function validate($errors = null) {
    	if(!$errors) $errors = array();
    	$validation = new Validation($errors);
    	$validation->validate('expirationDate',$this->expiration);
    	$validation->validate('CVC',$this->cvc);
    	$validation->validate('cardNumber',$this->$number);
    	try {
    		$this->address->validate();
    	} catch (OrdrinExceptionBadValue $ex) {
    		$errors[] = $ex.__toString();
    	}
      if(!empty($errors)) {
        throw new OrdrinExceptionBadValue($validation->getErrors());
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