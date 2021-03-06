<?php
/**
 * Uri.php
 * 
 * @fileOverview An RFC 3986 compliant, scheme extendable URI parsing/validating/resolving library for PHP.
 * @author Gregory Beaver greg@chiaraquartet.net
 * @author Gary Court gary.court@gmail.com
 * @version 1.0
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
 * or implied, of Gary Court.
 */
namespace Pyrus\JsonSchema\URI;
class Components
{
    public
    /**
     * @type String
     */
    
    $scheme = null,
    
    /**
     * @type String
     */
    
    $authority = null,
    
    /**
     * @type String
     */
    
    $userinfo = null,
    
    /**
     * @type String
     */
    
    $host = null,
    
    /**
     * @type Number
     */
    
    $port = null,
    
    /**
     * @type String
     */
    
    $path = null,
    
    /**
     * @type String
     */
    
    $query = null,
    
    /**
     * @type String
     */
    
    $fragment = null,
    
    /**
     * @type String
     * @enum "uri", "absolute", "relative", "same-document"
     */
    
    $reference = null,
    
    /**
     * @type Array
     */
    
    $errors = array('E_ERROR' => array());

    function __set($var, $value)
    {
        throw new Exception('Unknown components value: ' . $var);
    }

    function __get($var)
    {
        throw new Exception('Unknown components value: ' . $var);
    }    
}