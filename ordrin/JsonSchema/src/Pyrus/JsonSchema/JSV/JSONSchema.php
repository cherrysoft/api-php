<?php
/**
 * JSV: JSON Schema Validator
 * 
 * @fileOverview A JavaScript implementation of a extendable, fully compliant JSON Schema validator.
 * @author Gregory Beaver greg@chiaraquartet.net
 * @author Gary Court gary.court@gmail.com
 * @version 3.5
 * @see http://github.com/garycourt/uri-js Ported from JSV
 */

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

namespace Pyrus\JsonSchema\JSV;
use Pyrus\JsonSchema as JS, Pyrus\JsonSchema\JSV;
/**
 * This class binds a {@link JSONInstance} with a {@link JSONSchema} to provided context aware methods. 
 */
class JSONSchema extends JSONInstance
{
    protected $_schema;
    protected $_fd;
    protected $_attributes = array();
    /**
     * @param {Environment} env The environment this schema belongs to
     * @param {JSONInstance|Any} json The value of the schema
     * @param {String} [uri] The URI of the schema. If undefined, the URI will be a randomly generated UUID. 
     * @param {JSONSchema|Boolean} [schema] The schema to bind to the instance. If <code>undefined</code>, the environment's default schema will be used. If <code>true</code>, the instance's schema will be itself.
     * @extends JSONInstance
     */
    
    function __construct(Environment $env, $json, $uri = null, $schema = null) {
        parent::__construct($env, $json, $uri);
        
        if ($schema === true) {
            $this->_schema = $this;
        } elseif ($json instanceof JSONSchema && !($schema instanceof JSONSchema)) {
            $this->_schema = $json->getSchema();  //TODO: Make sure cross environments don't mess everything up
        } else {
            if ($schema instanceof JSONSchema) {
                $this->_schema = $schema;
            } else {
                $default = $this->_env->getDefaultSchema();
                if ($default) {
                    $this->_schema = $default;
                } else {
                    $this->_schema = $this->createEmptySchema($this->_env);
                }
            }
        }
        
        //determine fragment delimiter from schema
        $fr = $this->_schema->getValueOfProperty("fragmentResolution");
        if ($fr === "dot-delimited") {
            $this->_fd = ".";
        } else if ($fr === "slash-delimited") {
            $this->_fd = "/";
        }
    }

    function __clone()
    {
        $this->_attributes = JSV::dirtyClone($this->_attributes);
        parent::__clone();
    }

    /**
     * Creates an empty schema.
     * 
     * @param {Environment} env The environment of the schema
     * @returns {JSONSchema} The empty schema, who's schema is itself.
     */
    
    static function createEmptySchema(Environment $env)
    {
        static $race = 0;
        if ($race) return $this;
        $race = 1; // avoid race condition: endless loop
        $schema = new self($env, array(), null);
        $race = 0;
        $schema->setSchema($schema);
        return $schema;
    }
    
    function setSchema(JSONSchema $schema)
    {
        $this->_schema = $schema;
        $fr = $this->_schema->getValueOfProperty("fragmentResolution");
        if ($fr === "dot-delimited") {
            $this->_fd = ".";
        } else if ($fr === "slash-delimited") {
            $this->_fd = "/";
        }
    }
    
    /**
     * Returns the schema of the schema.
     * 
     * @returns {JSONSchema} The schema of the schema
     */
    
    function getSchema()
    {
        return $this->_schema;
    }
    
    /**
     * Returns the value of the provided attribute name.
     * <p>
     * This method is different from {@link JSONInstance#getProperty} as the named property 
     * is converted using a parser defined by the schema's schema before being returned. This
     * makes the return value of this method attribute dependent.
     * </p>
     * 
     * @param {String} key The name of the attribute
     * @param {Any} [arg] Some attribute parsers accept special arguments for returning resolved values. This is attribute dependent.
     * @returns {JSONSchema|Any} The value of the attribute
     */
    
