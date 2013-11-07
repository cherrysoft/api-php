<?php
/**
 * json-schema-draft-01 Environment
 * 
 * @fileOverview Implementation of the first revision of the JSON Schema specification draft.
 * @author Michael Lumish
 * @version 1.5
 * @see http://github.com/murgatroid99/JsonSchema
 */
namespace Pyrus\JsonSchema\JSV\Schema;

/* http://www.wtfpl.net/ */

use Pyrus\JsonSchema\JSV\Exception, Pyrus\JsonSchema\JSV\ValidationException,
  Pyrus\JsonSchema\JSV\JSONInstance, Pyrus\JsonSchema\JSV\JSONSchema,
  Pyrus\JsonSchema\JSV\Report, Pyrus\JsonSchema\JSV\URI,
  Pyrus\JsonSchema\JSV\EnvironmentOptions, Pyrus\JsonSchema\JSV\Environment,
  Pyrus\JsonSchema\JSV, Pyrus\JsonSchema as JS;

class Draft04 extends Draft03{

  function __construct($register = true){
    return parent::__construct($register);
  }

  function initializeEnvironment(){
        $this->ENVIRONMENT = new Environment();
        $this->ENVIRONMENT->setOption("validateReferences", true);
        $this->ENVIRONMENT->setOption("defaultSchemaURI", "http://json-schema.org/draft-04/schema#");
        
        //prevent reference errors
        $this->ENVIRONMENT->createSchema(array(), true, "http://json-schema.org/draft-04/schema#");
        $this->ENVIRONMENT->createSchema(array(), true, "http://json-schema.org/draft-04/hyper-schema#");
        $this->ENVIRONMENT->createSchema(array(), true, "http://json-schema.org/draft-04/links#");
    
  }

  function initializeSchema($uri = "http://json-schema.org/draft-04/schema#"){
    return parent::initializeSchema($uri);
  }

  function initializeHyperSchema($uri1 = "http://json-schema.org/draft-04/hyper-schema#", $uri2 = "http://json-schema.org/draft-04/hyper-schema#"){
    return parent::initializeHyperSchema($uri1, $uri2);
  }

  function initializeLinks($uri = "http://json-schema.org/draft-04/links#"){
    return parent::initializeLinks($uri);
  }

  function registerSchemas(){
    //We need to reinitialize these schemas as they reference each other
    $this->HYPERSCHEMA = $this->ENVIRONMENT->createSchema($this->HYPERSCHEMA->getValue(), $this->HYPERSCHEMA,
                                                          "http://json-schema.org/draft-04/hyper-schema#");
        
    $this->ENVIRONMENT->setOption("latestJSONSchemaSchemaURI", "http://json-schema.org/draft-04/schema#");
    $this->ENVIRONMENT->setOption("latestJSONSchemaHyperSchemaURI", "http://json-schema.org/draft-04/hyper-schema#");
    $this->ENVIRONMENT->setOption("latestJSONSchemaLinksURI", "http://json-schema.org/draft-04/links#");
        
    //
    //Latest JSON Schema
    //
        
    //Hack, but WAY faster then instantiating a new schema
    $this->ENVIRONMENT->replaceSchema("http://json-schema.org/schema#", $this->SCHEMA);
    $this->ENVIRONMENT->replaceSchema("http://json-schema.org/hyper-schema#", $this->HYPERSCHEMA);
    $this->ENVIRONMENT->replaceSchema("http://json-schema.org/links#", $this->LINKS);
        
    //
    //register environment
    //
        
    $this->ENVIRONMENT->setOption("defaultFragmentDelimiter", "/");
    JSV::registerEnvironment("json-schema-draft-04", $this->ENVIRONMENT);
    if (!JSV::getDefaultEnvironmentID() || JSV::getDefaultEnvironmentID() === "json-schema-draft-01"
        || JSV::getDefaultEnvironmentID() === "json-schema-draft-02"
        || JSV::getDefaultEnvironmentID() === "json-schema-draft-03") {
      JSV::setDefaultEnvironmentID("json-schema-draft-04");
    }
  }

