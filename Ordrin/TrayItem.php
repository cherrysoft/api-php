<?php

/* Address Class */
class TrayItem {

    function __construct($itemId, $quantity, $options = null) {
      $this->itemId = $itemId;
      $this->quantity = $quantity;
      $this->options = $options;
    }

    function validate($element = "all") {
        if ($element == 'zip' && !preg_match('/(^\d{5}$)|(^\d{5}-\d{4}$)/', $this->$element)) {
            parent::$_errors[] = 'Address - validation - zip code (invalid) (' . $this->$element . ')';
        } elseif ($element == 'phone' && !preg_match('/(^\(?(\d{3})\)?[- .]?(\d{3})[- .]?(\d{4})$)/', $this->$element)) {
            parent::$_errors[] = 'Address - validation - phone number (invalid) (' . $this->$element . ')';
        } elseif ($element == 'city' && !preg_match('/[A-z.-]/', $this->$element)) {
            parent::$_errors[] = 'Address - validation - city (invalid, only letters/spaces allowed) (' . $this->$element . ')';
        } elseif ($element == 'state' && !preg_match('/^([A-z]){2}$/', $this->$element)) {
            parent::$_errors[] = 'Address - validation - state (invalid, only letters allowed and must be passed as two-letter abbreviation) (' . $this->$element . ')';
        } else {
            // do ALL validation
            if (!preg_match('/(^\d{5}$)|(^\d{5}-\d{4}$)/', $this->zip)) {
                parent::$_errors[] = 'Address - validation - zip code (invalid) (' . $this->zip . ')';
            }

            if (!preg_match('/(^\(?(\d{3})\)?[- .]?(\d{3})[- .]?(\d{4})$)/', $this->phone) && $this->phone != "") {
                parent::$_errors[] = 'Address - validation - phone number (invalid) (' . $this->phone . ')';
            }

            if (!preg_match('/[A-z.-]/', $this->city)) {
                parent::$_errors[] = 'Address - validation - city (invalid, only letters/spaces allowed) (' . $this->city . ')';
            }
            
            if (!preg_match('/^([A-z]){2}$/', $this->state)  && $this->$state != "") {
                parent::$_errors[] = 'Address - validation - state (invalid, only letters allowed and must be passed as two-letter abbreviation) (' . $this->state . ')';
            }
        }
    }

    function _convertForAPI() {
        return $this->__get('zip') . '/' . $this->__get('city') . '/' . $this->__get('street');
    }

    function __set($name, $value) {
        $this->$name = $value;
    }

    function __get($name) {
        return $this->$name;
    }
}
