--TEST--
JsonSchema: register a schema, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$schema = $env->createSchema(array('type' => 'string'), null, 'http://test.example.com/1');
$instance = $env->createInstance(array(), 'http://test.example.com/4');

$test->assertSchemaValidate($env->findSchema('http://json-schema.org/hyper-schema')->validate($schema), 'schema validate');
$test->assertSchemaValidate($env->validate('', array('$ref' => 'http://test.example.com/1')), 'basic');

?>
===DONE===
--EXPECT--
===DONE===