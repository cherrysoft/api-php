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

namespace Pyrus\JsonSchema;
use Pyrus\JsonSchema\JSV\Environment, Pyrus\JsonSchema\JSV\Exception, Pyrus\JsonSchema\JSV\ValidationException, Pyrus\JsonSchema\JSV\JSONInstance, Pyrus\JsonSchema\JSV\JSONSchema, Pyrus\JsonSchema\JSV\Report;

function is_json_object($i)
{
    if (is_object($i)) {
        return true;
    }
    if (!is_array($i)) {
        return false;
    }
    foreach ($i as $k => $v) {
        if (is_string($k)) {
            return true;
        }
        return false;
    }
    return true; // empty array is same as empty object, really
}

function is_json_array($i)
{
    if (!is_array($i)) {
        return false;
    }
    foreach ($i as $k => $v) {
        if (is_string($k)) {
            return false;
        }
        return true;
    }
    return true;
}

/**
 * A static class that provides the ability to create and manage {@link Environment}s,
 * as well as providing utility methods.
 *
 * Note: when porting JSV changes to PHP, remove all references to an "O" (the letter O) variable, this hack is
 * only necessary in javascript to make for (a in blah) work and is not needed in PHP
 * 
 * @namespace
 */
    
class JSV
{
    static protected $_environments = array();
    static protected $_defaultEnvironmentID = "json-schema-draft-04";

    /**
     * Creates and returns a new {@link Environment} that is a clone of the environment registered with the provided ID.
     * If no environment ID is provided, the default environment is cloned.
     * 
     * @param {String} [id] The ID of the environment to clone. If <code>undefined</code>, the default environment ID is used.
     * @returns {Environment} A newly cloned {@link Environment}
     * @throws {Error} If there is no environment registered with the provided ID
     * @return class JsonSchema\Environment environment object
     */
    
    static function createEnvironment($id=NULL)
    {
        if (!$id) {
            $id = static::$_defaultEnvironmentID;
        }
        
        if (!isset(static::$_environments[$id])) {
            switch ($id) { // lazy load
            case 'json-schema-draft-04':
            case 'json-schema-draft-03' :
            case 'json-schema-draft-02' :
            case 'json-schema-draft-01' :
              $class = __NAMESPACE__ . '\JSV\Schema\\' . ucfirst(str_replace(array('json-schema-', '-'), '', $id));
              new $class;
              break;
            default:
              throw new Exception("Unknown Environment ID: $id");
            }
        }
        //else
        return clone static::$_environments[$id];
    }

    /**
     * Registers the provided {@link Environment} with the provided ID.
     * 
     * @param {String} id The ID of the environment
     * @param {Environment} env The environment to register
     */
    
    static function registerEnvironment($id, $env)
    {
        if (!$id && $env) {
            $id = $env->_id;
        }
        if ($id && !isset(static::$_environments[$id]) && $env instanceof Environment) {
            $env->setId($id);
            static::$_environments[$id] = $env;
        }
    }

    /**
     * Sets which registered ID is the default environment.
     * 
     * @param {String} id The ID of the registered environment that is default
     * @throws {Error} If there is no registered environment with the provided ID
     */
    
    static function setDefaultEnvironmentID($id)
    {
        if (is_string($id)) {
            if (!static::$_environments[$id]) {
                throw new Exception("Unknown Environment ID");
            }
            
            static::$_defaultEnvironmentID = $id;
        }
    }

    /**
     * Returns the ID of the default environment.
     * 
     * @returns {String} The ID of the default environment
     */
    
    static function getDefaultEnvironmentID()
    {
        return static::$_defaultEnvironmentID;
    }

    //
    // Utility Functions
    //
    
    /**
     * Returns the name of the type of the provided value.
     *
     * @event //utility
     * @param {Any} o The value to determine the type of
     * @returns {String} The name of the type of the value
     */
    //typeOf : typeOf, use gettype
        
    /**
     * Return a new object that inherits all of the properties of the provided object.
     *
     * @event //utility
     * @param {Object} proto The prototype of the new object
     * @returns {Object} A new object that inherits all of the properties of the provided object
     */
    //createObject : createObject, use clone
        
    /**
     * Returns a new object with each property transformed by the iterator.
     *
     * @event //utility
     * @param {Object} obj The object to transform
     * @param {Function} iterator A function that returns the new value of the provided property
     * @returns {Object} A new object with each property transformed
     */
    // unnecessary

    /**
     * Returns a new array with each item transformed by the iterator.
     * 
     * @event //utility
     * @param {Array} arr The array to transform
     * @param {Function} iterator A function that returns the new value of the provided item
     * @param {Object} scope The value of <code>this</code> in the iterator
     * @returns {Array} A new array with each item transformed
     */
    //mapArray : mapArray, use array_map
    
    /**
     * Returns a new array that only contains the items allowed by the iterator.
     *
     * @event //utility
     * @param {Array} arr The array to filter
     * @param {Function} iterator The function that returns true if the provided property should be added to the array
     * @param {Object} scope The value of <code>this</code> within the iterator
     * @returns {Array} A new array that contains the items allowed by the iterator
     */
    //filterArray : filterArray, use array_filter
    
    /**
     * Returns the first index in the array that the provided item is located at.
     *
     * @event //utility
     * @param {Array} arr The array to search
     * @param {Any} o The item being searched for
     * @returns {Number} The index of the item in the array, or <code>-1</code> if not found
     */
    //searchArray : searchArray, use array_search
        
