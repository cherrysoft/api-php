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
use Pyrus\JsonSchema\JSV, Pyrus\JsonSchema\is_json_object, Pyrus\JS\is_json_array;

class Environment
{
    protected $_schemas = array();
    protected $_options;
    protected $_id;
    /**
     * An Environment is a sandbox of schemas thats behavior is different from other environments.
     * 
     * @name Environment
     * @class
     */
    
    function __construct() {
        $this->_id = JSV::randomUUID();
        $this->_options = new EnvironmentOptions;
    }
    
    /**
     * Returns a clone of the target environment.
     * 
     * @returns {Environment} A new {@link Environment} that is a exact copy of the target environment 
     */
    
    function __clone()
    {
        $this->_options = clone $this->_options;
    }
    
    /**
     * Returns a new {@link JSONInstance} of the provided data.
     * 
     * @param {JSONInstance|Any} data The value of the instance
     * @param {String} [uri] The URI of the instance. If undefined, the URI will be a randomly generated UUID. 
     * @returns {JSONInstance} A new {@link JSONInstance} from the provided data
     */
    
    function createInstance($data, $uri = '')
    {
        $uri = JSV::formatURI($uri);
        
        if ($data instanceof JSONInstance && (!$uri || $data->getURI() === $uri)) {
            return $data;
        }
        //else
        $instance = new JSONInstance($this, $data, $uri);
        
        return $instance;
    }
    
    /**
     * Creates a new {@link JSONSchema} from the provided data, and registers it with the environment. 
     * 
     * @param {JSONInstance|Any} data The value of the schema
     * @param {JSONSchema|Boolean} [schema] The schema to bind to the instance. If <code>undefined</code>, the environment's default schema will be used. If <code>true</code>, the instance's schema will be itself.
     * @param {String} [uri] The URI of the schema. If undefined, the URI will be a randomly generated UUID. 
     * @returns {JSONSchema} A new {@link JSONSchema} from the provided data
     * @throws {InitializationError} If a schema that is not registered with the environment is referenced 
     */
    
    function createSchema($data, $schema = null, $uri = '')
    {
        $uri = JSV::formatURI($uri);
 
        if ($data instanceof JSONSchema && (!$uri || $data->getUri() === $uri) && (!$schema || $data->getSchema()->equals($schema))) {
            return $data;
        }
        
        $instance = new JSONSchema($this, $data, $uri, $schema);
        
        $initializer = $instance->getSchema()->getValueOfProperty("initializer");
        if (is_callable($initializer)) {
            if (is_object($initializer)) {
                $instance = $initializer($instance);
            } else {
                $instance = call_user_func($initializer, $instance);
            }
        }
        
        //register schema
        $this->_schemas[$instance->getUri()] = $instance;
        settype($uri, 'string');
        $this->_schemas[$uri] = $instance;
        
        //build & cache the rest of the schema
        $instance->getAttributes();
        
        return $instance;
    }
    
    /**
     * Creates an empty schema.
     * 
     * @param {Environment} env The environment of the schema
     * @returns {JSONSchema} The empty schema, who's schema is itself.
     */
    
    function createEmptySchema()
    {
        $schema = JSONSchema::createEmptySchema($this);
        $this->_schemas[$schema->getUri()] = $schema;
        return $schema;
    }
    
    /**
     * Returns the schema registered with the provided URI.
     * 
     * @param {String} uri The absolute URI of the required schema
     * @returns {JSONSchema|undefined} The request schema, or <code>undefined</code> if not found
     */
    
    function findSchema($uri)
    {
        if (!isset($this->_schemas[JSV::formatURI($uri)])) {
            return null;
        }
        return $this->_schemas[JSV::formatURI($uri)];
    }
    
    /**
     * Sets the specified environment option to the specified value.
     * 
     * @param {String} name The name of the environment option to set
     * @param {Any} value The new value of the environment option
     */
    
    function setOption($name, $value)
    {
        $this->_options->$name = $value;
    }
    
    /**
     * Returns the specified environment option.
     * 
     * @param {String} name The name of the environment option to set
     * @returns {Any} The value of the environment option
     */
    
    function getOption($name)
    {
        return $this->_options->$name;
    }
    
    /**
     * Sets the default fragment delimiter of the environment.
     * 
     * @deprecated Use {@link Environment#setOption} with option "defaultFragmentDelimiter"
     * @param {String} fd The fragment delimiter character
     */
    
