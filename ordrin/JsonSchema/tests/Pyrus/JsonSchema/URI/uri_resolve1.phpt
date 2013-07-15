--TEST--
JsonSchema: URI - resolve (1) with scheme
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$base = "http://a/b/c/d;p?q";

$test->assertEquals('g:h', $uri->resolve($base, 'g:h'), 'g:h');
$test->assertEquals('http://a/b/c/g', $uri->resolve($base, 'g'), 'g');
$test->assertEquals('http://a/b/c/g', $uri->resolve($base, './g'), './g');
$test->assertEquals('http://a/b/c/g/', $uri->resolve($base, 'g/'), 'g/');
$test->assertEquals('http://a/g', $uri->resolve($base, '/g'), '/g');
$test->assertEquals('http://g', $uri->resolve($base, '//g'), '//g');
?>
===DONE===
--EXPECT--
===DONE===