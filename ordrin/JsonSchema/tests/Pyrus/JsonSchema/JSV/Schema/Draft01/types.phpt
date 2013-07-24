--TEST--
JsonSchema: basic types, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(new \stdClass, array('type' => 'object')), "Object");
$test->assertSchemaValidate($env->validate(array(), array('type' => 'array')), "Array");
$test->assertSchemaValidate($env->validate('', array('type' => 'string')), "String");
$test->assertSchemaValidate($env->validate(00, array('type' => 'number')), "Number");
$test->assertSchemaValidate($env->validate(false, array('type' => 'boolean')), "Boolean");
$test->assertSchemaValidate($env->validate(null, array('type' => 'null', 'optional' => true)), "Null");
$test->assertSchemaValidate($env->validate(true, array('type' => 'any')), "Any");

$test->assertSchemaValidate($env->validate(new \stdClass, array('type' => array('null', 'boolean', 'number', 'integer', 'string', 'array', 'object'))), "Union");
$test->assertSchemaValidate($env->validate(new \stdClass, array('type' => array(array('type' => 'string'), array('type' => 'object' )))), "Schema Union");
?>
===DONE===
--EXPECT--
===DONE===