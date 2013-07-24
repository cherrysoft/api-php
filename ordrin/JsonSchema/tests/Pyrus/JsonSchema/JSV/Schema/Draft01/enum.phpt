--TEST--
JsonSchema: enum, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidate($env->validate(true, array('enum' => array(false, true))), "bool");
$test->assertSchemaValidate($env->validate(2, array('enum' => array(1,2,3))), "2");
$test->assertSchemaValidate($env->validate('a', array('enum' => array('a'))), "a");
$test->assertSchemaValidate($env->validate(array(), array('properties' => array('a' => array('enum' => array('a'),
                                                                                             'optional' => true)))), "complex");
?>
===DONE===
--EXPECT--
===DONE===