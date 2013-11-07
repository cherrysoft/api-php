<?php
/**
 * json-schema-draft-01 Environment
 * 
 * @fileOverview Implementation of the first revision of the JSON Schema specification draft.
 * @author Gary Court <gary.court@gmail.com>
 * @author Gregory Beaver <greg@chiaraquartet.net>
 * @version 1.5
 * @see http://github.com/garycourt/JSV
 */
namespace Pyrus\JsonSchema\JSV\Schema;

/*
 * Copyright 2010 Gary Court. All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 * 
 *    1. Redistributions of source code must retain the above copyright notice, this list of
 *       conditions and the following disclaimer.
 * 
 *    2. Redistributions in binary form must reproduce the above copyright notice, this list
 *       of conditions and the following disclaimer in the documentation and/or other materials
 *       provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY GARY COURT ``AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL GARY COURT OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are those of the
 * authors and should not be interpreted as representing official policies, either expressed
 * or implied, of Gary Court or the JSON Schema specification.
 */

/*jslint white: true, sub: true, onevar: true, undef: true, eqeqeq: true, newcap: true, immed: true, indent: 4 */
/*global require */

use Pyrus\JsonSchema\JSV\Exception, Pyrus\JsonSchema\JSV\ValidationException, Pyrus\JsonSchema\JSV\JSONInstance, Pyrus\JsonSchema\JSV\JSONSchema,
    Pyrus\JsonSchema\JSV\Report, Pyrus\JsonSchema\JSV\URI, Pyrus\JsonSchema\JSV\EnvironmentOptions, Pyrus\JsonSchema\JSV\Environment,
    Pyrus\JsonSchema\JSV, Pyrus\JsonSchema as JS;

class Draft01
{
    var $ENVIRONMENT,
        $TYPE_VALIDATORS,
        $SCHEMA,
        $HYPERSCHEMA,
        $LINKS;

    function __construct($register = true)
    {
        $this->initializeTypeValidators();
        $this->initializeEnvironment();
        $this->initializeSchema();
        $this->initializeHyperSchema();
        $this->initializeLinks();

        //We need to reinitialize these 3 schemas as they all reference each other
        if ($register) {
            $this->registerSchemas();
        }
    }

    function registerSchemas()
    {
        $this->SCHEMA = $this->ENVIRONMENT->createSchema($this->SCHEMA->getValue(), $this->HYPERSCHEMA, "http://json-schema.org/schema#");
        $this->HYPERSCHEMA = $this->ENVIRONMENT->createSchema($this->HYPERSCHEMA->getValue(), $this->HYPERSCHEMA, "http://json-schema.org/hyper-schema#");
        $this->LINKS = $this->ENVIRONMENT->createSchema($this->LINKS->getValue(), $this->HYPERSCHEMA, "http://json-schema.org/links#");

        JSV::registerEnvironment("json-schema-draft-00", $this->ENVIRONMENT);
        JSV::registerEnvironment("json-schema-draft-01", JSV::createEnvironment("json-schema-draft-00"));
        
        if (!JSV::getDefaultEnvironmentID()) {
            JSV::setDefaultEnvironmentID("json-schema-draft-01");
        }
    }

    function initializeTypeValidators()
    {
        $this->TYPE_VALIDATORS = array(
            "string" => function ($instance, $report) {
                return is_string($instance->getValue());
            },
            
            "number" => function ($instance, $report) {
                return !is_string($instance->getValue()) && is_numeric($instance->getValue());
            },
            
            "integer" => function ($instance, $report) {
                return is_int($instance->getValue());
            },
            
            "boolean" => function ($instance, $report) {
                return is_bool($instance->getValue());
            },
            
            "object" => function ($instance, $report) {
                return JS\is_json_object($instance->getValue());
            },
            
            "array" => function ($instance, $report) {
                return JS\is_json_array($instance->getValue());
            },
            
            "null" => function ($instance, $report) {
                return $instance->getValue() === null;
            },
            
            "any" => function ($instance, $report) {
                return true;
            }
        );
    }

    function initializeEnvironment()
    {
        $this->ENVIRONMENT = new Environment();
        $this->ENVIRONMENT->setOption("defaultFragmentDelimiter", ".");
        $this->ENVIRONMENT->setOption("defaultSchemaURI", "http://json-schema.org/schema#");  //updated later
    }

