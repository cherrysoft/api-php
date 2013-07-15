--TEST--
JsonSchema: patterns, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate('', array('pattern' => '^$')), "blank");
$test->assertSchemaValidate($env->validate('today', array('pattern' => 'day')), "blank");
?>
===DONE===
--EXPECT--
===DONE===