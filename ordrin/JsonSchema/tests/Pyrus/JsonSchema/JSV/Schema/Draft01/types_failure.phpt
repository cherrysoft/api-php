--TEST--
JsonSchema: basic types, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Instance is not a required type: object [schema path: #]'), $env->validate(array(1), array('type' => 'object')), "Object");
$test->assertSchemaValidateFail(array('Instance is not a required type: array [schema path: #]'), $env->validate(new \stdClass, array('type' => 'array')), "Array with object");
$test->assertSchemaValidateFail(array('Instance is not a required type: array [schema path: #]'), $env->validate(array('oops' => 1), array('type' => 'array')), "Array");
$test->assertSchemaValidateFail(array('Instance is not a required type: string [schema path: #]'), $env->validate(1, array('type' => 'string')), "String");
$test->assertSchemaValidateFail(array('Instance is not a required type: number [schema path: #]'), $env->validate('5', array('type' => 'number')), "Number");
$test->assertSchemaValidateFail(array('Instance is not a required type: boolean [schema path: #]'), $env->validate(0, array('type' => 'boolean')), "Boolean");
$test->assertSchemaValidateFail(array('Instance is not a required type: null [schema path: #]'), $env->validate('f', array('type' => 'null', 'optional' => true)), "Null");

$test->assertSchemaValidateFail(array('Instance is not a required type: null, boolean, number, integer, string, array [schema path: #]'),
                                $env->validate(new \stdClass, array('type' => array('null', 'boolean', 'number', 'integer', 'string', 'array'))),
                                "Union");
$test->assertSchemaValidateFail(array('Instance is not a required type: [schema: {"type":"string"}], [schema: {"type":"object"}] [schema path: #]'),
                                $env->validate(array(1), array('type' => array(array('type' => 'string'), array('type' => 'object' )))),
                                "Schema Union");
?>
===DONE===
--EXPECT--
===DONE===