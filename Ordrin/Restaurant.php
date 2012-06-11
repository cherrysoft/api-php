<?php

/* Restuarant API */
class Restaurant extends OrdrinApi {
    function __construct($key,$base_url){
      $this->_key = $key;
      $this->base_url = $base_url;
    }

    function getDeliveryList($date_time, $address) {
        //$addr->validate();

        return $this->_call_api("GET",
                                array(
                                  "dl",
                                  "ASAP",
                                  $address->zip,
                                  $address->city,
                                  $address->street
                                ),
                                "GET"
                        );
    }

    function deliveryCheck($rID, $dT, $addr) {
        if (!is_numeric($rID)) {
            parent::$_errors[] = "Restaurant DeliveryCheck - Validation - restaurant ID (invalid, must be numeric) we got ($rID)";
        }

        //$addr->validate();
        return $this->_call_api("GET",
                                array(
                                 "dc",
                                 $rID, 
                                 "ASAP",
                                 rawurlencode($addr->zip),
                                 rawurlencode($addr->city),
                                 rawurlencode($addr->street)
                             )
                        );
    }

    function deliveryFee($rID, $subtotal, $tip, $dT, $addr) {
        if (!is_numeric($rID)) {
            parent::$_errors[] = "Restaurant DeliveryCheck - Validation - restaurant ID (invalid, must be numeric) we got ($rID)";
        }

//        $addr->validate();
        return $this->_call_api("GET",
                               array(
                                  "fee",
                                  $rID,
                                  $subtotal,
                                  $tip,
                                  "ASAP",
                                  $addr->zip,
                                  $addr->city,
                                  $addr->street
                              )
                        );
    }

    function details($rID) {
        if (!is_numeric($rID)) {
            parent::$_errors[] = "Restaurant DeliveryCheck - Validation - restaurant ID (invalid, must be numeric) we got ($rID)";
        }

        return $this->_call_api("GET",
                               array("rd",$rID)
                        );
    }

}
