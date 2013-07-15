--TEST--
JsonSchema: requires, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Number is less than the required minimum value [schema path: #]'),
                                $env->validate(0, array('minimum' => 1, 'maximum' => 10)), "0 min 1 max 10");
$test->assertSchemaValidateFail(array('Number is greater than the required maximum value [schema path: #]'),
                                $env->validate(11, array('minimum' => 1, 'maximum' => 10)), "11 min 1 max 10");


?>
===DONE===
--EXPECT--
===DONE===