    function getAttribute($key, $arg = null)
    {
        if (!$arg && array_key_exists($key, $this->_attributes)) {
            return $this->_attributes[$key];
        }
        
        $schemaProperty = $this->_schema->getProperty("properties")->getProperty($key);
        $parser = $schemaProperty->getValueOfProperty("parser");
        $property = $this->getProperty($key);
        if (is_callable($parser)) {
            if (is_object($parser)) {
                $result = $parser($property, $schemaProperty, $arg);
            } else {
                call_user_func($parser, $property, $schemaProperty, $arg);
            }
            if (!$arg && $this->_attributes) {
                $this->_attributes[$key] = $result;
            }
            return $result;
        }
        //else
        return $property->getValue();
    }
    
    /**
     * Returns all the attributes of the schema.
     * 
     * @returns {Object} A map of all parsed attribute values
     */
    
    function getAttributes()
    {
        if (!count($this->_attributes) && JS\is_json_object($this->_value)) {
            $properties = $this->getProperties();
            $schemaProperties = $this->_schema->getProperty("properties");
            $this->_attributes = array();
            foreach ($properties as $key => $val) {
                $parser = null;
                if (count($schemaProperties)) {
                    $schemaProperty = $schemaProperties->getProperty($key);
                    if ($schemaProperty) {
                        $parser = $schemaProperty->getValueOfProperty("parser");
                    }
                }
                if (is_callable($parser)) {
                    if (is_object($parser)) {
                        $this->_attributes[$key] = $parser($val, $schemaProperty);
                    } else {
                        $this->_attributes[$key] = call_user_func($parser, $val, $schemaProperty);
                    }
                } else {
                    $this->_attributes[$key] = $val->getValue();
                }
            }
        }
        
        return $this->_attributes;
    }
    
    /**
     * Convenience method for retrieving a link or link object from a schema. 
     * This method is the same as calling <code>schema.getAttribute("links", [rel, instance])[0];</code>.
     * 
     * @param {String} rel The link relationship
     * @param {JSONInstance} [instance] The instance to resolve any URIs from
     * @returns {String|Object|undefined} If <code>instance</code> is provided, a string containing the resolve URI of the link is returned.
     *   If <code>instance</code> is not provided, a link object is returned with details of the link.
     *   If no link with the provided relationship exists, <code>undefined</code> is returned.
     * @see JSONSchema#getAttribute
     */
    
    function getLink($rel, JSONInstance $instance)
    {
        $schemaLinks = $this->getAttribute("links", array($rel, $instance));
        if ($schemaLinks && count($schemaLinks) && $schemaLinks[count($schemaLinks) - 1]) {
            return $schemaLinks[count($schemaLinks) - 1];
        }
    }
    
    /**
     * Validates the provided instance against the target schema and returns a {@link Report}.
     * 
     * @param {JSONInstance|Any} instance The instance to validate; may be a {@link JSONInstance} or any JavaScript value
     * @param {Report} [report] A {@link Report} to concatenate the result of the validation to. If <code>undefined</code>, a new {@link Report} is created. 
     * @param {JSONInstance} [parent] The parent/containing instance of the provided instance
     * @param {JSONSchema} [parentSchema] The schema of the parent/containing instance
     * @param {String} [name] The name of the parent object's property that references the instance
     * @returns {Report} The result of the validation
     */
    
    function validate($instance, Report $report = null, JSONInstance $parent = null, JSONSchema $parentSchema = null, $name = null)
    {
        $validator = $this->_schema->getValueOfProperty("validator");
        
        if (!($instance instanceof JSONInstance)) {
            $instance = $this->getEnvironment()->createInstance($instance);
        }
        
        if (!($report instanceof Report)) {
            $report = new Report();
        }
        
        if (is_callable($validator) && !$report->isValidatedBy($instance->getURI(), $this->getURI())) {
            $report->registerValidation($instance->getURI(), $this->getURI());
            $validator($instance, $this, $this->_schema, $report, $parent, $parentSchema, $name);
        }
        
        return $report;
    }
}
