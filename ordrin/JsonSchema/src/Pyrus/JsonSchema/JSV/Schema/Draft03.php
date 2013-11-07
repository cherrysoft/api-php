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

use Pyrus\JsonSchema\JSV\Exception, Pyrus\JsonSchema\JSV\ValidationException, Pyrus\JsonSchema\JSV\JSONInstance, Pyrus\JsonSchema\JSV\JSONSchema,
    Pyrus\JsonSchema\JSV\Report, Pyrus\JsonSchema\JSV\URI, Pyrus\JsonSchema\JSV\EnvironmentOptions, Pyrus\JsonSchema\JSV\Environment,
    Pyrus\JsonSchema\JSV, Pyrus\JsonSchema as JS;


class Draft03 extends Draft02
{
    var
        $draft1,
        $draft2;

    function __construct($register = true)
    {
        $this->draft1 = new Draft03\Draft01();
        $this->draft2 = new Draft03\Draft02();

        return parent::__construct($register);
    }

    function initializeEnvironment()
    {
        $this->ENVIRONMENT = new Environment();
        $this->ENVIRONMENT->setOption("validateReferences", true);
        $this->ENVIRONMENT->setOption("defaultSchemaURI", "http://json-schema.org/draft-03/schema#");  //update later
        
        //prevent reference errors
        $this->ENVIRONMENT->createSchema(array(), true, "http://json-schema.org/draft-03/schema#");
        $this->ENVIRONMENT->createSchema(array(), true, "http://json-schema.org/draft-03/hyper-schema#");
        $this->ENVIRONMENT->createSchema(array(), true, "http://json-schema.org/draft-03/links#");
    }

    function initializeSchema($uri = "http://json-schema.org/draft-03/schema#")
    {
        return parent::initializeSchema($uri);
    }

    function initializeHyperSchema($uri1 = "http://json-schema.org/draft-03/hyper-schema#", $uri2 = "http://json-schema.org/draft-03/hyper-schema#")
    {
        return parent::initializeHyperSchema($uri1, $uri2);
    }

    function initializeLinks($uri = "http://json-schema.org/draft-03/links#")
    {
        return parent::initializeLinks($uri);
    }

    function registerSchemas()
    {
        //We need to reinitialize these schemas as they reference each other
        $this->HYPERSCHEMA = $this->ENVIRONMENT->createSchema($this->HYPERSCHEMA->getValue(), $this->HYPERSCHEMA,
                                                              "http://json-schema.org/draft-03/hyper-schema#");
        
        $this->ENVIRONMENT->setOption("latestJSONSchemaSchemaURI", "http://json-schema.org/draft-03/schema#");
        $this->ENVIRONMENT->setOption("latestJSONSchemaHyperSchemaURI", "http://json-schema.org/draft-03/hyper-schema#");
        $this->ENVIRONMENT->setOption("latestJSONSchemaLinksURI", "http://json-schema.org/draft-03/links#");
        
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
        JSV::registerEnvironment("json-schema-draft-03", $this->ENVIRONMENT);
        if (!JSV::getDefaultEnvironmentID() || JSV::getDefaultEnvironmentID() === "json-schema-draft-01"
            || JSV::getDefaultEnvironmentID() === "json-schema-draft-02") {
            JSV::setDefaultEnvironmentID("json-schema-draft-03");
        }
    }

