<?php
namespace Ordrin;
spl_autoload_register();
require "JsonSchema/src/Loader.php";
use Pyrus\JsonSchema\JSV;
require "mutate.php";
use \HttpRequest;
use \Exception;
class APIHelper{
  const PRODUCTION = 0;
  const TEST = 1;

  private $api_key;
  private $urls;
  
  private $ENDPOINT_INFO;

  private $methods;


  public function __construct($api_key, $servers){
    $this->ENDPOINT_INFO = json_decode(file_get_contents(dirname(__FILE__) . "/schemas.json"), true);
    $this->methods = array("GET" => HttpRequest::METH_GET,
                            "POST" => HttpRequest::METH_POST,
                            "PUT" => HttpRequest::METH_PUT,
                            "DELETE" => HttpRequest::METH_DELETE);
    $this->api_key = $api_key;
    if($servers == APIHelper::PRODUCTION){
      $this->urls = array("restaurant" => "https://r.ordr.in",
                          "user" => "https://u.ordr.in",
                          "order" => "https://o.ordr.in");
    } elseif($servers == APIHelper::TEST){
      $this->urls = array("restaurant" => "https://r-test.ordr.in",
                           "user" => "https://u-test.ordr.in",
                           "order" => "https://o-test.ordr.in");
    }
  }

  private function call_api($base_url, $method, $uri, $data=NULL, $login=NULL){
    $full_url = $base_url . $uri;
    $headers = array("X-NAAMA-CLIENT-AUTHENTICATION" => "id=\"$this->api_key\", version=\"1\"");
    if(!is_null($login)){
      $hash_code = hash("sha256", join('', array($login["password"], $login["email"], $uri)));
      $headers["X-NAAMA-AUTHENTICATION"] = "username=\"$login[email]\", response=\"$hash_code\", version=\"1\"";
    }
    $request = new HttpRequest($full_url, $this->methods[$method]);
    $request->addHeaders($headers);

    switch($method){
      case "POST" : $request->setPostFields($data); break;
      case "PUT"  : $request->setPutData(http_build_query($data)); $request->setContentType('application/x-www-form-urlencoded'); break;
      default     : break;
    }
    $message = $request->send();
    $response = $message->getBody();
    $result = json_decode($response, true);
    if(!is_null($result) && 
       array_key_exists('_error', $result) && 
       $result["error"]){
      if(array_key_exists("text", $result)){
        throw new Exception("$result[msg]; $result[text]");
      } else {
        throw new Exception($result["msg"]);
      }
    }
    return $result;
  }

  public function call_endpoint($endpoint_group, $endpoint_name, $url_params, $kwargs){
    $endpoint_data = $this->ENDPOINT_INFO[$endpoint_group][$endpoint_name];
    $value_mutators = array();
    foreach($endpoint_data["properties"] as $name=>$info){
      if(array_key_exists("mutator", $info)){
        $value_mutators[$name] = $info["mutator"];
      } else {
        $value_mutators[$name] = "identity";
      }
    }
    if(array_key_exists("allOf", $endpoint_data)){
      foreach($endpoint_data["allOf"] as $subschema){
        foreach($subschema["oneOf"] as $option){
          foreach($option["properties"] as $name=>$info){
            if(array_key_exists("mutator", $info)){
              $value_mutators[$name] = $info["mutator"];
            } else {
              $value_mutators[$name] = "identity";
            }
          }
        }
      }
    }
    if(!array_key_exists("email", $value_mutators)){
      $value_mutators["email"] = "identity";
    }
    $env = JSV::createEnvironment(null);
    $report = $env->validate($kwargs, $endpoint_data);
    if(count($report->errors)){
      $msg = "";
      foreach($report->errors as $error){
        $msg = $msg . "\n" . strval($error->getMessage());
      }
      throw new Exception($msg);
    }
    
    $arg_dict = array();
    foreach($url_params as $name){
      $arg_dict[$name] = urlencode(Mutate::$value_mutators[$name]($kwargs[$name]));
    }
    $data = array();
    foreach($kwargs as $name=>$value){
      if(!in_array("name", $url_params) && $name != "current_password"){
        $data[$name] = Mutate::$value_mutators[$name]($value);
      }
    }
    $uri = $endpoint_data["meta"]["uri"];
    foreach($arg_dict as $name=>$value){
      $uri = str_replace('{' . $name . '}', $value, $uri);
    }
    if(empty($data)){
      $data = NULL;
    }
    $login = NULL;
    if($endpoint_data["meta"]["userAuth"]){
      $login = array("email" => $kwargs["email"],
                     "password" => Mutate::sha256($kwargs["current_password"]));
    }
    return $this->call_api($this->urls[$endpoint_group], $endpoint_data["meta"]["method"], $uri, $data, $login);
  }
}
