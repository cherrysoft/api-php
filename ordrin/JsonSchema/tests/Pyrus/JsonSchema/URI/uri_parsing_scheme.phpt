--TEST--
JsonSchema: URI - parsing scheme
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$components = $uri->parse('uri:');
$test->assertEquals('uri', $components->scheme, "scheme");
$test->assertEquals('', $components->authority, "authority");
$test->assertEquals('', $components->userinfo, "userinfo");
$test->assertEquals('', $components->host, "host");
$test->assertEquals('', $components->port, "port");
$test->assertEquals('', $components->path, "path");
$test->assertEquals('', $components->query, "query");
$test->assertEquals('', $components->fragment, "fragment");
?>
===DONE===
--EXPECT--
===DONE===