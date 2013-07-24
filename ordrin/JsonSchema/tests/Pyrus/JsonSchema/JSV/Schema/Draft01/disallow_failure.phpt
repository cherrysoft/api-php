--TEST--
JsonSchema: disallow, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Instance object is a disallowed type, cannot be any of: object [schema path: #]'),
                                $env->validate(new \stdClass, array('disallow' => 'object')), "Object");
$test->assertSchemaValidateFail(array('Instance array is a disallowed type, cannot be any of: array [schema path: #]'),
                                $env->validate(array(), array('disallow' => 'array')), "array");
$test->assertSchemaValidateFail(array('Instance string is a disallowed type, cannot be any of: string [schema path: #]'),
                                $env->validate('', array('disallow' => 'string')), "string");
$test->assertSchemaValidateFail(array('Instance integer is a disallowed type, cannot be any of: integer [schema path: #]'),
                                $env->validate(1, array('disallow' => 'integer')), "integer");
$test->assertSchemaValidateFail(array('Instance number is a disallowed type, cannot be any of: number [schema path: #]'),
                                $env->validate(0.2, array('disallow' => 'number')), "number");
$test->assertSchemaValidateFail(array('Instance boolean is a disallowed type, cannot be any of: boolean [schema path: #]'),
                                $env->validate(false, array('disallow' => 'boolean')), "boolean");
$test->assertSchemaValidateFail(array('Instance null is a disallowed type, cannot be any of: any [schema path: #]'),
                                $env->validate(null, array('disallow' => 'any', 'optional' => true)), "boolean");
$test->assertSchemaValidateFail(array('Instance null is a disallowed type, cannot be any of: null [schema path: #]'),
                                $env->validate(null, array('disallow' => 'null', 'optional' => true)), "boolean");
?>
===DONE===
--EXPECT--
===DONE===