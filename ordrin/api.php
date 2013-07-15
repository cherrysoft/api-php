<?php
namespace Ordrin;
class APIs{
  const PRODUCTION = APIHelper::PRODUCTION;
  const TEST = APIHelper::TEST;

  public function __construct($api_key, $servers){
    $helper = new APIHelper($api_key, $servers);
  }

  
  // order endpoints
  
  public function ($args){
    /*
      Arguments:
    rid--Ordr.in's unique restaurant identifier for the restaurant.
    em--The customer's email address
    tip--Tip amount in dollars and cents
    first_name--The customer's first name
    last_name--The customer's last name
    phone--The customer's phone number
    zip--The zip code part of the address
    addr--The street address
    city--The city part of the address
    state--The state part of the address
    card_number--Credit card number
    card_cvc--3 or 4 digit security code
    card_expiry--The credit card expiration date.
    card_bill_addr--The credit card's billing street address
    card_bill_city--The credit card's billing city
    card_bill_state--The credit card's billing state
    card_bill_zip--The credit card's billing zip code
    card_bill_phone--The credit card's billing phone number

    Keyword Arguments:
    tray--Represents a tray of menu items in the format '[menu item id]/[qty],[option id],...,[option id]'
    addr2--The second part of the street address, if needed
    card_name--Full name as it appears on the credit card
    card_bill_addr2--The second part of the credit card's biling street address.


    Either
    delivery_date--Delivery date
    delivery_time--Delivery time
    OR
    delivery_date--Delivery date
     */
    return $helper->call_endpoint("order", "order_guest", array("rid"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    rid--Ordr.in's unique restaurant identifier for the restaurant.
    tip--Tip amount in dollars and cents
    first_name--The customer's first name
    last_name--The customer's last name
    email -- The user's email
    current_password -- The user's current password

    Keyword Arguments:
    tray--Represents a tray of menu items in the format '[menu item id]/[qty],[option id],...,[option id]'


    Either
    phone--The customer's phone number
    zip--The zip code part of the address
    addr--The street address
    addr2--The second part of the street address, if needed
    city--The city part of the address
    state--The state part of the address
    OR
    nick--The delivery location nickname. (From the user's addresses)
    Either
    card_name--Full name as it appears on the credit card
    card_number--Credit card number
    card_cvc--3 or 4 digit security code
    card_expiry--The credit card expiration date.
    card_bill_addr--The credit card's billing street address
    card_bill_addr2--The second part of the credit card's biling street address.
    card_bill_city--The credit card's billing city
    card_bill_state--The credit card's billing state
    card_bill_zip--The credit card's billing zip code
    card_bill_phone--The credit card's billing phone number
    OR
    card_nick--The credit card nickname. (From the user's credit cards)
    Either
    delivery_date--Delivery date
    delivery_time--Delivery time
    OR
    delivery_date--Delivery date
     */
    return $helper->call_endpoint("order", "order_user", array("rid"), $args);
  }
  
  
  // restaurant endpoints
  
  public function ($args){
    /*
      Arguments:
    datetime--Delivery date and time
    rid--Ordr.in's unique restaurant identifier for the restaurant.
    addr--Delivery location street address
    city--Delivery location city
    zip--The zip code part of the address

    Keyword Arguments:


     */
    return $helper->call_endpoint("restaurant", "delivery_check", array("rid", "datetime", "zip", "city", "addr"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    datetime--Delivery date and time
    addr--Delivery location street address
    city--Delivery location city
    zip--The zip code part of the address

    Keyword Arguments:


     */
    return $helper->call_endpoint("restaurant", "delivery_list", array("datetime", "zip", "city", "addr"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    datetime--Delivery date and time
    rid--Ordr.in's unique restaurant identifier for the restaurant.
    subtotal--The cost of all items in the tray in dollars and cents.
    tip--The tip in dollars and cents.
    addr--Delivery location street address
    city--Delivery location city
    zip--The zip code part of the address

    Keyword Arguments:


     */
    return $helper->call_endpoint("restaurant", "fee", array("rid", "subtotal", "tip", "datetime", "zip", "city", "addr"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    rid--Ordr.in's unique restaurant identifier for the restaurant.

    Keyword Arguments:


     */
    return $helper->call_endpoint("restaurant", "restaurant_details", array("rid"), $args);
  }
  
  
  // user endpoints
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    password--The user's new password
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "change_password", array("email"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    pw--The user's password
    first_name--The user's first name
    last_name--The user's last name

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "create_account", array("email"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    nick--The nickname of this address
    phone--The customer's phone number
    zip--The zip code part of the address
    addr--The street address
    city--The city part of the address
    state--The state part of the address
    current_password -- The user's current password

    Keyword Arguments:
    addr2--The second part of the street address, if needed


     */
    return $helper->call_endpoint("user", "create_addr", array("email", "nick"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    nick--The nickname of this address
    card_number--Credit card number
    card_cvc--3 or 4 digit security code
    card_expiry--The credit card expiration date.
    bill_addr--The credit card's billing street address
    bill_city--The credit card's billing city
    bill_state--The credit card's billing state
    bill_zip--The credit card's billing zip code
    bill_phone--The credit card's billing phone number
    current_password -- The user's current password

    Keyword Arguments:
    bill_addr2--The second part of the credit card's biling street address.


     */
    return $helper->call_endpoint("user", "create_cc", array("email", "nick"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    nick--The nickname of this address
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "delete_addr", array("email", "nick"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    nick--The nickname of this address
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "delete_cc", array("email", "nick"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "get_account_info", array("email"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "get_all_saved_addrs", array("email"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "get_all_saved_ccs", array("email"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    oid--Ordr.in's unique order id number.
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "get_order", array("email", "oid"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "get_order_history", array("email"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    nick--The nickname of this address
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "get_saved_addr", array("email", "nick"), $args);
  }
  
  public function ($args){
    /*
      Arguments:
    email--The user's email address
    nick--The nickname of this address
    current_password -- The user's current password

    Keyword Arguments:


     */
    return $helper->call_endpoint("user", "get_saved_cc", array("email", "nick"), $args);
  }
  
  
}