    protected function getSchemaArray()
    {
        return array(
            '$schema' => "http://json-schema.org/hyper-schema#",
            "id" => "http://json-schema.org/schema#",
            "type" => "object",
            
            "properties" => array(
                "type" => array(
                    "type" => array("string", "array"),
                    "items" => array(
                        "type" => array("string", array('$ref' => "#"))
                    ),
                    "optional" => true,
                    "uniqueItems" => true,
                    "default" => "any",
                    
                    "parser" => function (JSONInstance $instance, $self) {
                        if (is_string($instance->getValue())) {
                            return $instance->getValue();
                        } else if (JS\is_json_object($instance->getValue())) {
                            return $instance->getEnvironment()->createSchema(
                                $instance, 
                                $self->getEnvironment()->findSchema($self->resolveURI("#"))
                            );
                        } else if (is_array($instance->getValue())) { // don't need is_json_array because of previous elseif
                            $parser = $self->getValueOfProperty("parser");
                            return array_map(function ($prop) use ($parser, $self) {
                                return $parser($prop, $self);
                            }, $instance->getProperties());
                        }
                        //else
                        return "any";
                    },
                
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        $requiredTypes = $schema->getAttribute("type");
                        settype($requiredTypes, 'array');
                        
                        //for instances that are required to be a certain type
                        if ($instance->getValue() !== null && $requiredTypes && count($requiredTypes)) {
                            $typeValidators = $self->getValueOfProperty("typeValidators");
                            if (!$typeValidators) {
                                $typeValidators = array();
                            }
                            
                            //ensure that type matches for at least one of the required types
                            foreach ($requiredTypes as $x => $type) {
                                $type = $requiredTypes[$x];
                                if ($type instanceof JSONSchema) {
                                    $subreport = clone $report;
                                    if (!count($type->validate($instance, $subreport, $parent, $parentSchema, $name)->errors)) {
                                        return true;  //instance matches this schema
                                    }
                                } else {
                                    if (is_string($type) && isset($typeValidators[$type]) && is_callable($typeValidators[$type])) {
                                        if ($typeValidators[$type]($instance, $report)) {
                                            return true;  //type is valid
                                        }
                                    } else {
                                        return true;  //unknown types are assumed valid
                                    }
                                }
                            }
                            
                            //if we get to this point, type is invalid
                            $msgs = array_map(function($val) {
                                if (is_string($val)) {
                                    return $val;
                                }
                                return '[schema: ' . json_encode($val->getValue()) . ']';
                            }, $requiredTypes);
                            $report->addError($instance, $schema, "type", "Instance is not a required type: " .
                                              implode(', ', $msgs) . " [schema path: " . $instance->getPath() . "]", $requiredTypes);
                            return false;
                        }
                        //else, anything is allowed if no type is specified
                        return true;
                    },
                    
                    "typeValidators" => $this->TYPE_VALIDATORS
                ), // type
                
                "properties" => array(
                    "type" => "object",
                    "additionalProperties" => array('$ref' => "#"),
                    "optional" => true,
                    "default" => array(),
                    
                    "parser" => function ($instance, $self, $arg = null) {
                        $env = $instance->getEnvironment();
                        $selfEnv = $self->getEnvironment();
                        if (JS\is_json_object($instance->getValue())) {
                            if ($arg) {
                                return $env->createSchema($instance->getProperty($arg), $selfEnv->findSchema($self->resolveURI("#")));
                            } else {
                                $sch = $selfEnv->findSchema($self->resolveURI("#"));
                                return array_map(function ($instance) use ($sch, $env) {
                                    return $env->createSchema($instance, $sch);
                                }, $instance->getProperties());
                            }
                        }
                        //else
                        return array();
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        //this attribute is for object type instances only
                        if (JS\is_json_object($instance->getValue())) {
                            //for each property defined in the schema
                            $propertySchemas = $schema->getAttribute("properties");
                            foreach ($propertySchemas as $key => $val) {
                                if ($val) {
                                    //ensure that instance property is valid
                                    $val->validate($instance->getProperty($key), $report, $instance, $schema, $key);
                                }
                            }
                        }
                    }
                ),
                
                "items" => array(
                    "type" => array(array('$ref' => "#"), "array"),
                    "items" => array('$ref' => "#"),
                    "optional" => true,
                    "default" => array(),
                    
                    "parser" => function ($instance, $self) {
                        if (JS\is_json_object($instance->getValue())) {
                            return $instance->getEnvironment()->createSchema($instance, $self->getEnvironment()->findSchema($self->resolveURI("#")));
                        } else if (is_array($instance->getValue())) { // don't need is_json_array here because of previous if
                            $sch = $self->getEnvironment()->findSchema($self->resolveURI("#"));
                            return array_map(function ($instance) use ($sch){
                                return $instance->getEnvironment()->createSchema($instance, $sch);
                            }, $instance->getProperties());
                        }
                        //else
                        return $instance->getEnvironment()->createEmptySchema();
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        
                        if (JS\is_json_array($instance->getValue())) {
                            $properties = $instance->getProperties();
                            $items = $schema->getAttribute("items");
                            $additionalProperties = $schema->getAttribute("additionalProperties");
                            
                            if (is_array($items)) { // no need for is_json_array here, items is either 1 thing or an array of things
                                for ($x = 0, $xl = count($properties); $x < $xl; ++$x) {
                                    if (isset($items[$x])) {
                                        $itemSchema = $items[$x];
                                    } else {
                                        $itemSchema = $additionalProperties;
                                    }
                                    if ($itemSchema !== false) {
                                        $itemSchema->validate($properties[$x], $report, $instance, $schema, $x);
                                    } else {
                                        $report->addError($instance, $schema, "additionalProperties", "Additional items are not allowed [schema path: " .
                                                          $instance->getPath() . "]", $itemSchema);
                                    }
                                }
                            } else {
                                if ($items) {
                                    $itemSchema = $items;
                                } else {
                                    $itemSchema = $additionalProperties;
                                }
                                foreach ($properties as $x => $property) {
                                    $itemSchema->validate($property, $report, $instance, $schema, $x);
                                }
                            }
                        }
                    }
                ),
                
                "optional" => array(
                    "type" => "boolean",
                    "optional" => true,
                    "default" => false,
                    
                    "parser" => function ($instance, $self) {
                        return (bool) $instance->getValue();
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if ($instance->getValue() === null && !$schema->getAttribute("optional")) {
                            $report->addError($instance, $schema, "optional", "Property \"$name\" is required [schema path: " .
                                              $instance->getPath() . "]", false);
                        }
                    },
                    
                    "validationRequired" => true
                ),
                
                "additionalProperties" => array(
                    "type" => array(array('$ref' => "#"), "boolean"),
                    "optional" => true,
                    "default" => array(),
                    
                    "parser" => function ($instance, $self) {
                        if (JS\is_json_object($instance->getValue())) {
                            return $instance->getEnvironment()->createSchema($instance, $self->getEnvironment()->findSchema($self->resolveURI("#")));
                        } else if (is_bool($instance->getValue()) && $instance->getValue() === false) {
                            return false;
                        }
                        //else
                        return $instance->getEnvironment()->createEmptySchema();
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        //we only need to check against object types as arrays do their own checking on this property
                        if (JS\is_json_object($instance->getValue())) {
                            $additionalProperties = $schema->getAttribute("additionalProperties");
                            $props = $schema->getAttribute("properties");
                            if ($props) {
                                $propertySchemas = $props;
                            } else {
                                $propertySchemas = array();
                            }
                            $properties = $instance->getProperties();
                            foreach ($properties as $key => $val) {
                                if ($val && !isset($propertySchemas[$key])) {
                                    if ($additionalProperties instanceof JSONSchema) {
                                        $additionalProperties->validate($val, $report, $instance, $schema, $key);
                                    } else if ($additionalProperties === false) {
                                        $report->addError($instance, $schema, "additionalProperties",
                                                          "Additional properties are not allowed [schema path: " .
                                                          $instance->getPath() . "]", $additionalProperties);
                                    }
                                }
                            }
                        }
                    }
                ),
                
                "requires" => array(
                    "type" => array("string", array('$ref' => "#")),
                    "optional" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (is_string($instance->getValue())) {
                            return $instance->getValue();
                        } else if (JS\is_json_object($instance->getValue())) {
                            return $instance->getEnvironment()->createSchema($instance, $self->getEnvironment()->findSchema($self->resolveURI("#")));
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if ($instance->getValue() !== null && $parent && $parent->getValue() !== null) {
                            $requires = $schema->getAttribute("requires");
                            if (is_string($requires)) {
                                if ($parent->getProperty($requires)->getValue() === null) {
                                    $report->addError($instance, $schema, "requires", 'Property requires sibling property "' . $requires .
                                                      '" [schema path: ' .
                                                      $instance->getPath() . ']',
                                                      $requires);
                                }
                            } else if ($requires instanceof JSONSchema) {
                                $requires->validate($parent, $report);  //WATCH: A "requires" schema does not support the "requires" attribute
                            }
                        }
                    }
                ),
                
                "minimum" => array(
                    "type" => "number",
                    "optional" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (is_numeric($instance->getValue())) {
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_numeric($instance->getValue())) {
                            $minimum = $schema->getAttribute("minimum");
                            $minimumCanEqual = $schema->getAttribute("minimumCanEqual");
                            if (is_numeric($minimum) && ($instance->getValue() < $minimum ||
                                                         ($minimumCanEqual === false && $instance->getValue() === $minimum))) {
                                $report->addError($instance, $schema, "minimum", "Number is less than the required minimum value [schema path: " .
                                                  $instance->getPath() . "]", $minimum);
                            }
                        }
                    }
                ),
                
                "maximum" => array(
                    "type" => "number",
                    "optional" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (is_numeric($instance->getValue())) {
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_numeric($instance->getValue())) {
                            $maximum = $schema->getAttribute("maximum");
                            $maximumCanEqual = $schema->getAttribute("maximumCanEqual");
                            if (is_numeric($maximum) && ($instance->getValue() > $maximum ||
                                                         ($maximumCanEqual === false && $instance->getValue() === $maximum))) {
                                $report->addError($instance, $schema, "maximum", "Number is greater than the required maximum value [schema path: " .
                                                  $instance->getPath() . "]", $maximum);
                            }
                        }
                    }
                ),
                
