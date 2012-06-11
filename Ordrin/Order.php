<?php

/* Order API */
class Order extends OrdrinApi {
    function __construct($key,$base_url){
      $this->_key = $key;
      $this->base_url = $base_url;
    }

    function submit($rID, $tray, $tip, $dateTime, $email, $fName, $lName, $addr, $credit_card) {
//        $addr->validate();
//        $ccAddr->validate();

        // Validations
/*
        if (!is_numeric($rID)) {
            parent::$_errors[] = 'Order submit - validation - restaurant ID (invalid, must be numeric) ' . "($rID)";
        }

        if (!preg_match($this->_cc_re, $card_number)) {
            parent::$_errors[] = 'Order submit - validation - credit card number (invalid) ' . "($card_number)";
        }

        if (!is_numeric($card_cvc)) {
            parent::$_errors[] = 'Order submit - validation - credit card security code (invalid, must be numeric) ' . "($card_cvc)";
        }

        if (filter_var($em, FILTER_VALIDATE_EMAIL) === false) {
            parent::$_errors[] = 'Order submit - validation - email (invalid) ' . "($em)";
        }

        // Validations are done

        if ($dT->_asap) {
            $date = "ASAP";
            $time = "";
        } else {
            $date = $dT->_strAPI('month') . '-' . $dT->_strAPI('day');
            $time = $dT->_strAPI('hour') . ':' . $dT->_strAPI('minute');
        }
*/
        $params =  array(
                                'tray' => $tray->_convertForAPI(),
                                'tip' => $tip,
                                'delivery_date' => "ASAP",
                                'delivery_time' => "",
                                'first_name' => $fName,
                                'last_name' => $lName,
                                'addr' => $addr->street,
                                'city' => $addr->city,
                                'state' => $addr->state,
                                'zip' => $addr->zip,
                                'phone' => $addr->phone,
                                'card_name' => $credit_card->name,
                                'card_number' => $credit_card->number,
                                'card_expiry' => $credit_card->expiration,
                                'card_cvc' => $credit_card->cvc,
                                'card_bill_addr' => $credit_card->address->street,
                                'card_bill_addr2' => $credit_card->address->street2,
                                'card_bill_city' => $credit_card->address->city,
                                'card_bill_state' => $credit_card->address->state,
                                'card_bill_zip' => $credit_card->address->zip,
                                'type' => 'res'
                            );

        $auth = false;
        if(!isset($email)){
          $auth = true;
          $params['password'] = hash('sha256','testtest');
        } else {
          $params['em'] = $email;
        }

        return $this->_call_api('POST',
                                array(
                                    'o',
                                    $rID,
                                ),
                                $params,
                                $auth
                        );
    }
}
