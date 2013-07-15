--TEST--
JsonSchema: extends, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Instance is not a required type: string [schema path: #]'),
                                $env->validate(1, array('type' => 'number', 'extends' => array('type' => 'string'))),
                            "redefine string as number");
?>
===DONE===
--EXPECT--
===DONE===