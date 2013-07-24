--TEST--
JsonSchema: format, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidateFail(array('The number of decimal places of 0.1 is greater than the allowed maximum 0 [schema path: #]'),
                                $env->validate(0.1, array('maxDecimal' => 0)), "0.1 0");
$test->assertSchemaValidateFail(array('The number of decimal places of 0.111 is greater than the allowed maximum 1 [schema path: #]'),
                                $env->validate(0.111, array('maxDecimal' => 1)), "0.111 1");

?>
===DONE===
--EXPECT--
===DONE===