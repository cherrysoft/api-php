--TEST--
JsonSchema: additional properties (arrays), schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$a = array(1, 2, 3);
$test->assertSchemaValidate($env->validate($a, array()), "Empty");
$test->assertSchemaValidate($env->validate($a, array('additionalProperties' => true)), "true additionalProperties");
$test->assertSchemaValidate($env->validate($a, array('additionalProperties' => false)), "false additionalProperties");
$test->assertSchemaValidate($env->validate($a, array('additionalProperties' => array('type' => 'number'))), "additionalProperties type=number");
$test->assertSchemaValidate($env->validate($a, array('additionalProperties' => array('type' => 'string'))), "additionalProperties type=string");
$test->assertSchemaValidate($env->validate(array('1', '2'), array('items' => array('type' => 'string'), 'additionalProperties' => false)),
                            "1 item + false additionalProperties");
$test->assertSchemaValidate($env->validate(array('1', '2'), array('items' => array(array('type' => 'string'),
                                                                                   array('type' => 'string')), 'additionalProperties' => false)),
                            "2 items + false additionalProperties");
$test->assertSchemaValidate($env->validate(array('1', '2', 3), array('items' => array(array('type' => 'string'),
                                                                                   array('type' => 'string')),
                                                                  'additionalProperties' => array('type' => 'number'))),
                            "2 items + additionalProperties type=number");
$test->assertSchemaValidate($env->validate(array('1', '2', '3'), array('items' => array(array('type' => 'string'),
                                                                                   array('type' => 'string'), array('type' => 'string')),
                                                                  'additionalProperties' => array('type' => 'number'))),
                            "3 items + additionalProperties type=number");
?>
===DONE===
--EXPECT--
===DONE===