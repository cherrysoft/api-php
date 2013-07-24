--TEST--
JsonSchema: URI - resolve (4) with dot segments, abnormal examples
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$base = "http://a/b/c/d;p?q";

//abnormal examples from RFC 3986
$test->assertEquals('http://a/g', $uri->resolve($base, '../../../g'), '../../../g');
$test->assertEquals('http://a/g', $uri->resolve($base, '../../../../g'), '../../../../g');

$test->assertEquals('http://a/g', $uri->resolve($base, '/./g'), '/./g');
$test->assertEquals('http://a/g', $uri->resolve($base, '/../g'), '/../g');
$test->assertEquals('http://a/b/c/g.', $uri->resolve($base, 'g.'), 'g.');
$test->assertEquals('http://a/b/c/.g', $uri->resolve($base, '.g'), '.g');
$test->assertEquals('http://a/b/c/g..', $uri->resolve($base, 'g..'), 'g..');
$test->assertEquals('http://a/b/c/..g', $uri->resolve($base, '..g'), '..g');

$test->assertEquals('http://a/b/g', $uri->resolve($base, './../g'), './../g');
$test->assertEquals('http://a/b/c/g/', $uri->resolve($base, './g/.'), './g/.');
$test->assertEquals('http://a/b/c/h', $uri->resolve($base, 'g/../h'), 'g/../h');
$test->assertEquals('http://a/b/c/g;x=1/y', $uri->resolve($base, 'g;x=1/./y'), 'g;x=1/./y');
$test->assertEquals('http://a/b/c/y', $uri->resolve($base, 'g;x=1/../y'), 'g;x=1/../y');
?>
===DONE===
--EXPECT--
===DONE===