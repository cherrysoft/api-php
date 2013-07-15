--TEST--
JsonSchema: extends, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(array(), array('extends' => array())),
                            "basic");
$test->assertSchemaValidate($env->validate(array(), array('extends' => array('type' => 'object'))),
                            "basic 2");
$test->assertSchemaValidate($env->validate(1, array('type' => 'integer', 'extends' => array('type' => 'number'))),
                            "redefine number as integer");
$test->assertSchemaValidate($env->validate(array('a' => 1, 'b' => 2),
                                           array('properties' =>
                                                 array(
                                                       'a' => array('type' => 'number')
                                                      ),
                                                 'additionalProperties' => false,
                                                 'extends' => array(
                                                    'properties' => array(
                                                        'b' => array('type' => 'number')
                                                    )
                                                                   ))),
                            "add a property");
?>
===DONE===
--EXPECT--
===DONE===