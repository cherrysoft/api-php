<?php

require_once('../Ordrin.php');

/*
no DateTime:createFromFormat in older PHP versions

pif ($_POST["dT"] == 'ASAP') {
  $dt = new dT();
  $dt->asap();
} else {
  $dt = new dT(DateTime::createFromFormat('mdY', $_POST["dT"]));
}
*/

$dt = new dT("");
$dt->asap();
  
$api = new Ordrin('mlJhC8iX4BGWVtn', 'https://r-test.ordr.in');

switch ($_GET["api"]) {
  case "r":
    $r = new Restaurant();
  break;
  case "u":
    $u = new User();
    if ($_POST["func"] == "upass") {
      $u->setCurrAcct($_POST["email"], $_POST["oldPass"]);
    } elseif ($_POST["func"] != "macc") {
      $u->setCurrAcct($_POST["email"], $_POST["pass"]);
    } 
    $api->_url = "https://u-test.ordr.in";
  break;
  case "o":
    $o = new Order();
    $u = new User();
    $u->setCurrAcct($_POST["email"], $_POST["pass"]);
    $api->_url = "https://o-test.ordr.in";
    
    $a = new Address($_POST["addr"], $_POST["city"], $_POST["zip"], $_POST["addr2"], $_POST["state"], $_POST["phone"], $_POST["addrNick"]);
    $print = $o->submit($_POST["rid"], $_POST["tray"], $dt, $_POST["email"], $_POST["fName"], $_POST["lName"], $a, $_POST["fName"] . " " . $_POST["lName"], $_POST["cardNum"], $_POST["csc"], $_POST["expMo"] + $_POST["expYr"], $a);
    echo json_encode($print);
  break;
}

switch ($_POST["func"]) {
  case "dl":
    $addr = new Address($_POST["addr"], $_POST["city"], $_POST["zip"], "", $_POST["state"], "", "");
    $print = $r->deliveryList($dt, $addr);
    echo json_encode($print);
  break;
  case "dc":
    $addr = new Address($_POST["addr"], $_POST["city"], $_POST["zip"], "", $_POST["state"], "", "");
    $print = $r->deliveryCheck($_POST["rid"], $dt, $addr);
    echo json_encode($print);
  break;
  case "df":
    $sT = new Money($_POST["sT"]);
    $tip = new Money($_POST["tip"]);
    $addr = new Address($_POST["addr"], $_POST["city"], $_POST["zip"], "", $_POST["state"], "", "");
    $print = $r->deliveryFee($_POST["rid"], $sT, $tip, $dt, $addr);
    echo json_encode($print);
  break;
  case "rd":
    $print = $r->details($_POST["rid"]);
    echo json_encode($print);
  break;

  case "gacc":
    $print = $u->getAcct();
    echo json_encode($print);
  break;
  case "macc":
    $print = $u->makeAcct($_POST["email"], $_POST["pass"], $_POST["fName"], $_POST["lName"]);
    echo json_encode($print);
  break;
  case "upass":
    $print = $u->updatePassword($_POST["pass"]);
    echo json_encode($print);
  break;
  case "gaddr":
    $print = $u->getAddress($_POST["addrNick"]);
    echo json_encode($print);
  break;
  case "uaddr":
    $a = new Address($_POST["addr"], $_POST["city"], $_POST["zip"], $_POST["addr2"], $_POST["state"], $_POST["phone"], $_POST["addrNick"]);
    $print = $u->addAddress($a);
    echo json_encode($print);
  break;
  case "daddr":
    $print = $u->deleteAddress($_POST["addrNick"]);
    echo json_encode($print);
  break;
  case "gcar":
    $print = $u->getCard($_POST["cardNick"]);
    echo json_encode($print);
  break;
  case "ucar":
    $a = new Address($_POST["addr"], $_POST["city"], $_POST["zip"], $_POST["addr2"], $_POST["state"], $_POST["phone"], $_POST["addrNick"]);
    $print = $u->updateCard($_POST["cardNick"], $_POST["fName"] . $_POST["lName"], $_POST["cardNum"], $_POST["csc"], $_POST["expMo"], $_POST["expYr"], $a);
    echo json_encode($print);
  break;
  case "dcar":
    $print = $u->deleteCard($_POST["cardNick"]);
    echo json_encode($print);
  break;
  case "gordr":
    $print = $u->orderHistory($_POST["ordrID"]);
    echo json_encode($print);
  break;
  case "gordrs":
    $print = $u->orderHistory();
    echo json_encode($print);
  break;
}
?>