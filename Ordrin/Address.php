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
    	$validation = new Validation();
     	//do ALL validation
      	$validation->validate('zipCode',$this->zip);
      	$validation->validate('telephone',$this->phone);
      	$validation->validate('city',$this->city);
      	$validation->validate('state',$this->state);
     	if(!empty($validation->errors)) {
     	   throw new OrdrinExceptionBadValue($validation->errors);
      	}
    }

    function __set($name, $value) {
        $this->$name = $value;
    }

    function __get($name) {
        return $this->$name;
    }
}
