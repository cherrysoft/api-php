--TEST--
JsonSchema: divisibleBy, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidate($env->validate(0, array('divisibleBy' => 1)), "0 1");
$test->assertSchemaValidate($env->validate(10, array('divisibleBy' => 5)), "10 5");
$test->assertSchemaValidate($env->validate(10, array('divisibleBy' => 10)), "10 10");
$test->assertSchemaValidate($env->validate(0, array('divisibleBy' => 2.5)), "0 2.5");
$test->assertSchemaValidate($env->validate(5, array('divisibleBy' => 2.5)), "5 2.5");
$test->assertSchemaValidate($env->validate(7.5, array('divisibleBy' => 2.5)), "7.5 2.5");

?>
===DONE===
--EXPECT--
===DONE===