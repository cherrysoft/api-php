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


class Draft02 extends Draft01
{
    function registerSchemas()
    {
        $this->SCHEMA = $this->ENVIRONMENT->createSchema($this->SCHEMA->getValue(), $this->HYPERSCHEMA, "http://json-schema.org/schema#");
        $this->HYPERSCHEMA = $this->ENVIRONMENT->createSchema($this->HYPERSCHEMA->getValue(), $this->HYPERSCHEMA, "http://json-schema.org/hyper-schema#");
        $this->LINKS = $this->ENVIRONMENT->createSchema($this->LINKS->getValue(), $this->HYPERSCHEMA, "http://json-schema.org/links#");

        JSV::registerEnvironment("json-schema-draft-02", $this->ENVIRONMENT);
        
        if (!JSV::getDefaultEnvironmentID() || JSV::getDefaultEnvironmentID() === "json-schema-draft-01") {
            JSV::setDefaultEnvironmentID("json-schema-draft-02");
        }
    }

    function initializeEnvironment()
    {
        $this->ENVIRONMENT = new Environment();
        $this->ENVIRONMENT->setOption("defaultFragmentDelimiter", "/");
        $this->ENVIRONMENT->setOption("defaultSchemaURI", "http://json-schema.org/schema#");  //updated later
    }

    function getSchemaArray()
    {
        $schema = parent::getSchemaArray();
        $schema = $this->insert($schema, 'properties/pattern', 'uniqueItems', array(
				"type" => "boolean",
				"optional" => true,
				"default" => false,
				
				"parser" => function ($instance, $self) {
					return (bool) $instance->getValue();
				},
				
				"validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
					if (JS\is_json_array($instance->getValue()) && $schema->getAttribute("uniqueItems")) {
						$value = $instance->getProperties();
						for ($x = 0, $xl = count($value) - 1; $x < $xl; ++$x) {
							for ($y = $x + 1, $yl = count($value); $y < $yl; ++$y) {
								if ($value[$x]->equals($value[$y])) {
									$report->addError($instance, $schema, "uniqueItems", "Array can only contain unique items [schema path: " .
                                                      $instance->getPath() . "]",
                                                      array('x' => $x, 'y' => $y));
								}
							}
						}
					}
				}
			));
        $schema = $this->insert($schema, 'properties/maxDecimal', 'divisibleBy', array(
                    "type" => "number", // was integer
                    "minimum" => 0,

                    "minimumCanEqual" => false, // new constraint
                    "optional" => true,
                                    
                    "parser" => function ($instance, $self) {
                        if (is_numeric($instance->getValue())) {
                            return $instance->getValue();
                        }
                    },
                    
                    "validator" => function ($instance, $schema, $self, $report, $parent, $parentSchema, $name) {
                        if (is_numeric($instance->getValue())) {
                            $divisor = $schema->getAttribute("divisibleBy");
                            if ($divisor === 0) {
                                $report->addError($instance, $schema, "divisibleBy", "Nothing is divisible by 0 [schema path: " .
                                                  $instance->getPath() . "]", $divisor);
                            } elseif (floor($instance->getValue() / $divisor) != $instance->getValue() / $divisor) {
                                $report->addError($instance, $schema, "divisibleBy", "Number is not divisible by " . $divisor .
                                                  " [schema path: " . $instance->getPath() . "]", $divisor);
                            }
                        }
                    }
                ), 'replace');
        $schema['fragmentResolution'] = 'slash-delimited';
        return $schema;
    }

    function getHyperSchemaArray()
    {
        $schema = parent::getHyperSchemaArray();
        $schema['properties']['fragmentResolution']['default'] = 'slash-delimited'; // was dot-delimited
        return $schema;
    }

    function getLinksArray()
    {
        $schema = parent::getLinksArray();
        $schema = $this->insert($schema, 'properties/method', 'targetSchema', array(
				'$ref' => "hyper-schema#",
				
				//need this here because parsers are run before links are resolved
				"parser" => $this->HYPERSCHEMA->getAttribute("parser")
			));
        return $schema;
    }
}