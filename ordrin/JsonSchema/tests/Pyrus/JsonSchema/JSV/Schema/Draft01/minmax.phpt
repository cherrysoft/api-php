--TEST--
JsonSchema: minimum/maximum, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(0, array()), "simple");
$test->assertSchemaValidate($env->validate(1, array('minimum' => 1, 'maximum' => 10)), "1 min 1 max 10");
$test->assertSchemaValidate($env->validate(5, array('minimum' => 1, 'maximum' => 10)), "5 min 1 max 10");
$test->assertSchemaValidate($env->validate(10, array('minimum' => 1, 'maximum' => 10)), "10 min 1 max 10");
$test->assertSchemaValidate($env->validate(1, array('minimum' => 1, 'maximum' => 1)), "1 min 1 max 1");

?>
===DONE===
--EXPECT--
===DONE===