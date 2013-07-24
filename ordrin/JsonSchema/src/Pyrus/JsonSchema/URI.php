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
namespace Pyrus\JsonSchema;
use Pyrus\JsonSchema\URI\Components, Pyrus\JsonSchema\URI\Options, Pyrus\JsonSchema\URI\SchemeHandlerInterface,
Pyrus\JsonSchema\URI\Exception;

class URI
{
    static $schemeHandlers;
    protected $options;
    static protected $regex = array(
        'ALPHA' => "[A-Za-z]",
        'CR' => "[\\x0D]",
        'DIGIT' => "[0-9]",
        'DQUOTE' => "[\\x22]",
        'HEXDIG' => "[a-zA-Z0-9]",
        'LF' => "[\\x0A]",
        'SP' => "[\\x20]",
        'PCT_ENCODED' => "(?:%[a-zA-Z0-9][a-zA-Z0-9])",
        'GEN_DELIMS' => "[\\:\\/\\?\\#\\[\\]\\@]",
        'SUB_DELIMS' => "[\\!\\$\\&\\'\\(\\)\\*\\+\\,\\;\\=]",
        'H16' => "(?:[0-9a-fA-F]{1,4})",
        'PORT' => "(?:\\d*)",
        'URI_PARSE' => "/^(?:([^:\/?#]+):)?(?:\/\/((?:([^\/?#@]*)@)?([^\/?#:]*)(?:\:(\d*))?))?([^?#]*)(?:\?([^#]*))?(?:#(.*))?/i",
        'RDS1' => "/^\.\.?\//",
        'RDS2' => "/^\/\.(\/|$)/",
        'RDS3' => "/^\/\.\.(\/|$)/",
        'RDS4' => "/^\.\.?$/",
        'RDS5' => "/^\/?.*?(?=\/|$)/",
    );

    function __construct()
    {
        $this->options = new Options;
        static::setupRegex();
    }
    
    static function mergeSet($set)
    {
        $set = substr($set, 0, strlen($set) - 1);
        for ($i = 1; $i < func_num_args(); $i++) {
            $nextSet = func_get_arg($i);
            $set .= substr($nextSet, 1, strlen($nextSet) - 2);
        }
        return $set . ']';
    }
        
        
    static function subexp($str)
    {
        return "(?:" . $str . ")";
    }

