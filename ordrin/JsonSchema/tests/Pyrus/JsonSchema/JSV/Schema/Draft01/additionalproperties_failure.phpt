--TEST--
JsonSchema: additional properties, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$a =array('a' => 1, 'b' => 2, 'c' => 3);
$b = array('1', '2', '3');
$test->assertSchemaValidateFail(array('Additional properties are not allowed [schema path: #]'),
                                $env->validate($a,
                                               array('properties' => array('a' => array(), 'b' => array()),
                                                     'additionalProperties' => false)), "additionalProperties=false");
$test->assertSchemaValidateFail(array('Instance is not a required type: string [schema path: #.c]'),
                                $env->validate($a,
                                               array('properties' => array('a' => array(), 'b' => array()),
                                                     'additionalProperties' => array('type' => 'string'))), "additionalProperties type=string");

$test->assertSchemaValidateFail(array('Additional items are not allowed [schema path: #]'),
                                $env->validate($b,
                                               array('items' => array(array('type' => 'string'), array('type' => 'string')),
                                                     'additionalProperties' => false)), "items, additionalProperties=false");
$test->assertSchemaValidateFail(array('Instance is not a required type: number [schema path: #.2]'),
                                $env->validate($b,
                                               array('items' => array(array('type' => 'string'), array('type' => 'string')),
                                                     'additionalProperties' => array('type' => 'number'))), "items, additionalProperties type=number");

?>
===DONE===
--EXPECT--
===DONE===