<?php
include "ordrin/api.php";
$email = "php+" . time() . "@ordr.in";
$password = "password";


$address = array(
  "addr" => "900 Broadway",
  "city" => "New York",
  "state" => "NY",
  "zip" => "10003",
  "phone" => "555-555-5555"
                 );

$credit_card = array(
  "card_name" => "Test User",
  "card_expiry" => "01/2016",
  "card_number" => "4111111111111111",
  "card_cvc" => "123",
  "card_bill_addr" => $address["addr"],
  "card_bill_city" => $address["city"],
  "card_bill_state" => $address["state"],
  "card_bill_zip" => $address["zip"],
  "card_bill_phone" => $address["phone"]
                     );

$ordrin_api = new Ordrin\APIs("2HGAzwbK5IWNJPRN_c-kvbqtfGhS-k2a6p-1Zg2iNN4", Ordrin\APIs::TEST);
$delivery_list = $ordrin_api->delivery_list(array("datetime" => "ASAP",
                                                  "addr" => "900 Broadway",
                                                  "city" => "New York",
                                                  "zip" => "10003"));
$restaurant_id = (string)($delivery_list[0]["id"]);
$detail = $ordrin_api->restaurant_details(array("rid" => $restaurant_id));
$acc_resp = $ordrin_api->create_account(array("email" => $email,
                                              "pw" => $password,
                                              "first_name" => "Test",
                                              "last_name" => "User"));
echo json_encode($acc_resp, JSON_PRETTY_PRINT);

$acc_desc = $ordrin_api->get_account_info(array("email" => $email,
                                                "current_password" => $password));
echo json_encode($acc_desc, JSON_PRETTY_PRINT);

function find_item_to_order($item_list){
  foreach($item_list as $item){
    if($item["is_orderable"] == 1){
      if(floatval($item["price"]) >= 5.00){
        return (string)$item["id"];
      }
    } else {
      if(array_key_exists("children", $item)){
        $item_id = find_item_to_order($item["children"]);
        if(!is_null($item_id)){
          return $item_id;
        }
      }
    }
  }
  return null;
}

$item_id = find_item_to_order($detail["menu"]);
$tray = "$item_id/10";

$order_data = array_merge($address, $credit_card, array("rid" => $restaurant_id,
                                                        "tray" => $tray,
                                                        "tip" => "5.00",
                                                        "first_name" => "Test",
                                                        "last_name" => "User",
                                                        "delivery_date" => "ASAP",
                                                        "em" => $email));
echo json_encode($ordrin_api->order_guest($order_data));
?>