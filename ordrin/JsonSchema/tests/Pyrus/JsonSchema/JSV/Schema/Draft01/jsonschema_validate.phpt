--TEST--
JsonSchema: schema validation of the basic schemas
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$schema = $env->findSchema("http://json-schema.org/schema");
$hyperSchema = $env->findSchema("http://json-schema.org/hyper-schema");
$links = $env->findSchema("http://json-schema.org/links");

$test->assertSchemaValidate($schema->validate($schema), 'schema->validate(schema)');
$test->assertSchemaValidate($hyperSchema->validate($schema), 'hyperschema->validate(schema)');
$test->assertSchemaValidate($hyperSchema->validate($hyperSchema), 'hyperschema->validate(hyperschema)');
$test->assertSchemaValidate($hyperSchema->validate($links), 'hyperschema->validate(links)');
?>
===DONE===
--EXPECT--
===DONE===