--TEST--
JsonSchema: requires, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Property requires sibling property "a" [schema path: #.b]'),
                                $env->validate(array('b' => 2),
                                               array('properties' => array('b' => array('requires' => 'a')))),
                                "no a property");
$test->assertSchemaValidateFail(array('Property requires sibling property "c" [schema path: #.b]'),
                                $env->validate(array('a' => 1, 'b' => 2),
                                               array('properties' =>
                                                     array('a' =>
                                                           array('requires' => 'b'),
                                                           'b' =>
                                                           array('requires' => 'c')
                                                           ),
                                                     )),
                                "no c property");
$test->assertSchemaValidateFail(array('Instance is not a required type: string [schema path: #.b]'),
                                $env->validate(array('b' => 2),
                                               array('properties' =>
                                                     array('b' =>
                                                           array(
                                                                 'requires' => array(
                                                                    'properties' => array(
                                                                        'b' => array('type' => 'string')
                                                                    )
                                                                 )
                                                           ),
                                                     ))),
                                "no a property");

?>
===DONE===
--EXPECT--
===DONE===