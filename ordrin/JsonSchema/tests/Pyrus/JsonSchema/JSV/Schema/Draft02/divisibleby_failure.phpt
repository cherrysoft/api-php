--TEST--
JsonSchema: divisibleBy, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaException(array('Number is less than the required minimum value [schema path: #/divisibleBy]'), function() use ($env) {
    $env->validate(0, array('divisibleBy' => 0));
}, "0 0");
$test->assertSchemaValidateFail(array('Number is not divisible by 5 [schema path: #]'),
                                $env->validate(7, array('divisibleBy' => 5)), "7 5");
$test->assertSchemaValidateFail(array('Number is not divisible by 2 [schema path: #]'),
                                $env->validate(4.5, array('divisibleBy' => 2)), "4.5 2");
$test->assertSchemaValidateFail(array('Number is not divisible by 7.5 [schema path: #]'),
                                $env->validate(2.5, array('divisibleBy' => 7.5)), "2.5 7.5");

?>
===DONE===
--EXPECT--
===DONE===