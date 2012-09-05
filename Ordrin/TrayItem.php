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
      $validation = new Validation();
      $validation -> validateItemID($this->itemId);
      $validation -> validateQuantity($this->quantity);
      $validation -> validateItemID($this->itemId);
      if(isset($this->options)) {
      	foreach($this->options as $option) {
      		$validation -> validateInteger($option, 'Options');
      	}
      }
      if(!empty($validation->errors)) {
        throw new OrdrinExceptionBadValue($validation->errors);
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