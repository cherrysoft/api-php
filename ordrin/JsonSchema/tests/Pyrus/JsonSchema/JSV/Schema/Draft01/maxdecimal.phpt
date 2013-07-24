--TEST--
JsonSchema: format, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidate($env->validate(0, array('maxDecimal' => 0)), "0 0");
$test->assertSchemaValidate($env->validate(0, array('maxDecimal' => 1)), "0 1");
$test->assertSchemaValidate($env->validate(0.22, array('maxDecimal' => 2)), "0.22 2");
$test->assertSchemaValidate($env->validate(0.33, array('maxDecimal' => 3)), "0.33 3");
?>
===DONE===
--EXPECT--
===DONE===