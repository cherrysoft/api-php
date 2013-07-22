<?php
include "ordrin/api.php";
$ordrin_api = new Ordrin\APIs("2HGAzwbK5IWNJPRN_c-kvbqtfGhS-k2a6p-1Zg2iNN4");
$delivery_list = $ordrin_api->delivery_list(array("datetime" => "ASAP",
                                                  "addr" => "900 Broadway",
                                                  "city" => "New York",
                                                  "zip" => "10003"));
echo $delivery_list;
?>