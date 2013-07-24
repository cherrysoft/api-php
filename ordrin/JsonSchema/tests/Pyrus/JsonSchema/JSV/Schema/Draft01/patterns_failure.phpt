--TEST--
JsonSchema: basic types, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('String does not match pattern [schema path: #]'),
                                $env->validate('', array('pattern' => '^ $')), "space");
$test->assertSchemaValidateFail(array('String does not match pattern [schema path: #]'),
                                $env->validate('today', array('pattern' => 'dam')), "dam");
$test->assertSchemaException(array('Regex "aa(a"is not a valid regular expression [schema path: #.pattern]',
                                   'String is not in the required format [schema path: #.pattern]'), function() use ($env) {
    $env->validate('aaaaa', array('pattern' => 'aa(a'));
}, "aa(a");
?>
===DONE===
--EXPECT--
===DONE===