    /**
     * Returns an array representation of a value.
     * <ul>
     * <li>For array-like objects, the value will be casted as an Array type.</li>
     * <li>If an array is provided, the function will simply return the same array.</li>
     * <li>For a null or undefined value, the result will be an empty Array.</li>
     * <li>For all other values, the value will be the first element in a new Array. </li>
     * </ul>
     *
     * @event //utility
     * @param {Any} o The value to convert into an array
     * @returns {Array} The value as an array
     */
    //toArray : toArray, use settype($var, 'array');
    
    /**
     * Returns an array of the names of all properties of an object.
     * 
     * @event //utility
     * @param {Object|Array} o The object in question
     * @returns {Array} The names of all properties
     */
    //keys : keys, use get_object_vars
    
    /**
     * Mutates the array by pushing the provided value onto the array only if it is not already there.
     *
     * @event //utility
     * @param {Array} arr The array to modify
     * @param {Any} o The object to add to the array if it is not already there
     * @returns {Array} The provided array for chaining
     */
    //pushUnique : pushUnique, use array_unique() after adding to the array
    
    /**
     * Mutates the array by removing the first item that matches the provided value in the array.
     *
     * @event //utility
     * @param {Array} arr The array to modify
     * @param {Any} o The object to remove from the array
     * @returns {Array} The provided array for chaining
     */
    //popFirst : popFirst, use array_udiff($arr, array($o))
    
    /**
     * Creates a copy of the target object.
     * <p>
     * This method will create a new instance of the target, and then mixin the properties of the target.
     * If <code>deep</code> is <code>true</code>, then each property will be cloned before mixin.
     * </p>
     * <p><b>Warning</b>: This is not a generic clone function, as it will only properly clone objects and arrays.</p>
     * 
     * @event //utility
     * @param {Any} o The value to clone 
     * @param {Boolean} [deep=false] If each property should be recursively cloned
     * @returns A cloned copy of the provided value
     */
    //clone : clone, use built-in PHP clone
    
    /**
     * Generates a pseudo-random UUID.
     * 
     * @event //utility
     * @returns {String} A new universally unique ID
     */
    static function randomUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
        
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
        
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
        
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Properly escapes a URI component for embedding into a URI string.
     * 
     * @event //utility
     * @param {String} str The URI component to escape
     * @returns {String} The escaped URI component
     */
    //escapeURIComponent : escapeURIComponent, use urlencode
    
    /**
     * Returns a URI that is formated for JSV. Currently, this only ensures that the URI ends with a hash tag (<code>#</code>).
     * 
     * @event //utility
     * @param {String} uri The URI to format
     * @returns {String} The URI formatted for JSV
     */
    static function formatURI($uri)
    {
        if ($uri && !strpos($uri, '#') && $uri[strlen($uri)-1] != '#') {
            $uri .= '#';
        }
        return $uri;
    }
        
    /**
     * Merges two schemas/instance together.
     * 
     * @event //utility
     * @param {JSONSchema|Any} base The old value to merge
     * @param {JSONSchema|Any} extra The new value to merge
     * @param {Boolean} extension If the merge is a JSON Schema extension
     * @return {Any} The modified base value
     */
     
    static function inherits($base, $extra = null, $extension = false)
    {        
        if ($extra === null) {
            return self::dirtyClone($base);
        } else if ($base === null || gettype($base) != gettype($extra)) {
            return self::dirtyClone($extra);
        } else if (is_json_object($extra)) {
            if ($base instanceof JSONSchema) {
                $base = $base->getAttributes();
            }
            if ($extra instanceof JSONSchema) {
                $extra = $extra->getAttributes();
                if (isset($extra["extends"]) && $extension && $extra["extends"] instanceof JSONSchema) {
                    $extra["extends"] = array($extra["extends"]);
                }
            }
            $child = self::dirtyClone($base);
            foreach ($extra as $x => $val) {
                $b = isset($base[$x]) ? $base[$x] : null;
                $child[$x] = self::inherits($b, $val, $extension);
            }
            return $child;
        } else {
            return self::dirtyClone($extra);
        }
    }

	
	/**
	 * Warning: Not a generic clone function
	 * Produces a JSV acceptable clone
	 */
	
    static function dirtyClone($obj)
    {
		if ($obj instanceof JSONInstance) {
			$obj = $obj->getValue();
		}
        if (!is_object($obj) && !is_array($obj)) {
            return $obj;
        }
		
		if (is_object($obj)) {
            if (is_callable($obj) || $obj instanceof \Exception) {
                return $obj;
            }
            return clone $obj;
        }
        $newObj = array();
        foreach ($obj as $key => $val) {
            $newObj[$key] = self::dirtyClone($val);
        }
        return $newObj;
	}

    static function getMatchedPatternProperties($instance, $schema, $report, $self)
    {
		$matchedProperties = array();
		
		if (is_json_object($instance.getValue())) {
			$patternProperties = $schema->getAttribute("patternProperties");
			$properties = $instance->getProperties();
			foreach ($patternProperties as $pattern => $patproperty) {
                if (!@preg_match($pattern, '')) {
                    if ($report) {
                        $report->addError($schema, $self, "patternProperties", "Invalid pattern", $pattern);
                    }
                }
                foreach ($properties as $key => $property) {
                    if (preg_match($pattern, $key)) {
                        if (isset($matchedProperties[$key])) {
                            $matchedProperties[$key][] = $patproperty;
                            $matchedProperties[$key] = array_unique($matchedProperties[$key]);
                        } else {
                            $matchedProperties[$key] = array($patproperty);
                        }
                    }
                }
			}
		}
		
		return $matchedProperties;
	}
}
