--TEST--
JsonSchema: URI - parsing complex example
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$components = $uri->parse('uri://user:pass@example.com:123/one/two.three?q1=a1&q2=a2#body');
$test->assertEquals('uri', $components->scheme, "scheme");
$test->assertEquals('user:pass@example.com:123', $components->authority, "authority");
$test->assertEquals('user:pass', $components->userinfo, "userinfo");
$test->assertEquals('example.com', $components->host, "host");
$test->assertEquals(123, $components->port, "port");
$test->assertEquals('/one/two.three', $components->path, "path");
$test->assertEquals('q1=a1&q2=a2', $components->query, "query");
$test->assertEquals('body', $components->fragment, "fragment");
?>
===DONE===
--EXPECT--
===DONE===