                "minimumCanEqual" => array(
                    "type" => "boolean",
                    "optional" => true,
                    "requires" => "minimum",
                    "default" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (is_bool($instance->getValue())) {
                            return $instance->getValue();
                        }
                        //else
                        return true;
                    }
                ),
                
                "maximumCanEqual" => array(
                    "type" => "boolean",
                    "optional" => true,
                    "requires" => "maximum",
                    "default" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (is_bool($instance->getValue())) {
                            return $instance->getValue();
                        }
                        //else
                        return true;
                    }
                ),
                
                "minItems" => array(
                    "type" => "integer",
                    "optional" => true,
                    "minimum" => 0,
                    "default" => 0,
                    
                    "parser" => function ($instance, $self) {
                        if (is_numeric($instance->getValue())) {
                            return $instance->getValue();
                        }
                        //else
                        return 0;
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (JS\is_json_array($instance->getValue())) {
                            $minItems = $schema->getAttribute("minItems");
                            if (is_numeric($minItems) && count($instance->getProperties()) < $minItems) {
                                $report->addError($instance, $schema, "minItems", "The number of items is less than the required minimum [schema path: " .
                                                  $instance->getPath() . "]", $minItems);
                            }
                        }
                    }
                ),
                
                "maxItems" => array(
                    "type" => "integer",
                    "optional" => true,
                    "minimum" => 0,
                    
                    "parser" => function ($instance, $self) {
                        if (is_numeric($instance->getValue())) {
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (JS\is_json_array($instance->getValue())) {
                            $maxItems = $schema->getAttribute("maxItems");
                            if (is_numeric($maxItems) && count($instance->getProperties()) > $maxItems) {
                                $report->addError($instance, $schema, "maxItems", "The number of items is greater than the required maximum [schema path: " .
                                                  $instance->getPath() . "]", $maxItems);
                            }
                        }
                    }
                ),
                
                "pattern" => array(
                    "type" => "string",
                    "optional" => true,
                    "format" => "regex",
                    
                    "parser" => function ($instance, $self) {
                        if (is_string($instance->getValue())) {
                            if (false === @preg_match('/' . str_replace('/', '\\/', $instance->getValue()) . '/', '')) {
                                return new Exception($instance->getValue());
                            }
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        $pattern = $schema->getAttribute("pattern");
                        if ($pattern instanceof Exception) {
                            $report->addError($instance, $schema, "pattern", "Schema has invalid pattern \"" .
                                              $pattern->getMessage() . "\" [schema path: " . $instance->getPath() . "]", $pattern);
                        } elseif (is_string($instance->getValue()) && $pattern &&
                                  !preg_match('/' . str_replace('/', '\\/', $pattern) . '/', $instance->getValue())) {
                            $report->addError($instance, $schema, "pattern", "String does not match pattern [schema path: " .
                                              $instance->getPath() . "]", $pattern);
                        }
                    }
                ),
                
                "minLength" => array(
                    "type" => "integer",
                    "optional" => true,
                    "minimum" => 0,
                    "default" => 0,
                    
                    "parser" => function ($instance, $self) {
                        if (is_numeric($instance->getValue())) {
                            return $instance->getValue();
                        }
                        //else
                        return 0;
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_string($instance->getValue())) {
                            $minLength = $schema->getAttribute("minLength");
                            if (is_numeric($minLength) && strlen($instance->getValue()) < $minLength) {
                                $report->addError($instance, $schema, "minLength", "String is less than the required minimum length [schema path: " .
                                                  $instance->getPath() . "]", $minLength);
                            }
                        }
                    }
                ),
                
                "maxLength" => array(
                    "type" => "integer",
                    "optional" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (is_numeric($instance->getValue())) {
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_string($instance->getValue())) {
                            $maxLength = $schema->getAttribute("maxLength");
                            if (is_numeric($maxLength) && strlen($instance->getValue()) > $maxLength) {
                                $report->addError($instance, $schema, "maxLength", "String is greater than the required maximum length [schema path: " .
                                                  $instance->getPath() . "]", $maxLength);
                            }
                        }
                    }
                ),
                
                "enum" => array(
                    "type" => "array",
                    "optional" => true,
                    "minItems" => 1,
                    "uniqueItems" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (JS\is_json_array($instance->getValue())) {
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (null !== $instance->getValue()) {
                            $enums = $schema->getAttribute("enum");
                            if ($enums) {
                                foreach ($enums as $enum) {
                                    if ($instance->equals($enum)) {
                                        return true;
                                    }
                                }
                                $toString = function ($instance) {
                                    $v = $instance;
                                    if ($v instanceof JSONInstance) {
                                        $v = $v->getValue();
                                    }
                                    switch (gettype($v)) {
                                        case 'string' :
                                            if (strlen($v) > 20) {
                                                $v = substr($v, 0, 20) . '...';
                                            }
                                            $inst = '"' . $v . '"';
                                            break;
                                        case 'integer' :
                                        case 'double' :
                                            $inst = $v;
                                            break;
                                        case 'boolean' :
                                            $inst = $v ? 'TRUE' : 'FALSE';
                                            break;
                                        case 'null' :
                                            $inst = 'NULL';
                                            break;
                                        case 'array' :
                                            $inst = '[array:';
                                            if (is_json_array($v)) {
                                                $inst .= json_encode($v) . ']';
                                                break;
                                            }
                                        case 'object' :
                                            $inst = '[object:' . json_encode($v) . ']';
                                            break;
                                        default :
                                            $inst = '[' . gettype($v) . ']';
                                    }
                                    return $inst;
                                };
                                $enums = array_map($toString, $enums);
                                $report->addError($instance, $schema, "enum", "Instance " .
                                                  $toString($instance) . " is not one of the possible values " .
                                                  implode(', ', $enums) . " [schema path: " .
                                                  $instance->getPath() . "]", $enums);
                            }
                        }
                    }
                ),
                
                "title" => array(
                    "type" => "string",
                    "optional" => true
                ),
                
                "description" => array(
                    "type" => "string",
                    "optional" => true
                ),
                
                "format" => array(
                    "type" => "string",
                    "optional" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (is_string($instance->getValue())) {
                            $sch = $instance->getEnvironment()->findSchema($instance->getValue());
                            if ($sch) {
                                // format may be a uri to a schema that defines that format
                                return $sch;
                            }
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_string($instance->getValue())) {
                            $format = $schema->getAttribute("format");
                            $formatValidators = $self->getValueOfProperty("formatValidators");
                            if (is_string($format) && isset($formatValidators[$format]) &&
                                is_callable($formatValidators[$format]) && !$formatValidators[$format]($instance, $report, $schema)) {
                                $report->addError($instance, $schema, "format", "String is not in the required format [schema path: " .
                                                  $instance->getPath() . "]", $format);
                            } elseif ($format instanceof JSONSchema) {
                                $errorcount = count($report->errors);
                                $format->validate($instance, $report, $parent, $parentSchema, $name);
                                if (count($report->errors) != $errorcount) {
                                    $report->addError($instance, $schema, "format", "String is not in the required format [schema path: " .
                                                      $instance->getPath() . "]", $format);
                                }
                            }
                        }

                    },
                    
                    "formatValidators" => array(
                        "regex" => function($instance, $report, $schema) {
                            if (false === @preg_match('/' . str_replace('/', '\\/', $instance->getValue()) . '/', '')) {
                                $report->addError($instance, $schema, "regex", "Regex \"" .
                                                  $instance->getValue() . "\"is not a valid regular expression [schema path: " .
                                                  $instance->getPath() . "]", $instance->getValue());
                                return false;
                            }
                            return true;
                        },
                        "date-time" => function($instance, $report, $schema) {
                            $zone = @date_default_timezone_get();
                            date_default_timezone_set('UTC');
                            if (preg_match('/^([0-9]{4})\-(0[1-9]|1[0-2])\-(0[1-9]|[1-2][0-9]|30|31)T(0[0-9]|1[0-9]|2[0-4])' .
                                           '\:([0-5][0-9])\:([0-5][0-9])Z$/', $instance->getValue(), $matches)) {
                                try {
                                    $a = new \DateTime($instance->getValue());
                                    if ($a->format('Y') == $matches[1] &&
                                        $a->format('m') == $matches[2] &&
                                        $a->format('d') == $matches[3] &&
                                        $a->format('H') == $matches[4] &&
                                        $a->format('i') == $matches[5] &&
                                        $a->format('s') == $matches[6]) {
                                        return true;
                                    }
                                } catch (Exception $e) {
                                }
                            }
                            $report->addError($instance, $schema, "date-time", "Date-time \"" .
                                              $instance->getValue() . "\" is not valid [schema path: " .
                                              $instance->getPath() . "]", $instance->getValue());
                            date_default_timezone_set($zone);
                            return false;
                        },
                        "date" => function($instance, $report, $schema) {
                            $zone = @date_default_timezone_get();
                            date_default_timezone_set('UTC');
                            if (preg_match('/^([0-9]{4})\-(0[1-9]|1[0-2])\-(0[1-9]|[1-2][0-9]|30|31)$/', $instance->getValue(), $matches)) {
                                try {
                                    $a = new \DateTime($instance->getValue());
                                    if ($a->format('Y') == $matches[1] &&
                                        $a->format('m') == $matches[2] &&
                                        $a->format('d') == $matches[3]) {
                                        return true;
                                    }
                                } catch (Exception $e) {
                                }
                            }
                            $report->addError($instance, $schema, "date", "Date \"" .
                                              $instance->getValue() . "\" is not valid [schema path: " .
                                              $instance->getPath() . "]", $instance->getValue());
                            date_default_timezone_set($zone);
                            return false;
                        },
                        "time" => function($instance, $report, $schema) {
                            $zone = @date_default_timezone_get();
                            date_default_timezone_set('UTC');
                            if (preg_match('/^(0[0-9]|1[0-9]|2[0-4])' .
                                           '\:([0-5][0-9])\:([0-5][0-9])$/', $instance->getValue(), $matches)) {
                                try {
                                    $a = new \DateTime($instance->getValue());
                                    if ($a->format('H') == $matches[1] &&
                                        $a->format('i') == $matches[2] &&
                                        $a->format('s') == $matches[3]) {
                                        return true;
                                    }
                                } catch (Exception $e) {
                                }
                            }
                            $report->addError($instance, $schema, "time", "Time \"" .
                                              $instance->getValue() . "\" is not valid [schema path: " .
                                              $instance->getPath() . "]", $instance->getValue());
                            date_default_timezone_set($zone);
                            return false;
                        },
                        "uri" => function ($instance, $report, $schema) {
                            if (preg_match(JS\URI\Cache::$regex['URI_REF'], $instance->getValue())) {
                                return true;
                            }
                            $report->addError($instance, $schema, "uri", "URI \"" .
                                              $instance->getValue() . "\" is not valid [schema path: " .
                                              $instance->getPath() . "]", $instance->getValue());
                            return false;
                        }
                    )
                ),
                
                "contentEncoding" => array(
                    "type" => "string",
                    "optional" => true
                ),
                
                "default" => array(
                    "type" => "any",
                    "optional" => true
                ),
                
                "maxDecimal" => array(
                    "type" => "integer",
                    "optional" => true,
                    "minimum" => 0,
                                    
                    "parser" => function ($instance, $self) {
                        if (is_numeric($instance->getValue())) {
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_numeric($instance->getValue())) {
                            $maxDecimal = $schema->getAttribute("maxDecimal");
                            if (is_int($maxDecimal)) {
                                $decimals = explode('.', ($instance->getValue() + 0) . '');
                                if (count($decimals) > 1) {
                                    $decimals = strlen($decimals[1]);
                                    if ($decimals > $maxDecimal) {
                                        $report->addError($instance, $schema, "maxDecimal",
                                                          "The number of decimal places of " .
                                                          $instance->getValue() . " is greater than the allowed maximum " .
                                                          $maxDecimal . " [schema path: " .
                                                          $instance->getPath() . "]", $maxDecimal);
                                    }
                                }
                            }
                        }
                    }
                ),
                
                "disallow" => array(
                    "type" => array("string", "array"),
                    "items" => array("type" => "string"),
                    "optional" => true,
                    "uniqueItems" => true,
                    
                    "parser" => function ($instance, $self) {
                        if (is_string($instance->getValue()) || JS\is_json_array($instance->getValue())) {
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        $disallowedTypes = $schema->getAttribute("disallow");
                        settype($disallowedTypes, 'array');
                        
                        //for instances that are required to be a certain type
                        if ($disallowedTypes && count($disallowedTypes)) {
                            if ($self->getValueOfProperty("typeValidators")) {
                                $typeValidators = $self->getValueOfProperty("typeValidators");
                            } else {
                                $typeValidators = array();
                            }
                            
                            //ensure that type matches for at least one of the required types
                            for ($x = 0, $xl = count($disallowedTypes); $x < $xl; ++$x) {
                                $key = $disallowedTypes[$x];
                                if (is_callable($typeValidators[$key])) {
                                    if ($typeValidators[$key]($instance, $report)) {
                                        $report->addError($instance, $schema, "disallow", "Instance " .
                                                          str_replace(array('NULL', 'double'), array('null', 'number'),
                                                                      gettype($instance->getValue())) . ' is a disallowed type, cannot be ' .
                                                          "any of: " . implode(", ", $disallowedTypes) . " [schema path: " .
                                                          $instance->getPath() . "]", $disallowedTypes);
                                        return false;
                                    }
                                } 
                                /*
                                else {
                                    $report->addError($instance, $schema, "disallow", "Instance may be a disallowed type [schema path: " .
                                    $instance->getPath() . "]", $disallowedTypes);
                                    return false;
                                }
                                */
                            }
                            
                            //if we get to this point, type is valid
                            return true;
                        }
                        //else, everything is allowed if no disallowed types are specified
                        return true;
                    },
                    
                    "typeValidators" => $this->TYPE_VALIDATORS
                ),
            
                "extends" => array(
                    "type" => array(array('$ref' => "#"), "array"),
                    "items" => array('$ref' => "#"),
                    "optional" => true,
                    "default" => array(),
                    
                    "parser" => function ($instance, $self) {
                        if (JS\is_json_object($instance->getValue())) {
                            return $instance->getEnvironment()->createSchema($instance, $self->getEnvironment()->findSchema($self->resolveURI("#")));
                        } else if (is_array($instance->getValue())) { // is_json_array not needed because of previous if
                            $sch = $self->getEnvironment()->findSchema($self->resolveURI("#"));
                            $env = $instance->getEnvironment();
                            return array_map(function ($instance) use ($sch, $env) {
                                return $env->createSchema($instance, $sch);
                            }, $instance->getProperties());
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        $extensions = $schema->getAttribute("extends");
                        if ($extensions) {
                            if ($extensions instanceof JSONSchema) {
                                $extensions->validate($instance, $report, $parent, $parentSchema, $name);
                            } else if (JS\is_json_array($extensions)) {
                                foreach ($extensions as $extension) {
                                    $extension->validate($instance, $report, $parent, $parentSchema, $name);
                                }
                            }
                        }
                    }
                )
            ),
            
            "optional" => true,
            "default" => array(),
            "fragmentResolution" => "dot-delimited",
            
            "parser" => function ($instance, $self) {
                if (JS\is_json_object($instance->getValue())) {
                    return $instance->getEnvironment()->createSchema($instance, $self);
                }
            },
            
            "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                $propNames = $schema->getPropertyNames();
                $attributeSchemas = $self->getAttribute("properties");
                
                foreach ($attributeSchemas as $x => $val) {
                    if ($val->getValueOfProperty("validationRequired")) {
                        $propNames[] = $x;
                    }
                }
                $propNames = array_unique($propNames);
                
                foreach ($propNames as $property) {
                    if (!isset($attributeSchemas[$property])) {
                        continue;
                    }
                    $validator = $attributeSchemas[$property]->getValueOfProperty("validator");
                    if (is_callable($validator)) {
                        $validator($instance, $schema, $attributeSchemas[$property], $report, $parent, $parentSchema, $name);
                    }
                }
            },
                    
            "initializer" => function ($instance) {
                do {
                    //if there is a link to the full representation, replace instance
                    $link = $instance->getSchema()->getLink("full", $instance);
                    $sch = $instance->getEnvironment()->getSchemas();
                    if ($link && $instance->getUri() !== $link && isset($sch[$link])) {
                        $instance = $sch[$link];
                        return $instance;  //retrieved schemas are guaranteed to be initialized
                    }
                    
                    //if there is a link to a different schema, update instance
                    $link = $instance->getSchema()->getLink("describedby", $instance);
                    if ($link && $instance->getSchema()->getUri() !== $link && isset($sch[$link])) {
                        $instance->setSchema($sch[$link]);
                        continue;  //start over
                    }
                    
                    //extend schema
                    $extension = $instance->getAttribute("extends");
                    if ($extension instanceof JSONSchema) {
                        $extended = JSV::inherits($extension, $instance, true);
                        
                        $instance = $instance->getEnvironment()->createSchema($extended, $instance->getSchema(), $instance->getUri());
                    }
                    
                    break;  //get out of the loop
                } while (true);
        
                //if instance has a URI link to itself, update it's own URI
                $link = $instance->getSchema()->getLink("self", $instance);
                if ($link) {
                    $instance->setUri($link);
                }
                
                return $instance;
            }
        );
    }

    function initializeSchema($uri = "http://json-schema.org/schema#")
    {
        $this->SCHEMA = $this->ENVIRONMENT->createSchema($this->getSchemaArray(), true, $uri);
    }

    protected function getHyperSchemaArray()
    {
        return array(
            '$schema' => "http://json-schema.org/hyper-schema#",
            "id" => "http://json-schema.org/hyper-schema#",
        
            "properties" => array(
                "links" => array(
                    "type" => "array",
                    "items" => array('$ref' => "links#"),
                    "optional" => true,
                    
                    "parser" => function ($instance, $self, $arg = null) {
                        $linkSchemaURI = $self->getValueOfProperty("items");
                        $linkSchemaURI = $linkSchemaURI['$ref'];
                        $linkSchema = $self->getEnvironment()->findSchema($linkSchemaURI);
                        $linkParser = $linkSchema ? $linkSchema->getValueOfProperty("parser") : null;
                        settype($arg, 'array');
                        $arg = array_values($arg);
                        
                        if (is_callable($linkParser)) {
                            $links = array_map(function ($link) use ($linkParser) {
                                return $linkParser($link, $linkSchema);
                            }, $instance->getProperties());
                        } else {
                            $links = $instance->getValue();
                            settype($links, 'array');
                            $links = array_values($links);
                        }
                        
                        if (isset($arg[0])) {
                            $links = array_values(array_filter($links, function ($link) use ($arg) {
                                return $link["rel"] === $arg[0];
                            }));
                        }
                        
                        if (isset($arg[1])) {
                            $selfReferenceVariable = $self->getValueOfProperty("selfReferenceVariable");
                            $links = array_map(function ($link) use ($arg, $selfReferenceVariable) {
                                $value = null;
                                $instance = $arg[1];
                                $href = $link["href"];
                                $href = preg_replace_callback('/\{(.+)\}/', function ($matches) use ($instance, $selfReferenceVariable) {
                                    $p1 = $matches[1];
                                    if ($p1 === $selfReferenceVariable) {
                                        $value = $instance->getValue();
                                    } else {
                                        $value = $instance->getValueOfProperty($p1);
                                    }
                                    return $value !== null ? $value . '' : "";
                                }, $href);
                                return $href ? JSV::formatURI($instance->resolveURI($href)) : $href;
                            }, $links);
                        }
                        
                        return $links;
                    },
                    "selfReferenceVariable" => "-this"
                ),
                
                "fragmentResolution" => array(
                    "type" => "string",
                    "optional" => true,
                    "default" => "dot-delimited"
                ),
                
                "root" => array(
                    "type" => "boolean",
                    "optional" => true,
                    "default" => false
                ),
                
                "readonly" => array(
                    "type" => "boolean",
                    "optional" => true,
                    "default" => false
                ),
                
                "pathStart" => array(
                    "type" => "string",
                    "optional" => true,
                    "format" => "uri",
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (null !== $instance->getValue()) {
                            $pathStart = $schema->getAttribute("pathStart");
                            if (is_string($pathStart)) {
                                //TODO: Find out what pathStart is relative to
                                if (strpos($instance->getURI(), $pathStart) !== 0) {
                                    $report->addError($instance, $schema, "pathStart", "Instance's URI does not start with " . $pathStart .
                                                      " [schema path: " . $instance->getPath() . "]", $pathStart);
                                }
                            }
                        }
                    }
                ),
                
                "mediaType" => array(
                    "type" => "string",
                    "optional" => true,
                    "format" => "media-type"
                ),
                
                "alternate" => array(
                    "type" => "array",
                    "items" => array('$ref' => "#"),
                    "optional" => true
                )
            ),
            
            "links" => array(
                array(
                    "href" => '{$ref}',
                    "rel" => "full"
                ),
                
                array(
                    "href" => '{$schema}',
                    "rel" => "describedby"
                ),
                
                array(
                    "href" => "{id}",
                    "rel" => "self"
                )
            )//,
            
            //not needed as JSV->inherits does the job for us
            //"extends" => array('$ref' => "http://json-schema.org/schema#"}
        );
    }

    function initializeHyperSchema($uri1 = '', $uri2 = "http://json-schema.org/hyper-schema#")
    {
        $hyperschema = $this->ENVIRONMENT->createSchema($this->getHyperSchemaArray(), $this->SCHEMA, $uri1);
        $this->HYPERSCHEMA = $this->ENVIRONMENT->createSchema(JSV::inherits($this->SCHEMA, $hyperschema, true),
                                                              true,
                                                              $uri2);
        
        $this->ENVIRONMENT->setOption("defaultSchemaURI", $uri2);
    }

    function getLinksArray()
    {
        return array(
            '$schema' => "http://json-schema.org/hyper-schema#",
            "id" => "http://json-schema.org/links#",
            "type" => "object",
            
            "properties" => array(
                "href" => array(
                    "type" => "string"
                ),
                
                "rel" => array(
                    "type" => "string"
                ),
                
                "method" => array(
                    "type" => "string",
                    "default" => "GET",
                    "optional" => true
                ),
                
                "enctype" => array(
                    "type" => "string",
                    "requires" => "method",
                    "optional" => true
                ),
                
                "properties" => array(
                    "type" => "object",
                    "additionalProperties" => array('$ref' => "hyper-schema#"),
                    "optional" => true,
                    
                    "parser" => function ($instance, $self, $arg = null) {
                        $env = $instance->getEnvironment();
                        $selfEnv = $self->getEnvironment();
                        $additionalPropertiesSchemaURI = $self->getValueOfProperty("additionalProperties");
                        $additionalPropertiesSchemaURI = $additionalPropertiesSchemaURI['$ref'];
                        if (JS\is_json_object($instance->getValue())) {
                            if ($arg) {
                                return $env->createSchema($instance->getProperty($arg),
                                                          $selfEnv->findSchema($self->resolveURI($additionalPropertiesSchemaURI)));
                            } else {
                                $sch = $selfEnv->findSchema($self->resolveURI($additionalPropertiesSchemaURI));
                                return array_map(function ($instance) use ($env, $sch) {
                                    return $env->createSchema($instance, $sch);
                                }, $instance->getProperties());
                            }
                        }
                    }
                )
            ),
            
            "parser" => function ($instance, $self) {
                $selfProperties = $self->getProperty("properties");
                if (JS\is_json_object($instance)) {
                    $props = $instance->getProperties();
                    array_walk($props, function (&$property, $key) use ($selfProperties) {
                        $propertySchema = $selfProperties->getProperty($key);
                        $parser = $propertySchema ? $propertySchema->getValueOfProperty("parser") : null;
                        if (is_callable($parser)) {
                            $property = $parser($property, $propertySchema);
                        }
                        //else
                        $property = $property->getValue();
                    });
                    return $props;
                }
                return $instance->getValue();
            }
        );
    }
    function initializeLinks($uri = "http://json-schema.org/links#")
    {
        $this->LINKS = $this->ENVIRONMENT->createSchema($this->getLinksArray(), $this->HYPERSCHEMA, $uri);
    }

    /**
     * if the search path key is the same as newkey, we will instead replace the schema item
     * if $before is set to the string "replace" we will remove the old key and replace it with the new one
     */
    function insert($schema, $path, $newkey, $newvalue, $before = true, $origpath = '')
    {
        if (!$origpath) {
            $origpath = $path;
        }
        if (strpos($path, '/')) {
            // still have to traverse deeper in the tree
            $path = explode('/', $path);
            $marker = array_shift($path);
            $path = implode('/', $path);
            $newschema = array();
            $found = false;
            foreach ($schema as $key => $value) {
                if ($key == $marker) {
                    $found = true;
                    $newschema[$key] = $this->insert($value, $path, $newkey, $newvalue, $before, $origpath);
                    continue;
                }
                $newschema[$key] = $value;
            }
            if (!$found) {
                throw new Exception('Error: path key ' . $marker . ' was not found in ' . $path . ' component of ' . $origpath);
            }
            return $newschema;
        }
        // this is where we will insert
        $newschema = array();
        foreach ($schema as $key => $value) {
            if ($key != $path) {
                $newschema[$key] = $value;
                continue;
            }
            $found = true;
            if ($key == $newkey || $before === 'replace') {
                $newschema[$newkey] = $newvalue;
                continue;
            }
            if ($before) {
                $newschema[$newkey] = $newvalue;
                $newschema[$key] = $value;
            } else {
                $newschema[$key] = $value;
                $newschema[$newkey] = $newvalue;
            }
        }
        if (!$found) {
            throw new Exception('Error: path key ' . $path . ' was not found in ' . $path . ' component of ' . $origpath);
        }
        return $newschema;
    }
}