    static function setupRegex($cache = false)
    {
        static $done = 0;
        if ($done) return;
        if (class_exists(__NAMESPACE__ . '\URI\Cache', 1)) {
            static::$regex = namespace\URI\Cache::$regex;
            $done = 1;
            return;
        }
        $r = &static::$regex;
        $r['RESERVED'] = static::mergeSet($r['GEN_DELIMS'], $r['SUB_DELIMS']);
        $r['UNRESERVED'] = static::mergeSet($r['ALPHA'], $r['DIGIT'], "[\\-\\.\\_\\~]");
        $r['UNRESERVED_SET'] = array();
        for ($i = ord('a'); $i <= ord('z'); $i++) {
            $r['UNRESERVED_SET'][chr($i)] = 1;
            $r['UNRESERVED_SET'][chr($i - 32)] = 1;
        }
        for ($i = 0; $i < 10; $i++) { // digits
            $r['UNRESERVED_SET'][chr(48 + $i) . ''] = 1;
        }
        $r['UNRESERVED_SET']['-'] = 1;
        $r['UNRESERVED_SET']['.'] = 1;
        $r['UNRESERVED_SET']['_'] = 1;
        $r['UNRESERVED_SET']['~'] = 1;
        $r['SCHEME'] = static::subexp($r['ALPHA'] . static::mergeSet($r['ALPHA'], $r['DIGIT'], "[\\+\\-\\.]") . "*");
        $r['USERINFO'] = static::subexp(static::subexp($r['PCT_ENCODED'] . '|' . static::mergeSet($r['UNRESERVED'], $r['SUB_DELIMS'], "[\\:]")) . '*');
        $r['DEC_OCTET'] = static::subexp($r['DIGIT'] . "|" . static::subexp("[1-9]" . $r['DIGIT']) . "|" . static::subexp("1" .
                                                                                                                          $r['DIGIT'] . $r['DIGIT']) .
                                         "|" . static::subexp("2[0-4]" . $r['DIGIT']) . "|" . static::subexp("25[0-5]"));
        $r['IPV4ADDRESS'] = static::subexp($r['DEC_OCTET'] . "\\." . $r['DEC_OCTET'] . "\\." . $r['DEC_OCTET'] . "\\." . $r['DEC_OCTET']);
        $r['LS32'] = static::subexp(static::subexp($r['H16'] . "\\:" . $r['H16']) . "|" . $r['IPV4ADDRESS']);
        $r['IPV6ADDRESS'] = static::subexp(static::mergeSet($r['UNRESERVED'], $r['SUB_DELIMS'], "[\\:]") . "+");  //FIXME
        $r['IPVFUTURE'] = static::subexp("v" . $r['HEXDIG'] . "+\\." . static::mergeSet($r['UNRESERVED'], $r['SUB_DELIMS'], "[\\:]") . "+");
        $r['IP_LITERAL'] = static::subexp("\\[" . static::subexp($r['IPV6ADDRESS'] . "|" . $r['IPVFUTURE']) . "\\]");
        $r['REG_NAME'] = static::subexp(static::subexp($r['PCT_ENCODED'] . "|" . static::mergeSet($r['UNRESERVED'], $r['SUB_DELIMS'])) . "*");
        $r['HOST'] = static::subexp($r['IP_LITERAL'] . "|" . $r['IPV4ADDRESS'] . "|" . $r['REG_NAME']);
        $r['AUTHORITY'] = static::subexp(static::subexp($r['USERINFO'] . "@") . "?" . $r['HOST'] . static::subexp("\\:" . $r['PORT']) . "?");
        $r['PCHAR'] = static::subexp($r['PCT_ENCODED'] . "|" . static::mergeSet($r['UNRESERVED'], $r['SUB_DELIMS'], "[\\:\\@]"));
        $r['SEGMENT'] = static::subexp($r['PCHAR'] . "*");
        $r['SEGMENT_NZ'] = static::subexp($r['PCHAR'] . "+");
        $r['SEGMENT_NZ_NC'] = static::subexp(static::subexp($r['PCT_ENCODED'] . "|" . static::mergeSet($r['UNRESERVED'], $r['SUB_DELIMS'], "[\\@]")) . "+");
        $r['PATH_ABEMPTY'] = static::subexp(static::subexp("\\/" . $r['SEGMENT']) . "*");
        $r['PATH_ABSOLUTE'] = static::subexp("\\/" . static::subexp($r['SEGMENT_NZ'] . $r['PATH_ABEMPTY']) . "?");  //simplified
        $r['PATH_NOSCHEME'] = static::subexp($r['SEGMENT_NZ_NC'] . $r['PATH_ABEMPTY']);  //simplified
        $r['PATH_ROOTLESS'] = static::subexp($r['SEGMENT_NZ'] . $r['PATH_ABEMPTY']);  //simplified
        $r['PATH_EMPTY'] = static::subexp("");  //simplified
        $r['PATH'] = static::subexp($r['PATH_ABEMPTY'] . "|" . $r['PATH_ABSOLUTE'] . "|" . $r['PATH_NOSCHEME'] . "|" . $r['PATH_ROOTLESS'] . "|" . $r['PATH_EMPTY']);
        $r['QUERY'] = static::subexp(static::subexp($r['PCHAR'] . "|[\\/\\?]") . "*");
        $r['FRAGMENT'] = static::subexp(static::subexp($r['PCHAR'] . "|[\\/\\?]") . "*");
        $r['HIER_PART'] = static::subexp(static::subexp("\\/\\/" . $r['AUTHORITY'] . $r['PATH_ABEMPTY']) . "|" . $r['PATH_ABSOLUTE'] . "|" . $r['PATH_ROOTLESS'] . "|" . $r['PATH_EMPTY']);
        $r['URI'] = static::subexp($r['SCHEME'] . "\\:" . $r['HIER_PART'] . static::subexp("\\?" . $r['QUERY']) . "?" . static::subexp("\\#" . $r['FRAGMENT']) . "?");
        $r['RELATIVE_PART'] = static::subexp(static::subexp("\\/\\/" . $r['AUTHORITY'] . $r['PATH_ABEMPTY']) . "|" . $r['PATH_ABSOLUTE'] . "|" . $r['PATH_NOSCHEME'] . "|" . $r['PATH_EMPTY']);
        $r['RELATIVE_REF'] = static::subexp($r['RELATIVE_PART'] . static::subexp("\\?" . $r['QUERY']) . "?" . static::subexp("\\#" . $r['FRAGMENT']) . "?");
        $r['URI_REFERENCE'] = static::subexp($r['URI'] . "|" . $r['RELATIVE_REF']);
        $r['ABSOLUTE_URI'] = static::subexp($r['SCHEME'] . "\\:" . $r['HIER_PART'] . static::subexp("\\?" . $r['QUERY']) . "?");
        
        $r['URI_REF'] = "/^" . static::subexp("(" . $r['URI'] . ")|(" . $r['RELATIVE_REF'] . ")") . "\\z/";
        $r['GENERIC_REF']  = "/^(" . $r['SCHEME'] . ")\\:" .
            static::subexp(static::subexp("\\/\\/(" . static::subexp("(" . $r['USERINFO'] . ")@") . "?(" . $r['HOST'] . ")" .
                                          static::subexp("\\:(" . $r['PORT'] . ")") . "?)") . "?(" . $r['PATH_ABEMPTY'] . "|" .
                           $r['PATH_ABSOLUTE'] . "|" . $r['PATH_ROOTLESS'] . "|" . $r['PATH_EMPTY'] . ")") .
            static::subexp("\\?(" . $r['QUERY'] . ")") . "?" . static::subexp("\\#(" . $r['FRAGMENT'] . ")") . "?\\z/";
        $r['RELATIVE_REF'] = "/^(){0}" . static::subexp(static::subexp("\\/\\/(" . static::subexp("(" . $r['USERINFO'] . ")@") .
                                                                       "?(" . $r['HOST'] . ")" . static::subexp("\\:(" . $r['PORT'] . ")") .
                                                                       "?)") . "?(" . $r['PATH_ABEMPTY'] . "|" . $r['PATH_ABSOLUTE'] . "|" .
                                                        $r['PATH_NOSCHEME'] . "|" . $r['PATH_EMPTY'] . ")") .
            static::subexp("\\?(" . $r['QUERY'] . ")")
            . "?" . static::subexp("\\#(" . $r['FRAGMENT'] . ")") . "?$/";
        $r['ABSOLUTE_REF'] = "/^(" . $r['SCHEME'] . ")\\:" . static::subexp(static::subexp("\\/\\/(" .
                                                                                           static::subexp("(" . $r['USERINFO'] . ")@") . "?(" .
                                                                                           $r['HOST'] . ")" . static::subexp("\\:(" . $r['PORT'] . ")")
                                                                                           . "?)") . "?(" . $r['PATH_ABEMPTY'] . "|" .
                                                                            $r['PATH_ABSOLUTE'] . "|" . $r['PATH_ROOTLESS'] . "|" .
                                                                            $r['PATH_EMPTY'] . ")") . static::subexp("\\?(" . $r['QUERY'] . ")") . "?\\z/";
        $r['SAMEDOC_REF'] = "/^" . static::subexp("\\#(" . $r['FRAGMENT'] . ")") . "?\\z/";
        $r['AUTHORITY'] = "/^" . static::subexp("(" . $r['USERINFO'] . ")@") . "?(" . $r['HOST'] . ")" . static::subexp("\\:(" . $r['PORT'] . ")") . "?\\z/";
        
        $r['NOT_SCHEME'] = "/" . static::mergeSet("[^]", $r['ALPHA'], $r['DIGIT'], "[\\+\\-\\.]") . "/";
        $r['NOT_USERINFO'] = "/" . static::mergeSet("[^\\%\\:]", $r['UNRESERVED'], $r['SUB_DELIMS']) . "/";
        $r['NOT_HOST'] = "/" . static::mergeSet("[^\\%]", $r['UNRESERVED'], $r['SUB_DELIMS']) . "/";
        $r['NOT_PATH'] = "/" . static::mergeSet("[^\\%\\/\\:\\@]", $r['UNRESERVED'], $r['SUB_DELIMS']) . "/";
        $r['NOT_PATH_NOSCHEME'] = "/" . static::mergeSet("[^\\%\\/\\@]", $r['UNRESERVED'], $r['SUB_DELIMS']) . "/";
        $r['NOT_QUERY'] = "/" . static::mergeSet("[^\\%]", $r['UNRESERVED'], $r['SUB_DELIMS'], "[\\:\\@\\/\\?]") . "/";
        $r['NOT_FRAGMENT'] = $r['NOT_QUERY'];
        $r['ESCAPE'] = "/" . static::mergeSet("[^]", $r['UNRESERVED'], $r['SUB_DELIMS']) . "/";
        $r['UNRESERVEDREGEX'] = "/" . $r['UNRESERVED'] . "/";
        $r['OTHER_CHARS'] = "/" . static::mergeSet("[^\\%]", $r['UNRESERVED'], $r['RESERVED']) . "/";
        $r['PCT_ENCODEDS'] = "/" . $r['PCT_ENCODED'] . "+" . "/";
        $done = 1;
        if ($cache) {
            // save this complicated crap to a cached php file for faster loading later
            file_put_contents(__DIR__ . '/UriCache.php', str_replace(array(
                '0 => 1',
                '1 => 1',
                '2 => 1',
                '3 => 1',
                '4 => 1',
                '5 => 1',
                '6 => 1',
                '7 => 1',
                '8 => 1',
                '9 => 1',
                                                                          ),
                                                                     array(
                '"0" => 1',
                '"1" => 1',
                '"2" => 1',
                '"3" => 1',
                '"4" => 1',
                '"5" => 1',
                '"6" => 1',
                '"7" => 1',
                '"8" => 1',
                '"9" => 1',
                                                                          ), '<?php namespace ' . __NAMESPACE__ . "\\URI;\n" .
                              "class Cache\n" .
                              "{\n" .
                              'static $regex = ' .
                              var_export($r, 1) .
                              ';}'));
        }
    }
    
