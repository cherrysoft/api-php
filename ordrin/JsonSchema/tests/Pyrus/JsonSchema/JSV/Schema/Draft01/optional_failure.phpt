--TEST--
JsonSchema: optional properties, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Property "a" is required [schema path: #.a]'),
                                $env->validate(array(),
                                           array('properties' => array(
                                                'a' => array('optional' => false)
                                                ))), "non-matching item type");
$test->assertSchemaValidateFail(array('Instance is not a required type: number [schema path: #.1]'),
                                $env->validate(array('foo', 'foo'),
                                               array('type' => 'array', 'items' => array(
                                                                                     array('type' => 'string'),
                                                                                     array('type' => 'number')
                                                                                    ))), "non-matching item type, type is schema");

?>
===DONE===
--EXPECT--
===DONE===