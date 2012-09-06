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

    function validate($errors=null) {
      $validation = new Validation($errors);
      $validation->validate('itemID',$this->itemId);
      $validation->validate('quantity',$this->quantity);
      if(isset($this->options)) {
      	foreach($this->options as $option) {
      		$validation->validate('option', $option);
      	}
      }
      if(!empty($errors)) {
        throw new OrdrinExceptionBadValue($validation->getErrors());
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