    function utfCharToNumber($char) // from PHP manual comments on ord()
    {
        $i = 0;
        $number = '';
        while (isset($char[$i])) {
            $number.= ord($char[$i]);
            ++$i;
        }
        return $number;
    }

        
    function pctEncChar($matches)
    {
        $chr = $matches[0];
        $c = $this->utfCharToNumber($chr);

        if ($c < 128) {
            return "%" . strtoupper(dechex($c));
        } elseif (($c > 127) && ($c < 2048)) {
            return "%" . strtoupper(dechex(($c >> 6) | 192)) . "%" . strtoupper(dechex(($c & 63) | 128));
        } else {
            return "%" . strtoupper(dechex((c >> 12) | 224)) . "%" . strtoupper(dechex((($c >> 6) & 63) | 128)) .
                "%" . strtoupper(dechex(($c & 63) | 128));
        }
    }
        
    function pctDecUnreserved($str)
    {
        $newStr = "";
        $i = 0;
        $str = $str[0];
 
        while ($i < strlen($str)) {
            $c = hexdec(substr($str, $i + 1, 2));
 
            if ($c < 128) {
                if (isset(static::$regex['UNRESERVED_SET'][chr($c)])) {
                    $newStr .= chr($c);
                } else {
                    $newStr .= substr($str, $i, 3);
                }
                $i += 3;
            } elseif (($c > 191) && ($c < 224)) {
                $newStr .= substr($str, $i, 6);
                $i += 6;
            } else {
                $newStr .= substr($str, $i, 9);
                $i += 9;
            }
        }
 
        return $newStr;
    }
        