  function getSchemaArray(){
    $SCHEMA_03_JSON = parent::getSchemaArray();
    $propertiesparser = $SCHEMA_03_JSON["properties"]["properties"]["parser"];
    $extendsparser = $SCHEMA_03_JSON["properties"]["extends"]["parser"];

    $diff = array('$schema' => "http://json-schema.org/draft-04/schema#",
                  'id' => "http://json-schema.org/draft-04/schema#",
                  'properties' =>
                  array('multipleOf' =>
                        array('type' => 'number',
                              'minimum' => 1,
                              'validator' =>
                              function ($instance, $schema, $self, $report, $parent, $parentSchema, $name){
                                if (is_numeric($instance->getValue())){
                                  $multipleOf = $schema->getAttribute("multipleOf");
                                  if(is_numeric($multipleOf) && $instance->getValue() % $multipleOf !== 0){
                                    $report->addError($instance, $schema, "multipleOf",
                                                      "Number is not a multiple of the required multiple value" .
                                                      " [schema path: " . $instance->getPath() . "]", $minimum);
                                  }
                                }
                              }),
                        'maxProperties' =>
                        array('type' => 'integer',
                              'minimum' => 0,
                              'validator' =>
                              function ($instance, $schema, $self, $report, $parent, $parentSchema, $name){
                                $maxProperties = $schema->getAttribute("maxProperties");
                                if(is_int($maxProperties) && count($instance->getValue()) > $maxProperties){
                                  $report->addError($instance, $schema, "maxProperties",
                                                    "Object has more than the required maximum properties" .
                                                    " [schema path: " . $instance->getPath() . "]", $maxProperties);
                                }
                              }),
                        'minProperties' =>
                        array('type' => 'integer',
                              'minimum' => 0,
                              'validator' =>
                              function ($instance, $schema, $self, $report, $parent, $parentSchema, $name){
                                $minProperties = $schema->getAttribute("minProperties");
                                if(is_int($minProperties) && count($instance->getValue()) > $maxProperties){
                                  $report->addError($instance, $schema, "minProperties",
                                                    "Object has fewer than the required minimum properties" .
                                                    " [schema path: " . $instance->getPath() . "]", $minProperties);
                                }
                              }),
                        'required' =>
                        array('type' => 'array',
                              'default' => array(),
                              'validator' =>
                              function ($instance, $schema, $self, $report, $parent, $parentSchema, $name){
                                if(JS\is_json_object($instance->getValue())){
                                  $required = $schema->getAttribute("required");
                                  foreach($required as $req){
                                    $prop = $instance->getProperty($req);
                                    if($prop === null || ! $prop->getValue() ){
                                      $report->addError($instance, $schema, "required",
                                                        "Required property \"$req\" not provided" .
                                                        " [schema path: " . $instance->getPath() . "]", $required);
                                    }
                                  }
                                }
                              }),
                        'allOf' =>
                        array('type' => 'array',
                              'default' => array(),
                              'minItems' => 1,
                              'parser' => $extendsparser,
                              'validator' =>
                              function ($instance, $schema, $self, $report, $parent, $parentSchema, $name){
                                $subschemas = $schema->getAttribute("allOf");
                                if (JS\is_json_array($subschemas)){
                                  foreach($subschemas as $subschema){
                                    $subschema->validate($instance, $report, $parent, $parentSchema, $name);
                                  }
                                }
                              }),
                        'anyOf' =>
                        array('type' => 'array',
                              'default' => array(),
                              'minItems' => 1,
                              'parser' => $extendsparser,
                              'validator' =>
                              function ($instance, $schema, $self, $report, $parent, $parentSchema, $name){
                                $subschemas = $schema->getAttribute("anyOf");
                                if(JS\is_json_array($subschemas)){
                                  if(empty($subschemas)){
                                    return;
                                  }
                                  foreach($subschemas as $subschema){
                                    $temp_report = new Report();
                                    $subschema->validate($instance, $temp_report, $parent, $parentSchema, $name);
                                    if(!count($temp_report->errors)){
                                      return;
                                    }
                                  }
                                  $report->addError($instance, $schema, "anyOf",
                                                    "Object did not match any element of the 'anyOf' array" .
                                                    " [schema path: " . $instance->getPath() . "]", $subschemas);
                                }
                              }),
                        'oneOf' =>
                        array('type' => 'array',
                              'default' => array(),
                              'minItems' => 1,
                              'parser' => $extendsparser,
                              'validator' =>
                              function ($instance, $schema, $self, $report, $parent, $parentSchema, $name){
                                $subschemas = $schema->getAttribute("oneOf");
                                if(JS\is_json_array($subschemas)){
                                  $temp_full_report = new Report();
                                  $found = false;
                                  foreach($subschemas as $subschema){
                                    $temp_report = new Report();
                                    $subschema->validate($instance, $temp_report, $parent, $parentSchema, $name);
                                    if(!count($temp_report->errors)){
                                      if($found){
                                        $report->addError($instance, $schema, "oneOf",
                                                          "Object matched more than one element of the 'oneOf' array" .
                                                          " [schema path: " . $instance->getPath() . "]", $subschemas);
                                        return;
                                      } else {
                                        $found = true;
                                      }
                                    } else {
                                      $temp_full_report->errors = array_merge($temp_full_report->errors,
                                                                              $temp_report->errors);
                                    }
                                  }

                                  if(!$found){
                                    $report->addError($instance, $schema, "oneOf",
                                                      "Object did not match any element of the 'oneOf' array" .
                                                      " [schema path: " . $instance->getPath() . "]", $subschemas);
                                    $report->errors = array_merge($report->errors,
                                                                  $temp_full_report->errors);
                                  }
                                                                
                                }
                              }),
                        'not' =>
                        array('type' => 'array',
                              'default' => array(),
                              'minItems' => 1,
                              'parser' => $extendsparser,
                              'validator' =>
                              function ($instance, $schema, $self, $report, $parent, $parentSchema, $name){
                                $subschemas = $schema->getAttribute("not");
                                if(JS\is_json_array($subschemas)){
                                  foreach($subschemas as $subschema){
                                    $temp_report = new Report();
                                    $subschema->validate($instance, $temp_report, $parent, $parentSchema, $name);
                                    if(!count($temp_report->errors)){
                                      $report->addError($instance, $schema, "oneOf",
                                                        "Object matched an element of the 'not' array" .
                                                        " [schema path: " . $instance->getPath() . "", $subschemas);
                                    }
                                  }
                                }
                              })));
    return JSV::inherits($SCHEMA_03_JSON, $diff);
  }

    function getHyperSchemaArray()
    {
      $arr = parent::getHyperSchemaArray();
      $arr['$schema'] = "http://json-schema.org/draft-04/hyper-schema#";
      $arr['id'] = "http://json-schema.org/draft-04/hyper-schema#";
      $arr['properties']['links']['selfReferenceVariable'] = '@';
      $arr['properties']['root']['deprecated'] = true;
      $arr['properties']['contentEncoding']['deprecated'] = false;
      $arr['properties']['alternate']['deprecated'] = true;
      return $arr;
    }

    function getLinksArray()
    {
      $arr = parent::getLinksArray();
      $arr['$schema'] = "http://json-schema.org/draft-04/hyper-schema#";
      $arr['id'] = "http://json-schema.org/draft-04/links#";
        
      $arr['properties']['href']['required'] = true;
      $arr['properties']['properties']['deprecated'] = true;
      $arr['schema']['$ref'] = "http://json-schema.org/draft-04/hyper-schema#";
      return $arr;
    }
}
