--TEST--
JsonSchema: properties, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(array(),
                                           array('type' => 'object', 'properties' => array())), "Object, no properties");
$test->assertSchemaValidate($env->validate(array('a' => 1),
                                           array('type' => 'object', 'properties' => array(
                                                                                           'a' => array()))), "Object, property, no constraints");
$test->assertSchemaValidate($env->validate(array('a' => 1),
                                           array('type' => 'object', 'properties' => array(
                                                                                           'a' => array(
                                                                                                        'type' => 'number'
                                                                                                        )))), "Object, property, type constraint");
$test->assertSchemaValidate($env->validate(array('a' => array('b' => 'two')),
                                           array('type' => 'object', 'properties' => array(
                                                                                           'a' => array(
                                                                                                        'type' => 'object',
                                                                                                        'properties' => array(
                                                                                                            'b' => array(
                                                                                                                'type' => 'string'
                                                                                                            )
                                                                                                        )
                                                                                                        )))), "Object, property, type constraint");
?>
===DONE===
--EXPECT--
===DONE===