    function pctDecChars($matches)
    {
        $str = $matches[0];
        $newStr = "";
        $i = 0;
 
        while ($i < strlen($str)) {
            $c = hexdec(substr($str, $i + 1, 2));
 
            if ($c < 128) {
                $newStr .= chr($c);
                $i += 3;
            } elseif (($c > 191) && ($c < 224)) {
                $c2 = hexdec(substr($str, $i + 4, 2));
                $newStr .= utf8_encode(chr($c & 0xff) . chr($c2 & 0xff));
                $i += 6;
            }
            else {
                $c2 = hexdec(substr($str, $i + 4, 2));
                $c3 = hexdec(substr($str, $i + 7, 2));
                $newStr .= utf8_encode(chr($c & 0xff) . chr($c2 & 0xff) . chr($c3 & 0xff));
                $i += 9;
            }
        }
 
        return $newStr;
    }
    
    /**
     * @namespace
     */
    
    protected static $SCHEMES = array();

    function addScheme(SchemeHandlerInterface $handler)
    {
        static::$SCHEMES[$handler->getName()] = $handler;
    }
    
    /**
     * @param {String} uriString
     * @param {Options} [options]
     * @returns {URIComponents}
     */
    
    function parse($uriString, Options $options = null)
    {
        $components = new Components;
        
        $uriString = $uriString ? (string) $uriString : "";
        if (null === $options) {
            $options = $this->options;
        }
        
        if ($options->reference === "suffix") {
            $uriString = ($options->scheme ? $options->scheme . ":" : "") . "//" . $uriString;
        }
        
        if (preg_match(static::$regex['URI_REF'], $uriString, $matches)) {
            if ($matches[1]) {
                //generic URI
                $test = preg_match(static::$regex['GENERIC_REF'], $uriString, $matches);
            } else {
                //relative URI
                $test = preg_match(static::$regex['RELATIVE_REF'], $uriString, $matches);
            }
        } else {
            if (!$options->tolerant) {
                $components->errors->E_ERROR[] = new Exception("URI is not strictly valid.");
            }
            $test = preg_match(self::$regex['URI_PARSE'], $uriString, $matches);
        }
        
        if ($test) {
            //store each component
            $components->scheme = $matches[1];
            $components->authority = $matches[2];
            $components->userinfo = $matches[3];
            $components->host = $matches[4];
            $components->port = $matches[5];
            $components->path = $matches[6];
            $components->query = isset($matches[7]) ? $matches[7] : '';
            $components->fragment = isset($matches[8]) ? $matches[8] : '';
            
            //fix port number
            if (is_numeric($components->port)) {
                $components->port = (int)$matches[5];
            }
            
            //determine reference type
            if (!$components->scheme && !$components->authority && !$components->path && !$components->query) {
                $components->reference = "same-document";
            } else if (!$components->scheme) {
                $components->reference = "relative";
            } else if (!$components->fragment) {
                $components->reference = "absolute";
            } else {
                $components->reference = "uri";
            }
            
            //check for reference errors
            if ($options->reference && $options->reference !== "suffix" && $options->reference !== $components->reference) {
                $components->errors->E_ERROR[] = new Exception("URI is not a " . $options->reference . " reference.");
            }
            
            //check if a handler for the scheme exists
            if (isset(static::$SCHEMES[$components->scheme ? $components->scheme : $options->scheme])) {
                $schemeHandler = static::$SCHEMES[$components->scheme ? $components->scheme : $options->scheme];
                //perform extra parsing
                $components = $schemeHandler->parse($components, $options);
            }
        } else {
            $components->errors->E_ERROR[] = new Exception("URI can not be parsed.");
        }
        
        return $components;
    }
    
