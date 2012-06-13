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
    }

    function validate($element = "all") {
    }

    function __set($name, $value) {
        $this->$name = $value;
    }

    function __get($name) {
        return $this->$name;
    }
}
