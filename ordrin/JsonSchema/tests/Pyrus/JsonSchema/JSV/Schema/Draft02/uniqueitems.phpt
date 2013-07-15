--TEST--
JsonSchema: uniqueItems, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidate($env->validate(array(), array('uniqueItems' => true)), "basic");
$test->assertSchemaValidate($env->validate(array(null), array('uniqueItems' => true)), "null");
$test->assertSchemaValidate($env->validate(array(true, false), array('uniqueItems' => true)), "bools");
$test->assertSchemaValidate($env->validate(array(1,2,3), array('uniqueItems' => true)), "numbers");
$test->assertSchemaValidate($env->validate(array('a','b'), array('uniqueItems' => true)), "strings");
$test->assertSchemaValidate($env->validate(array(array(),array(2)), array('uniqueItems' => true)), "arrays");
$test->assertSchemaValidate($env->validate(array(new stdClass,new stdClass), array('uniqueItems' => true)), "objects");

?>
===DONE===
--EXPECT--
===DONE===