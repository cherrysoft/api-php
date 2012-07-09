<?php

/* TrayItem Class */
class TrayItem {

    function __construct($itemId, $quantity, $options = null) {
      $this->itemId = $itemId;
      $this->quantity = $quantity;
      if($options != null)
        $this->options = $options;
      
      $this->validate();
    }

    function validate($element = "all") {
      $_errors = array();
    
      if (!preg_match('/^\d+$/', $this->itemId)) {
        $_errors[] = 'TrayItem - Validation - Item ID (invalid, must be integer) (' . $this->itemId . ')';
      }
      
      if (!preg_match('/^\d+$/', $this->quantity)) {
        $_errors[] = 'TrayItem - Validation - Quantity (invalid, must be integer) (' . $this->quantity . ')';
      }
      
      if(isset($this->options)) {
        foreach($this->options as $option) {
          if (!preg_match('/^\d+$/', $option)) {
            $_errors[] = 'TrayItem - Validation - Options (invalid, must be integer) (' . $option . ')';
          }
        }
      }
      
      if(!empty($_errors)) {
        throw new OrdrinExceptionBadValue($_errors);
      }
    }

    function _convertForAPI() {
      $api_string = $this->__get('itemId') . '/' . $this->__get('quantity');
      if(isset($this->options)) {
        $api_string .= "," . implode($this->__get('options'), ",");
      }
      return $api_string;
    }

    function __set($name, $value) {
        $this->$name = $value;
    }

    function __get($name) {
        return $this->$name;
    }
}

?>