    function getSchemaArray()
    {
        $SCHEMA_02_JSON = $this->draft2->getSchemaArray();
        $disallowparser = $SCHEMA_02_JSON["properties"]["type"]["parser"];
        $propertiesparser = $SCHEMA_02_JSON["properties"]["properties"]["parser"];
        $additionalparser = $SCHEMA_02_JSON["properties"]["additionalProperties"]["parser"];
        return JSV::inherits($SCHEMA_02_JSON, array(
            '$schema' => "http://json-schema.org/draft-03/schema#",
            "id" => "http://json-schema.org/draft-03/schema#",
            
            "properties" => array(
                "patternProperties" => array(
                    "type" => "object",
                    "additionalProperties" => array('$ref' => "#"),
                    "default" => array(),
                    
                    "parser" => $propertiesparser,
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (JS\is_json_object($instance->getValue())) {
                            $matchedProperties = JSV::getMatchedPatternProperties($instance, $schema, $report, $self);
                            foreach ($matchedProperties as $key => $val) {
                                $x = count($val);
                                while ($x--) {
                                    $val[$x]->validate($instance->getProperty($key), $report, $instance, $schema, $key);
                                }
                            }
                        }
                    }
                ),
                
                "additionalProperties" => array(
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (JS\is_json_object($instance->getValue())) {
                            $additionalProperties = $schema->getAttribute("additionalProperties");
                            $propertySchemas = $schema->getAttribute("properties");
                            if (!$propertySchemas) {
                                $propertySchemas = array();
                            }
                            $properties = $instance->getProperties();
                            $matchedProperties = JSV::getMatchedPatternProperties($instance, $schema);
                            foreach ($properties as $key => $val) {
                                if ($val && !isset($propertySchemas[$key]) && !isset($matchedProperties[$key])) {
                                    if ($additionalProperties instanceof JSONSchema) {
                                        $additionalProperties->validate($val, $report, $instance, $schema, $key);
                                    } else if ($additionalProperties === false) {
                                        $report->addError($instance, $schema, "additionalProperties",
                                                          "Additional properties are not allowed [schema path: " . $instance->getPath() . "]", $additionalProperties);
                                    }
                                }
                            }
                        }
                    }
                ),
                
                "items" => array(
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (JS\is_json_array($instance->getValue())) {
                            $properties = $instance->getProperties();
                            $items = $schema->getAttribute("items");
                            $additionalItems = $schema->getAttribute("additionalItems");
                            
                            if (is_array($items)) {
                                for ($x = 0, $xl = count($properties); $x < $xl; ++$x) {
                                    if (isset($items[$x])) {
                                        $itemSchema = $items[$x];
                                    } else {
                                        $itemSchema = $additionalItems;
                                    }
                                    if (is_callabel($itemSchema) && is_object($itemSchema)) {
                                        $itemSchema->validate($properties[$x], $report, $instance, $schema, $x);
                                    } else {
                                        $report->addError($instance, $schema, "additionalItems", "Additional items are not allowed [schema path: " .
                                                          $instance->getPath() . "]", $itemSchema);
                                    }
                                }
                            } else {
                                if ($items) {
                                    $itemSchema = $items;
                                } else {
                                    $itemSchema = $additionalItems;
                                }
                                for ($x = 0, $xl = $properties->length(); $x < $xl; ++$x) {
                                    $itemSchema->validate($properties[$x], $report, $instance, $schema, $x);
                                }
                            }
                        }
                    }
                ),
                
                "additionalItems" => array(
                    "type" => array(array('$ref' => "#"), "boolean"),
                    "default" => array(),
                    
                    "parser" => $additionalparser,
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        //only validate if the "items" attribute is undefined
                        if (JS\is_json_array($instance->getValue()) && null === $schema->getProperty("items")) {
                            $additionalItems = $schema->getAttribute("additionalItems");
                            $properties = $instance->getProperties();
                            
                            if ($additionalItems !== false) {
                                for ($x = 0, $xl = count($properties); $x < $xl; ++$x) {
                                    $additionalItems->validate($properties[$x], $report, $instance, $schema, $x);
                                }
                            } else if (count($properties)) {
                                $report->addError($instance, $schema, "additionalItems", "Additional items are not allowed [schema path: " .
                                                  $instance->getPath() . "]", $additionalItems);
                            }
                        }
                    }
                ),
                
                "optional" => array(
                    "validationRequired" => false,
                    "deprecated" => true
                ),
                
                "fksrequired" => array(
                    "type" => "boolean",
                    "default" => false,
                    
                    "parser" => function ($instance, $self) {
                        return (bool) $instance->getValue();
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (null === $instance->getValue() && $schema->getAttribute("required")) {
                            $report->addError($instance, $schema, "required", "Property is required [schema path: " .
                                              $instance->getPath() . "]", true);
                        }
                    }
                ),
                
                "requires" => array(
                    "deprecated" => true
                ),
                