    /**
     * @private
     * @param {URIComponents} components
     * @returns {String}
     */
    
    function _recomposeAuthority($components)
    {
        $uriTokens = array();
        
        if ($components->userinfo || $components->host || is_int($components->port)) {
            if ($components->userinfo) {
                $uriTokens[] = preg_replace_callback(static::$regex['NOT_USERINFO'], array($this, 'pctEncChar'), $components->userinfo);
                $uriTokens[] = "@";
            }
            if ($components->host) {
                $uriTokens[] = preg_replace_callback(static::$regex['NOT_HOST'], array($this, 'pctEncChar'), strtolower($components->host));
            }
            if (is_int($components->port)) {
                $uriTokens[] = ":";
                $uriTokens[] =  $components->port + 0;
            }
        }
        
        return count($uriTokens) ? implode('', $uriTokens) : null;
    }
    
    /**
     * @param {String} input
     * @returns {String}
     */
    
    function removeDotSegments($input) {
        $output = array();
        
        while (strlen($input)) {
            if (preg_match(static::$regex['RDS1'], $input)) {
                $input = preg_replace(static::$regex['RDS1'], "", $input);
            } elseif (preg_match(static::$regex['RDS2'], $input)) {
                $input = preg_replace(static::$regex['RDS2'], "/", $input);
            } elseif (preg_match(static::$regex['RDS3'], $input)) {
                $input = preg_replace(static::$regex['RDS3'], "/", $input);
                array_pop($output);
            } elseif ($input === "." || $input === "..") {
                $input = "";
            } else {
                preg_match(static::$regex['RDS5'], $input, $matches);
                $output[] = $matches[0];
                $input = substr($input, strlen($matches[0]));
                if (!$input) {
                    $input = '';
                }
            }
        }
        
        return implode('', $output);
    }
    
