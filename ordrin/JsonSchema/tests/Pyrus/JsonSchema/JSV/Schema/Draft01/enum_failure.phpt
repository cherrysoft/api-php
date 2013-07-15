--TEST--
JsonSchema: enum, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Instance TRUE is not one of the possible values "false", "true" [schema path: #]'),
                                $env->validate(true, array('enum' => array('false', 'true'))), "true, enum is strings");
$test->assertSchemaValidateFail(array('Instance 4 is not one of the possible values 1, 2, 3, "4", "Fourscore and seven ..." [schema path: #]'),
                                $env->validate(4, array('enum' => array(1, 2, 3, '4',
                                                                        'Fourscore and seven years ago, I took a shower'))),
                                "4 enum contains other ints and strings");
$test->assertSchemaValidateFail(array('Instance "b" is not one of the possible values "a" [schema path: #.a]'),
                                $env->validate(array('a' => 'b'),
                                               array(
                                                     'properties' => array(
                                                        'a' => array(
                                                            'enum' => array('a')
                                                        )
                                                     )
                                                     )), "1234 min 1 max 3");

$test->assertSchemaException(array('The number of items is less than the required minimum [schema path: #.enum]'), function() use ($env) {
    $env->validate('', array('enum' => array()));
}, "simple");

?>
===DONE===
--EXPECT--
===DONE===