                "dependencies" => array(
                    "type" => "object",
                    "additionalProperties" => array(
                        "type" => array("string", "array", array('$ref' => "#")),
                        "items" => array(
                            "type" => "string"
                        )
                    ),
                    "default" => array(),
                    
                    "parser" => function ($instance, $self, $arg = null) {
                        $parseProperty = function(JSONInstance $property) {
                            if (is_string($property->getValue()) || JS\is_json_array($property->getValue())) {
                                return $property->getValue();
                            } else if (JS\is_json_object($property->getValue())) {
                                return $property->getEnvironment()->createSchema($property, $self->getEnvironment()
                                                                                 ->findSchema($self->resolveURI("#")));
                            }
                        };
                        
                        if (JS\is_json_object($instance->getValue())) {
                            if ($arg) {
                                return $parseProperty($instance->getProperty($arg));
                            } else {
                                return array_map($parseProperty, $instance->getProperties());
                            }
                        }
                        //else
                        return array();
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (JS\is_json_object($instance->getValue())) {
                            $dependencies = $schema->getAttribute("dependencies");
                            foreach ($dependencies as $key => $dependency) {
                                if ($instance->getProperty($key) !== null) {
                                    if (is_string($dependency)) {
                                        if ($instance->getProperty($dependency) === null) {
                                            $report->addError($instance, $schema, "dependencies", 'Property "' . $key .
                                                              '" requires sibling property "' . $dependency . '"' .
                                                              " [schema path: " . $instance->getPath() . "]", $dependencies);
                                        }
                                    } else if (JS\is_json_array($dependency)) {
                                        for ($x = 0, $xl = count($dependency); $x < $xl; ++$x) {
                                            if ($instance->getProperty($dependency[$x]) === null) {
                                                $report->addError($instance, $schema, "dependencies", 'Property "' .
                                                                  $key . '" requires sibling property "' . $dependency[$x] . '"' .
                                                                  " [schema path: " . $instance->getPath() . "]", $dependencies);
                                            }
                                        }
                                    } else if ($dependency instanceof JSONSchema) {
                                        $dependency->validate($instance, $report);
                                    }
                                }
                            }
                        }
                    }
                ),
                
                "minimumCanEqual" => array(
                    "deprecated" => true
                ),
                
                "maximumCanEqual" => array(
                    "deprecated" => true
                ),
                
                "exclusiveMinimum" => array(
                    "type" => "boolean",
                    "default" => false,
                    
                    "parser" => function ($instance, $self) {
                        return (bool) $instance->getValue();
                    }
                ),
                
                "exclusiveMaximum" => array(
                    "type" => "boolean",
                    "default" => false,
                    
                    "parser" => function ($instance, $self) {
                        return (bool) $instance->getValue();
                    }
                ),
                
