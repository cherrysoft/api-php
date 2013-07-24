<?php
namespace Ordrin;
class Mutate{
  public static function sha256( $value ){
    return hash("sha256", $value);
  }

  public static function state( $value ){
    return strtoupper($value);
  }
  
  public static function phone( $value ){
    $value = preg_replace('/\D/', "", $value);
    return preg_replace('/^(\d{3})(\d{3})(\d{4})$/', '$1-$2-$3', $value);
  }

  public static function identity( $value ){
    return $value;
  }
}