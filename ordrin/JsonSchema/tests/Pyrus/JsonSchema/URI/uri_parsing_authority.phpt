--TEST--
JsonSchema: URI - parsing authority
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$components = $uri->parse('//@a');
$test->assertEquals('', $components->scheme, "scheme");
$test->assertEquals('@a', $components->authority, "authority");
$test->assertEquals('', $components->userinfo, "userinfo");
$test->assertEquals('a', $components->host, "host");
$test->assertEquals('', $components->port, "port");
$test->assertEquals('', $components->path, "path");
$test->assertEquals('', $components->query, "query");
$test->assertEquals('', $components->fragment, "fragment");
?>
===DONE===
--EXPECT--
===DONE===