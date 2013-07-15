--TEST--
JsonSchema: minLength/maxLength, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('String is less than the required minimum length [schema path: #]'),
                                $env->validate('', array('minLength' => 1, 'maxLength' => 0)), "'' min 1 max 0");
$test->assertSchemaValidateFail(array('String is less than the required minimum length [schema path: #]'),
                                $env->validate('', array('minLength' => 1, 'maxLength' => 3)), "'' min 1 max 3");
$test->assertSchemaValidateFail(array('String is greater than the required maximum length [schema path: #]'),
                                $env->validate('1234', array('minLength' => 1, 'maxLength' => 3)), "1234 min 1 max 3");

?>
===DONE===
--EXPECT--
===DONE===