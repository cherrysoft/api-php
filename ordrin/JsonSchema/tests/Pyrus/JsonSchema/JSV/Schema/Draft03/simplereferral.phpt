--TEST--
JsonSchema: Draft 3, simple referral
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/schema.setup.php.inc';

$schema = array('$ref'=> "http://example.com/subdir/card.json");

$test->assertSchemaValidateFail(array('Instance is not a required type: array [schema path: #]'),
                                $env->validate(new \stdClass,
                                           $schema),
                            "card must be array");
$test->assertSchemaValidateFail(array('The number of items is less than the required minimum [schema path: #]'),
                                $env->validate(array(),
                                           $schema),
                            "card must have fields");
$test->assertSchemaValidateFail(array('The number of items is less than the required minimum [schema path: #]'),
                                $env->validate(array('foo'),
                                           $schema),
                            "card must have two fields");
$test->assertSchemaValidateFail(array('Instance is not a required type: string [schema path: #/1]'),
                                $env->validate(array('foo', array()),
                                           $schema),
                            "both fields must be string");
$test->assertSchemaValidateFail(array('The number of items is greater than the required maximum [schema path: #]'),
                                $env->validate(array('foo', 'bar', 'baz'),
                                           $schema),
                            "card maxItems 2");
$test->assertSchemaValidate($env->validate(array('foo', 'bar'),
                                           $schema),
                            "valid card");
?>
===DONE===
--EXPECT--
===DONE===