    function setDefaultFragmentDelimiter($fd)
    {
        if (is_string($fd) && strlen($fd) > 0) {
            $this->_options->defaultFragmentDelimiter = $fd;
        }
    }
    
    /**
     * Returns the default fragment delimiter of the environment.
     * 
     * @deprecated Use {@link Environment#getOption} with option "defaultFragmentDelimiter"
     * @returns {String} The fragment delimiter character
     */
    
    function getDefaultFragmentDelimiter()
    {
        return $this->_options->defaultFragmentDelimiter;
    }
    
    /**
     * Sets the URI of the default schema for the environment.
     * 
     * @deprecated Use {@link Environment#setOption} with option "defaultSchemaURI"
     * @param {String} uri The default schema URI
     */
    
    function setDefaultSchemaURI($uri)
    {
        if (is_string($uri)) {
            $this->_options->defaultSchemaURI = JSV::formatURI($uri);
        }
    }
    
    /**
     * Returns the default schema of the environment.
     * 
     * @returns {JSONSchema} The default schema
     */
    
    function getDefaultSchema()
    {
        return $this->findSchema($this->_options->defaultSchemaURI);
    }
    
    /**
     * Validates both the provided schema and the provided instance, and returns a {@link Report}. 
     * If the schema fails to validate, the instance will not be validated.
     * 
     * @param {JSONInstance|Any} instanceJSON The {@link JSONInstance} or JavaScript value to validate.
     * @param {JSONSchema|Any} schemaJSON The {@link JSONSchema} or JavaScript value to use in the validation. This will also be validated againt the schema's schema.
     * @returns {Report} The result of the validation
     */
    
    function validate($instanceJSON, $schemaJSON = null)
    {
        $report = new Report();
        
        try {
            $instance = $this->createInstance($instanceJSON);
            $report->instance = $instance;
        } catch (Exception $e) {
            $report->addError($e->uri, $e->schemaUri, $e->attribute, $e->getMessage(), $e->details);
        }
        
        try {
            $schema = $this->createSchema($schemaJSON);
            $report->schema = $schema;
            
            $schemaSchema = $schema->getSchema();
            $report->schemaSchema = $schemaSchema;
        } catch (Exception $e) {
            $report->addError($e->uri, $e->schemaUri, $e->attribute, $e->getMessage(), $e->details);
        }
        
        if ($schemaSchema) {
            $schemaSchema->validate($schema, $report);
        }
            
        if (count($report->errors)) {
            throw new Environment\Exception('Schema validation failed', $report);
        }
        
        return $schema->validate($instance, $report);
    }
    
    /**
     * @private
     */
    
    protected function _checkForInvalidInstances($stackSize, $schemaURI)
    {
        $result = array();
        $stack =
            array(
                array($schemaURI, $this->_schemas[$schemaURI])
            ); 
            $counter = 0;
        
        while ($counter++ < $stackSize && count($stack)) {
            $item = array_shift($stack);
            $uri = $item[0];
            $instance = $item[1];
            
            if ($instance instanceof JSONSchema) {
                if ($this->_schemas[$instance->getUri()] !== $instance) {
                    array_push($result, "Instance " . $uri . " does not match " . $instance->getUri());
                } else {
                    //$schema = $instance->getSchema();
                    //array_push($stack, array($uri . "/{schema}", $schema));
                    
                    $properties = $instance->getAttributes();
                    foreach ($properties as $key => $val) {
                        array_push($stack, array($uri . "/" . urlencode($key), $val));
                    }
                }
            } else if (is_object($instance)) {
                $properties = $instance;
                foreach (get_object_vars($properties) as $key => $val) {
                    if (isset($val)) {
                        array_push($stack, array($uri . "/" . urlencode($key), $val));
                    }
                }
            } else if (is_array($instance)) {
                $properties = $instance;
                foreach ($properties as $key => $val) {
                    array_push($stack, array($uri . "/" . urlencode($key), $val));
                }
            }
        }
        
        return count($result) ? $result : $counter;
    }

    function setId($id)
    {
        $this->_id = $id;
    }

    function getId()
    {
        return $this->_id;
    }

    function getSchemas()
    {
        return $this->_schemas;
    }

    // hack do not use unless you are Gary Court or porting his work
    function replaceSchema($uri, JSONSchema $schema)
    {
        $this->_schemas[$uri] = $schema;
    }
}
