--TEST--
JsonSchema: optional properties, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(array(),
                                           array('properties' => array(
                                                'a' => array('optional' => true)
                                                ))), "Array, no items");
$test->assertSchemaValidate($env->validate(array('a' => false),
                                           array('properties' => array(
                                                'a' => array('optional' => true)
                                                ))), "Array, no items");
$test->assertSchemaValidate($env->validate(array('a' => false),
                                           array('properties' => array(
                                                'a' => array('optional' => false)
                                                ))), "Array, no items");

?>
===DONE===
--EXPECT--
===DONE===