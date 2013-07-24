--TEST--
JsonSchema: URI - resolve (2) with query and fragment
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$base = "http://a/b/c/d;p?q";

$test->assertEquals('http://a/b/c/d;p?y', $uri->resolve($base, '?y'), '?y');
$test->assertEquals('http://a/b/c/g?y', $uri->resolve($base, 'g?y'), 'g?y');
$test->assertEquals('http://a/b/c/d;p?q#s', $uri->resolve($base, '#s'), '#s');
$test->assertEquals('http://a/b/c/g#s', $uri->resolve($base, 'g#s'), 'g#s');
$test->assertEquals('http://a/b/c/g?y#s', $uri->resolve($base, 'g?y#s'), 'g?y#s');
$test->assertEquals('http://a/b/c/;x', $uri->resolve($base, ';x'), ';x');
$test->assertEquals('http://a/b/c/g;x', $uri->resolve($base, 'g;x'), 'g;x');
$test->assertEquals('http://a/b/c/g;x?y#s', $uri->resolve($base, 'g;x?y#s'), 'g;x?y#s');
$test->assertEquals('http://a/b/c/d;p?q', $uri->resolve($base, ''), '[empty]');
?>
===DONE===
--EXPECT--
===DONE===