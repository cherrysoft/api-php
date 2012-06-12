<?php

require_once('../OrdrinApi.php');

/*
no DateTime:createFromFormat in older PHP versions

pif ($_POST["dT"] == 'ASAP') {
  $dt = new dT();
  $dt->asap();
} else {
  $dt = new dT(DateTime::createFromFormat('mdY', $_POST["dT"]));
}
*/

/*$dt = new dT("");
$dt->asap();*/
$dt = 'ASAP';

$ordrin = new OrdrinApi('HDpXJTdP4RGsKtNku8bTaA', OrdrinApi::TEST_SERVERS);

switch ($_GET["api"]) {
  case "r":
    // don't need to do anything
  break;
  case "u":
    $ordrin->user->authenticate($_POST['email'],hash('sha256',$_POST['pass']));
  break;
  case "o":
    $ordrin->user->authenticate($_POST['email'],hash('sha256',$_POST['pass']));
    
    $addr = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], "");
    $print = $ordrin->order->submit($_POST["rid"], $_POST["tray"], $dt, $_POST["email"], $_POST["fName"], $_POST["lName"], $a, $_POST["fName"] . " " . $_POST["lName"], $_POST["cardNum"], $_POST["csc"], $_POST["expMo"] + $_POST["expYr"], $a);
    echo json_encode($print);
  break;
}

switch ($_POST["func"]) {
  case "dl":
    $addr = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], "");
    $print = $ordrin->restaurant->getDeliveryList($dt, $addr);
    echo json_encode($print);
  break;
  case "dc":
    $addr = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], "");
    $print = $ordrin->restaurant->deliveryCheck($_POST["rid"], $dt, $addr);
    echo json_encode($print);
  break;
  case "df":
    $sT = $_POST["sT"];
    $tip = $_POST["tip"];
    $addr = $ordrin::address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], "");
    $print = $ordrin->restaurant->deliveryFee($_POST["rid"], $sT, $tip, $dt, $addr);
    echo json_encode($print);
  break;
  case "rd":
    $print = $ordrin->restaurant->details($_POST["rid"]);
    echo json_encode($print);
  break;

  case "gacc":
    $print = $ordrin->user->getAccountInfo();
    echo json_encode($print);
  break;
  case "macc":
    $print = $u->makeAcct($_POST["email"], hash('sha256',$_POST["pass"]), $_POST["fName"], $_POST["lName"]);
    echo json_encode($print);
  break;
  case "upass":
    $print = $ordrin->user->updatePassword(hash('sha256',$_POST['pass']));
    echo json_encode($print);
  break;
  case "gaddr":
    $print = $ordrin->user->getAddress($_POST["addrNick"]);
    echo json_encode($print);
  break;
  case "uaddr":
    $a = $ordrin::Address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], $_POST["phone"], $_POST["addr2"]);
    $print = $ordrin->user->setAddress($_POST["addrNick"], $a);
    echo json_encode($print);
  break;
  case "daddr":
    $print = $ordrin->user->deleteAddress($_POST["addrNick"]);
    echo json_encode($print);
  break;
  case "gcar":
    $print = $ordrin->user->getCard($_POST["cardNick"]);
    echo json_encode($print);
  break;
  case "ucar":
    $a = $ordrin::Address($_POST["addr"], $_POST["city"], $_POST["state"], $_POST["zip"], $_POST["phone"], $_POST["addr2"]);
    $print = $ordrin->user->setCard($_POST["cardNick"], $_POST["fName"] . $_POST["lName"], $_POST["cardNum"], $_POST["csc"], $_POST["expMo"], $_POST["expYr"], $a);
    echo json_encode($print);
  break;
  case "dcar":
    $print = $ordrin->user->deleteCard($_POST["cardNick"]);
    echo json_encode($print);
  break;
  case "gordr":
    $print = $ordrin->user->getOrderHistory();
    echo json_encode($print);
  break;
  case "gordrs":
    $print = $ordrin->user->getOrderHistory($_POST["ordrID"]);
    echo json_encode($print);
  break;
}
?>
