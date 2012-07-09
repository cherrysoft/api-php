<?php

/* Address Class */
class Address {
    function __construct($street, $city, $state, $zip, $phone, $street2=null) {
        $this->street = $street;
        $this->city = $city;
        $this->zip = $zip;
        $this->street2 = $street2;
        $this->state = $state;
        $this->phone = $phone;
        
        $this->validate();
    }

    function validate($element = "all") {
      $_errors = array();

      // do ALL validation
      if (!preg_match('/(^\d{5}$)|(^\d{5}\-\d{4}$)/', $this->zip)) {
        $_errors[] = 'Address - Validation - zip code (invalid) (' . $this->zip . ')';
      }

      if (!preg_match('/^\(?\d{3}\)?[\- .]?\d{3}[\- .]?\d{4}$/', $this->phone)) {
        $_errors[] = 'Address - Validation - phone number (invalid, must be in format ###-###-####) (' . $this->phone . ')';
      }

      if (!preg_match('/^[A-z.\- ]+$/', $this->city)) {
        $_errors[] = 'Address - Validation - city (invalid, only letters/spaces allowed) (' . $this->city . ')';
      }
            
      if (!preg_match('/^[a-z]{2}$/i', $this->state)) {
        $_errors[] = 'Address - validation - state (invalid, must be two-letter state abbreviation) (' . $this->state . ')';
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
