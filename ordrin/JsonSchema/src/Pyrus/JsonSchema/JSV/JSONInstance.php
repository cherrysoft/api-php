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
use Pyrus\JsonSchema\URI, Pyrus\JsonSchema\JSV;
/**
 * A wrapper class for binding an Environment, URI and helper methods to an instance. 
 * This class is most commonly instantiated with {@link Environment#createInstance}.
 * 
 * @name JSONInstance
 */
class JSONInstance
{
    protected $_fd;
    protected $_uri;
    protected $_value;
    protected $_env;

    function getFd()
    {
        return $this->_fd;
    }

    function getPath()
    {
        return substr($this->_uri, strpos($this->_uri, '#'));
    }

    function getUri()
    {
        return $this->_uri;
    }

    function setUri($uri)
    {
        $this->_uri = $uri;
    }

    function getValue()
    {
        return $this->_value;
    }

    function getEnvironment()
    {
        return $this->_env;
    }

    /**
     * @param {Environment} env The environment this instance belongs to
     * @param {JSONInstance|Any} json The value of the instance
     * @param {String} [uri] The URI of the instance. If undefined, the URI will be a randomly generated UUID. 
     * @param {String} [fd] The fragment delimiter for properties. If undefined, uses the environment default.
     */
    
    function __construct(Environment $env, $json, $uri, $fd = null) {
        if ($json instanceof JSONInstance) {
            if (!$fd) {
                $fd = $json->getFd();
            }
            if (!$uri) {
                $uri = $json->getUri();
            }
            $json = $json->getValue();
        }
        
        if (!$uri) {
            $uri = "urn:uuid:" . JSV::randomUUID() . "#";
        } elseif (strpos($uri, ":") === false) {
            $urimanager = new URI();
            $uri = JSV::formatURI($urimanager->resolve("urn:uuid:" . JSV::randomUUID() . "#", $uri));
        }
        
        $this->_env = $env;
        $this->_value = $json;
        $this->_uri = $uri;
        $this->_fd = $fd ? $fd : $this->_env->getOption("defaultFragmentDelimiter");
    }

    function __clone()
    {
        $this->_value = JSV::dirtyClone($this->_value);
    }

    /**
     * Returns a resolved URI of a provided relative URI against the URI of the instance.
     * 
     * @param {String} uri The relative URI to resolve
     * @returns {String} The resolved URI
     */
    
    function resolveURI($uri)
    {
        $urimanager = new URI();
        return JSV::formatURI($urimanager->resolve($this->_uri, $uri));
    }
    
    /**
     * Returns an array of the names of all the properties.
     * 
     * @returns {Array} An array of strings which are the names of all the properties
     */
    
    function getPropertyNames()
    {
        if ($this->_value === null || !is_array($this->_value)) {
            return array();
        }
        return array_keys($this->_value);
    }
    
    /**
     * Returns a {@link JSONInstance} of the value of the provided property name. 
     * 
     * @param {String} key The name of the property to fetch
     * @returns {JSONInstance} The instance of the property value
     */
    
    function getProperty($key)
    {
        $value = isset($this->_value) && is_array($this->_value) && isset($this->_value[$key]) ? $this->_value[$key] : null;
        if ($value instanceof JSONInstance) {
            return $value;
        }
        //else
        return new JSONInstance($this->_env, $value, $this->_uri . $this->_fd . urlencode($key), $this->_fd);
    }
    
    /**
     * Returns all the property instances of the target instance.
     * <p>
     * If the target instance is an Object, then the method will return a hash table of {@link JSONInstance}s of all the properties. 
     * If the target instance is an Array, then the method will return an array of {@link JSONInstance}s of all the items.
     * </p> 
     * 
     * @returns {Object|Array|undefined} The list of instances for all the properties
     */
    
    function getProperties()
    {
        $val = $this->_value;
        if ($val === null) {
            return array();
        }
        $self = $this;
        settype($val, 'array');
        array_walk($val, function (&$value, $key) use ($self) {
            if ($value instanceof JSONInstance) {
                return;
            }
            $value = new JSONInstance($self->getEnvironment(), $value, $self->getUri() . $self->getFd() . urlencode($key), $self->getFd());
        });
        return $val;
    }
    
    /**
     * Returns the JSON value of the provided property name. 
     * This method is a faster version of calling <code>instance.getProperty(key).getValue()</code>.
     * 
     * @param {String} key The name of the property
     * @returns {Any} The JavaScript value of the instance
     * @see JSONInstance#getProperty
     * @see JSONInstance#getValue
     */
    
    function getValueOfProperty($key)
    {
        if ($this->_value) {
            if (!is_array($this->_value)) {
                return null;
            }
            if (!isset($this->_value[$key])) {
                return null;
            }
            if ($this->_value[$key] instanceof JSONInstance) {
                return $this->_value[$key]->getValue();
            }
            return $this->_value[$key];
        }
    }
    
    /**
     * Return if the provided value is the same as the value of the instance.
     * 
     * @param {JSONInstance|Any} instance The value to compare
     * @returns {Boolean} If both the instance and the value match
     */
    
    function equals($instance) {
        if ($instance instanceof JSONInstance) {
            return $this->_value === $instance->getValue();
        }
        //else
        return $this->_value === $instance;
    }
}