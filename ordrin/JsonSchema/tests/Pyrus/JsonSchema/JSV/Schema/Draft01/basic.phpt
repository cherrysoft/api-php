--TEST--
JsonSchema: basic types, no schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(new \stdClass), "Object");
$test->assertSchemaValidate($env->validate(array()), "Array");
$test->assertSchemaValidate($env->validate(''), "String");
$test->assertSchemaValidate($env->validate(00), "Number");
$test->assertSchemaValidate($env->validate(false), "Boolean");
// we can't do this because PHP has no distinction between null and undefined
//$test->assertSchemaValidate($env->validate(null), "Null");
?>
===DONE===
--EXPECT--
===DONE===