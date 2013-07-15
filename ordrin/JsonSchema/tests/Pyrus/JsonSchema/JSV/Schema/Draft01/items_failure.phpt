--TEST--
JsonSchema: basic types, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Instance is not a required type: string [schema path: #.0]'),
                                $env->validate(array(1),
                                               array('type' => 'array', 'items' => array('type' => 'string'))), "non-matching item type");
$test->assertSchemaValidateFail(array('Instance is not a required type: number [schema path: #.1]'),
                                $env->validate(array('foo', 'foo'),
                                               array('type' => 'array', 'items' => array(
                                                                                     array('type' => 'string'),
                                                                                     array('type' => 'number')
                                                                                    ))), "non-matching item type, type is schema");

?>
===DONE===
--EXPECT--
===DONE===