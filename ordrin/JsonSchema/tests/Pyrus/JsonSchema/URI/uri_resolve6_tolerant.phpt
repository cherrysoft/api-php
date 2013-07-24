--TEST--
JsonSchema: URI - resolve (6) tolerant
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$base = "http://a/b/c/d;p?q";
$options = new Pyrus\JsonSchema\URI\Options;
$options->tolerant = true;

//abnormal examples from RFC 3986
$test->assertEquals('http:g', $uri->resolve($base, 'http:g'), 'http:g, non-tolerant');
$test->assertEquals('http://a/b/c/g', $uri->resolve($base, 'http:g', $options), 'http:g, tolerant');
?>
===DONE===
--EXPECT--
===DONE===