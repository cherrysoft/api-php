--TEST--
JsonSchema: minItems/maxItems, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('The number of items is less than the required minimum [schema path: #]'),
                                $env->validate(array(), array('minItems' => 1, 'maxItems' => 0)), "1 min 1 max 0");
$test->assertSchemaValidateFail(array('The number of items is less than the required minimum [schema path: #]'),
                                $env->validate(array(), array('minItems' => 1, 'maxItems' => 3)), "1 min 1 max 3");
$test->assertSchemaValidateFail(array('The number of items is greater than the required maximum [schema path: #]'),
                                $env->validate(array(1,2,3,4), array('minItems' => 1, 'maxItems' => 3)), "1 min 1 max 3");

?>
===DONE===
--EXPECT--
===DONE===