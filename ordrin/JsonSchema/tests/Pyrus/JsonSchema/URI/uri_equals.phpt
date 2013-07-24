--TEST--
JsonSchema: URI - equals
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertTrue($uri->equal("example://a/b/c/%7Bfoo%7D", "eXAMPLE://a/./b/../b/%63/%7bfoo%7d"), 'test');
$test->assertFalse($uri->equal("example://a/b/c/%7Bfoo%7D", "eXAMPLE://a/./b/../B/%63/%7bfoo%7d"), 'test');
$test->assertFalse($uri->equal("example://a/b/c/%7Bfoo%7D", "eXAMPLE://a/./b/../b/%64/%7bfoo%7d"), 'test');
?>
===DONE===
--EXPECT--
===DONE===