--TEST--
JsonSchema: URI - resolve (5) with abnormal query/fragment examples
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$base = "http://a/b/c/d;p?q";

//abnormal examples from RFC 3986
$test->assertEquals('http://a/b/c/g?y/./x', $uri->resolve($base, 'g?y/./x'), 'g?y/./x');
$test->assertEquals('http://a/b/c/g?y/../x', $uri->resolve($base, 'g?y/../x'), 'g?y/../x');
$test->assertEquals('http://a/b/c/g#y/./x', $uri->resolve($base, 'g#y/./x'), 'g#y/./x');
$test->assertEquals('http://a/b/c/g#y/../x', $uri->resolve($base, 'g#y/../x'), 'g#y/../x');
?>
===DONE===
--EXPECT--
===DONE===