    /**
     * @param {URIComponents} components
     * @param {Options} options
     * @returns {String}
     */
    
    function serialize(Components $components, Options $options = null)
    {
        $uriTokens = array(); 

        if (null === $options) {
            $options = $this->options;
        }
        
        //check if a handler for the scheme exists
        if (isset(static::$SCHEMES[$components->scheme ? $components->scheme : $options->scheme])) {
            $schemeHandler = static::$SCHEMES[$components->scheme ? $components->scheme : $options->scheme];
            //perform extra serialization
            $schemeHandler->serialize($components, $options);
        }
        
        if ($options->reference !== "suffix" && $components->scheme) {
            $uriTokens[] = preg_replace(static::$regex['NOT_SCHEME'], '', strtolower($components->scheme));
            $uriTokens[] = ':';
        }
        
        $components->authority = $this->_recomposeAuthority($components);
        if ($components->authority !== null) {
            if ($options->reference !== "suffix") {
                $uriTokens[] = "//";
            }
            
            $uriTokens[] = $components->authority;
            
            if ($components->path && $components->path[0] !== "/") {
                $uriTokens[] = "/";
            }
        }
        
        if ($components->path) {
            $s = $this->removeDotSegments(str_replace(array('%2E', '%2e'), '.', $components->path));
            
            if ($components->scheme) {
                $s = preg_replace_callback(static::$regex['NOT_PATH'], array($this, 'pctEncChar'), $s);
            } else {
                $s = preg_replace_callback(static::$regex['NOT_PATH_NOSCHEME'], array($this, 'pctEncChar'), $s);
            }
            
            if ($components->authority === null) {
                if (strlen($s) > 1 && $s[0] == '/' && $s[1] == '/') {
                    $s = '/%2F' . substr($s, 2);  //don't allow the path to start with "//"
                }
            }
            $uriTokens[] = $s;
        }
        
        if ($components->query) {
            $uriTokens[] = "?";
            $uriTokens[] = preg_replace_callback(static::$regex['NOT_QUERY'], array($this, 'pctEncChar'), $components->query);
        }
        
        if ($components->fragment) {
            $uriTokens[] = "#";
            $uriTokens[] = preg_replace_callback(static::$regex['NOT_FRAGMENT'], array($this, 'pctEncChar'), $components->fragment);
        }
        
        return preg_replace_callback('/%[0-9A-Fa-f]{2}/', function ($matches) {  //uppercase percent encoded characters
                return strtoupper($matches[0]);
            },
            preg_replace_callback(
                static::$regex['PCT_ENCODEDS'], array($this, 'pctDecUnreserved'),
                implode('', $uriTokens)
            )
        );
    }
    
    /**
     * @param {URIComponents} base
     * @param {URIComponents} relative
     * @param {Options} [options]
     * @param {Boolean} [skipNormalization]
     * @returns {URIComponents}
     */
    
