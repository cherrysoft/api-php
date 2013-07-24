--TEST--
JsonSchema: uniqueItems, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Array can only contain unique items [schema path: #]'),
                                $env->validate(array(false, false), array('uniqueItems' => true)), "bools");
$test->assertSchemaValidateFail(array('Array can only contain unique items [schema path: #]'),
                                $env->validate(array(1,2,1), array('uniqueItems' => true)), "numbers");
$test->assertSchemaValidateFail(array('Array can only contain unique items [schema path: #]'),
                                $env->validate(array('a','b','a'), array('uniqueItems' => true)), "letters");

?>
===DONE===
--EXPECT--
===DONE===