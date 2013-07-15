--TEST--
JsonSchema: minLength/maxLength, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidate($env->validate('1', array('minLength' => 1, 'maxLength' => 1)), "1 min 1 max 1");
$test->assertSchemaValidate($env->validate('1', array('minLength' => 1, 'maxLength' => 3)), "1 min 1 max 3");
$test->assertSchemaValidate($env->validate('12', array('minLength' => 1, 'maxLength' => 3)), "2 min 1 max 3");
$test->assertSchemaValidate($env->validate('123', array('minLength' => 1, 'maxLength' => 3)), "3 min 1 max 3");

?>
===DONE===
--EXPECT--
===DONE===