                "minimum" => array(
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_numeric($instance->getValue())) {
                            $minimum = $schema->getAttribute("minimum");
                            $exclusiveMinimum = $schema->getAttribute("exclusiveMinimum");
                            if (!$exclusiveMinimum) {
                                $exclusiveMinimum = (!$instance->getEnvironment()->getOption("strict") && !$schema->getAttribute("minimumCanEqual"));
                            }
                            if (is_numeric($minimum) && ($instance->getValue() < $minimum ||
                                                         ($exclusiveMinimum === true && $instance->getValue() === $minimum))) {
                                $report->addError($instance, $schema, "minimum", "Number is less than the required minimum value" .
                                                  " [schema path: " . $instance->getPath() . "]", $minimum);
                            }
                        }
                    }
                ),
                
                "maximum" => array(
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_numeric($instance->getValue())) {
                            $maximum = $schema->getAttribute("maximum");
                            $exclusiveMaximum = $schema->getAttribute("exclusiveMaximum");
                            if (!$exclusiveMaximum) {
                                $exclusiveMaximum = (!$instance->getEnvironment()->getOption("strict") && !$schema->getAttribute("maximumCanEqual"));
                            }
                            if (is_numeric($maximum) && ($instance->getValue() > $maximum ||
                                                         ($exclusiveMaximum === true && $instance->getValue() === $maximum))) {
                                $report->addError($instance, $schema, "maximum", "Number is greater than the required maximum value" .
                                                  " [schema path: " . $instance->getPath() . "]", $maximum);
                            }
                        }
                    }
                ),
                
                "contentEncoding" => array(
                    "deprecated" => true
                ),
                
                "divisibleBy" => array(
                    "exclusiveMinimum" => true
                ),
                
                "disallow" => array(
                    "items" => array(
                        "type" => array("string", array('$ref' => "#"))
                    ),
                    
                    "parser" => $disallowparser
                ),
                
                "id" => array(
                    "type" => "string",
                    "format" => "uri"
                ),
                
                '$ref' => array(
                    "type" => "string",
                    "format" => "uri"
                ),
                
                '$schema' => array(
                    "type" => "string",
                    "format" => "uri"
                )
            ),
            
            "dependencies" => array(
                "exclusiveMinimum" => "minimum",
                "exclusiveMaximum" => "maximum"
            ),
            
            "initializer" => function ($instance) {
                $schemaLink = $instance->getValueOfProperty('$schema');
                $refLink = $instance->getValueOfProperty('$ref');
                $idLink = $instance->getValueOfProperty('id');
                $sch = $instance->getEnvironment()->getSchemas();
                $opts = $instance->getEnvironment()->getOptions();
                
                //if there is a link to a different schema, update instance
                if ($schemaLink) {
                    $link = $instance->resolveURI($schemaLink);
                    if ($link && $instance->getSchema()->getUri() !== $link) {
                        if ($sch[$link]) {
                            $instance->setSchema($sch[link]);
                            $initializer = $instance->getSchema()->getValueOfProperty("initializer");
                            if (is_callabel($initializer)) {
                                return $initializer($instance);  //this function will finish initialization
                            } else {
                                return $instance;  //no further initialization
                            }
                        } else if ($opts["validateReferences"]) {
                            throw new InitializationException($instance, $instance->getSchema(), '$schema', "Unknown schema reference", $link);
                        }
                    }
                }
                
                //if there is a link to the full representation, replace instance
                if ($refLink) {
                    $link = $instance->resolveURI($refLink);
                    if ($link && $instance->getUri() !== $link) {
                        if ($sch[$link]) {
                            $instance = $sch[link];
                            return $instance;  //retrieved schemas are guaranteed to be initialized
                        } else if ($opts["validateReferences"]) {
                            throw new InitializationException($instance, $instance->getSchema(), '$ref', "Unknown schema reference", $link);
                        }
                    }
                }
                
                //extend schema
                $extension = $instance->getAttribute("extends");
                if ($extension instanceof JSONSchema) {
                    $extended = JSV::inherits($extension, $instance, true);
                    $instance = $instance->getEnvironment()->createSchema($extended, $instance->getSchema(), $instance->getUri());
                }
        
                //if instance has a URI link to itself, update it's own URI
                if ($idLink) {
                    $link = $instance->resolveURI($idLink);
                    if (is_string($link)) {
                        $instance->setUri(JSV::formatURI($link));
                    }
                }
                
                return $instance;
            }
        ));
    }

    function getHyperSchemaArray()
    {
        $arr = $this->draft2->getHyperSchemaArray();
        $arr['$schema'] = "http://json-schema.org/draft-03/hyper-schema#";
        $arr['id'] = "http://json-schema.org/draft-03/hyper-schema#";
        $arr['properties']['links']['selfReferenceVariable'] = '@';
        $arr['properties']['root']['deprecated'] = true;
        $arr['properties']['contentEncoding']['deprecated'] = false;
        $arr['properties']['alternate']['deprecated'] = true;
        return $arr;
    }

    function getLinksArray()
    {
        $arr = $this->draft2->getLinksArray();
        $arr['$schema'] = "http://json-schema.org/draft-03/hyper-schema#";
        $arr['id'] = "http://json-schema.org/draft-03/links#";
        
        $arr['properties']['href']['required'] = true;
        $arr['properties']['format'] = 'link-description-object-template';
        $arr['properties']['rel']['required'] = true;
        $arr['properties']['properties']['deprecated'] = true;
        $arr['schema']['$ref'] = "http://json-schema.org/draft-03/hyper-schema#";
        return $arr;
    }
}
