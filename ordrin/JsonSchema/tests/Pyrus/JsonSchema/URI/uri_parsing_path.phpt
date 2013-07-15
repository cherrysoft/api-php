--TEST--
JsonSchema: URI - parsing path
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$components = $uri->parse('//a/a');
$test->assertEquals('', $components->scheme, "scheme");
$test->assertEquals('a', $components->authority, "authority");
$test->assertEquals('', $components->userinfo, "userinfo");
$test->assertEquals('a', $components->host, "host");
$test->assertEquals('', $components->port, "port");
$test->assertEquals('/a', $components->path, "path");
$test->assertEquals('', $components->query, "query");
$test->assertEquals('', $components->fragment, "fragment");
?>
===DONE===
--EXPECT--
===DONE===