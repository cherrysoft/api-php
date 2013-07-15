--TEST--
JsonSchema: URI - parsing port
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$components = $uri->parse('//:80');
$test->assertEquals('', $components->scheme, "scheme");
$test->assertEquals(':80', $components->authority, "authority");
$test->assertEquals('', $components->userinfo, "userinfo");
$test->assertEquals('', $components->host, "host");
$test->assertEquals(80, $components->port, "port");
$test->assertEquals('', $components->path, "path");
$test->assertEquals('', $components->query, "query");
$test->assertEquals('', $components->fragment, "fragment");
?>
===DONE===
--EXPECT--
===DONE===