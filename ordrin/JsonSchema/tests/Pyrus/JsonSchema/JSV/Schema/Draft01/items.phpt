--TEST--
JsonSchema: array items, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(array(),
                                           array('type' => 'array', 'items' => array('type' => 'string'))), "Array, no items");
$test->assertSchemaValidate($env->validate(array('hi'),
                                           array('type' => 'array', 'items' => array('type' => 'string'))), "Array, with items");
$test->assertSchemaValidate($env->validate(array('hi', 2),
                                           array('type' => 'array', 'items' => array(
                                                                                     array('type' => 'string'),
                                                                                     array('type' => 'number')
                                                                                    ))), "Array, with items of different types");
?>
===DONE===
--EXPECT--
===DONE===