--TEST--
JsonSchema: URI - resolve (3) with dot segments
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$base = "http://a/b/c/d;p?q";

$test->assertEquals('http://a/b/c/', $uri->resolve($base, '.'), '.');
$test->assertEquals('http://a/b/c/', $uri->resolve($base, './'), './');
$test->assertEquals('http://a/b/', $uri->resolve($base, '..'), '..');
$test->assertEquals('http://a/b/', $uri->resolve($base, '../'), '../');
$test->assertEquals('http://a/b/g', $uri->resolve($base, '../g'), '../g');
$test->assertEquals('http://a/', $uri->resolve($base, '../..'), '../..');
$test->assertEquals('http://a/', $uri->resolve($base, '../../'), '../../');
$test->assertEquals('http://a/g', $uri->resolve($base, '../../g'), '../../g');

?>
===DONE===
--EXPECT--
===DONE===