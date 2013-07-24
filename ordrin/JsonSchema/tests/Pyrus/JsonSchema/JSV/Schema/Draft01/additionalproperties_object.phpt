--TEST--
JsonSchema: additional properties (objects), schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$a = array('a' => 1, 'b' => 2, 'c' => 3);
$test->assertSchemaValidate($env->validate($a, array()), "Empty");
$test->assertSchemaValidate($env->validate($a, array('additionalProperties' => true)), "true additionalProperties");
$test->assertSchemaValidate($env->validate($a, array('properties' => array(
                                                                        'a' => array(),
                                                                        'b' => array(),
                                                                          ),'additionalProperties' => true)), "2 properties + true additionalProperties");
$test->assertSchemaValidate($env->validate($a, array('additionalProperties' => array('type' => 'number'))), "additionalProperties type=number");
$test->assertSchemaValidate($env->validate($a, array('properties' => array(
                                                                        'a' => array(),
                                                                        'b' => array(),
                                                                          ),'additionalProperties' => array(
                                                                            'type'=> 'number'
                                                                                                           ))),
                            "2 properties + additionalProperties type=number");
$test->assertSchemaValidate($env->validate($a, array('properties' => array(
                                                                        'a' => array(),
                                                                        'b' => array(),
                                                                        'c' => array(),
                                                                          ),'additionalProperties' => array(
                                                                            'type'=> 'string'
                                                                                                           ))),
                            "3 properties + additionalProperties type=string");
?>
===DONE===
--EXPECT--
===DONE===