    function resolveComponents(Components $base, Components $relative, Options $options = null, $skipNormalization = false)
    {
        $target = new Components();
        
        if (!$skipNormalization) {
            $base = $this->parse($this->serialize($base, $options), $options);  //normalize base components
            $relative = $this->parse($this->serialize($relative, $options), $options);  //normalize relative components
        }
        if (null === $options) {
            $options = $this->options;
        }
        
        if (!$options->tolerant && $relative->scheme) {
            $target->scheme = $relative->scheme;
            $target->authority = $relative->authority;
            $target->userinfo = $relative->userinfo;
            $target->host = $relative->host;
            $target->port = $relative->port;
            $target->path = $this->removeDotSegments($relative->path);
            $target->query = $relative->query;
        } else {
            if ($relative->authority) {
                $target->authority = $relative->authority;
                $target->userinfo = $relative->userinfo;
                $target->host = $relative->host;
                $target->port = $relative->port;
                $target->path = $this->removeDotSegments($relative->path);
                $target->query = $relative->query;
            } else {
                if (!$relative->path) {
                    $target->path = $base->path;
                    if ($relative->query) {
                        $target->query = $relative->query;
                    } else {
                        $target->query = $base->query;
                    }
                } else {
                    if ($relative->path[0] === "/") {
                        $target->path = $this->removeDotSegments($relative->path);
                    } else {
                        if ($base->authority && !$base->path) {
                            $target->path = "/" . $relative->path;
                        } else if (!$base->path) {
                            $target->path = $relative->path;
                        } else {
                            if (strpos($base->path, '/') !== false) {
                                $target->path = substr($base->path, 0, strrpos($base->path, "/") + 1) . $relative->path;
                            } else {
                                $target->path = $relative->path;
                            }
                        }
                        $target->path = $this->removeDotSegments($target->path);
                    }
                    $target->query = $relative->query;
                }
                $target->authority = $base->authority;
                $target->userinfo = $base->userinfo;
                $target->host = $base->host;
                $target->port = $base->port;
            }
            $target->scheme = $base->scheme;
        }
        
        $target->fragment = $relative->fragment;
        
        return $target;
    }
    
    /**
     * @param {String} baseURI
     * @param {String} relativeURI
     * @param {Options} [options]
     * @returns {String}
     */
    
    function resolve($baseURI, $relativeURI, Options $options = null)
    {
        return $this->serialize($this->resolveComponents($this->parse($baseURI, $options),
                                                         $this->parse($relativeURI, $options), $options, true), $options);
    }
    
    /**
     * @param {String|URIComponents} uri
     * @param {Options} options
     * @returns {String|URIComponents}
     */
    
    function normalize($uri, Options $options = null)
    {
        if (is_string($uri)) {
            return $this->serialize($this->parse($uri, $options), $options);
        } else if ($uri instanceof Components) {
            return $this->parse($this->serialize($uri, $options), $options);
        }
        
        return $uri;
    }
    
    /**
     * @param {String|URIComponents} uriA
     * @param {String|URIComponents} uriB
     * @param {Options} options
     */
    
    function equal($uriA, $uriB, Options $options = null)
    {
        if (is_string($uriA)) {
            $uriA = $this->serialize($this->parse($uriA, $options), $options);
        } else if ($uriA instanceof Components) {
            $uriA = $this->serialize($uriA, $options);
        }
        
        if (is_string($uriB)) {
            $uriB = $this->serialize($this->parse($uriB, $options), $options);
        } else if ($uriB instanceof Components) {
            $uriB = $this->serialize($uriB, $options);
        }
        
        return $uriA == $uriB;
    }
    
    /**
     * @param {String} str
     * @returns {String}
     */
    
    function escapeComponent($str)
    {
        if (!$str) {
            return '';
        }
        return preg_replace_callback(static::$regex['ESCAPE'], array($this, 'pctEncChar'), $str);
    }
    
    /**
     * @param {String} str
     * @returns {String}
     */
    
    function unescapeComponent($str)
    {
        if (!$str) {
            return '';
        }
        return preg_replace_callback(static::$regex['PCT_ENCODEDS'], array($this, 'pctDecChar'), $str);
    }
}