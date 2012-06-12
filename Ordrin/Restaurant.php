<?php

/* Restuarant API */
class Restaurant extends OrdrinApi {
    function __construct($key,$base_url){
      $this->_key = $key;
      $this->base_url = $base_url;
    }

    /**
     * Get a list of restaurants that deliver to a particular address.
     *
     * @param mixed   $date_time  Either "ASAP" or date time object for delivery time
     * @param object  $address    Address for delivery
     *
     * @return object An object containing a list of restaurants that delivery to address
     */
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

    /**
     * Tell if a particular restaurant delivers to an address at the specified time.
     *
     * @param int     $rID        Ordr.in's restaurant identifier
     * @param mixed   $date_time  Either "ASAP" or date time object for delivery time
     * @param object  $address    Address for delivery
     *
     * @return object An object containing information about the restaurant and if delivery is availble
     */
    function deliveryCheck($rID, $date_time, $addr) {
        if (!is_numeric($rID)) {
            parent::$_errors[] = "Restaurant DeliveryCheck - Validation - restaurant ID (invalid, must be numeric) we got ($rID)";
        }

        //$addr->validate();
        return $this->_call_api("GET",
                                array(
                                 "dc",
                                 $rID, 
                                 "ASAP",
                                 $addr->zip,
                                 $addr->city,
                                 $addr->street
                             )
                        );
    }

    /**
     * Calculate all fees for a given subtotal and delivery address.
     *
     * @param int     $rID        Ordr.in's restaurant identifier
     * @param float   $subtotal   The cost of all items in tray in dollars and cents
     * @param float   $tip        The amount of tip in dollar's and cents
     * @param mixed   $date_time  Either "ASAP" or date time object for delivery time
     * @param object  $address    Address for delivery
     *
     * @return object An object containing information about the restaurant and fee amount
     */
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
