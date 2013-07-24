--TEST--
JsonSchema: register a schema, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$instance = $env->createInstance(array(), 'http://foo.example.com/4');

$schema = $env->createSchema(array('type' => 'string'), null, 'http://test.example.com/1');
$instance = $env->createInstance(array(), 'http://test.example.com/4');

$test->assertSchemaValidateFail(array('Instance is not a required type: string [schema path: #]'),
                                $env->validate(array(),
                                           array(
                                            '$ref' => 'http://test.example.com/1')),
                            "wrong type");
?>
===DONE===
--EXPECT--
===DONE===