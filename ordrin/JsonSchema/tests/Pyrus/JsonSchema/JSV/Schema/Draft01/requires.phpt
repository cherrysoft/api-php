--TEST--
JsonSchema: requires, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(array('a' => 1), array('properties' => array('a' => array(
                                                                                        'requires' => 'a'
                                                                                        )))), "simple");
$test->assertSchemaValidate($env->validate(array('a' => 1, 'b' => 2), array('properties' => array(
                                                                                                  'a' => array(
                                                                                                    'requires' => 'b'
                                                                                                   ),
                                                                                                  'b' => array(
                                                                                                    'requires' => 'a'
                                                                                                   )))), "circular requires");
$test->assertSchemaValidate($env->validate(array('a' => 1, 'b' => 2), array('properties' =>
                                                                            array(
                                                                                  'b' => array(
                                                                                    'properties' => array(
                                                                                        'a' => array(
                                                                                            'type' => 'number'
                                                                                        )
                                                                                    )
                                                                                              )
                                                                                 )
                                                                           )), "circular requires");

?>
===DONE===
--EXPECT--
===DONE===