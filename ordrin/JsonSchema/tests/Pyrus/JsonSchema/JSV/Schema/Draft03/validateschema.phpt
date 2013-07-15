--TEST--
JsonSchema: Draft 3, validate schema
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/schema.setup.php.inc';


$jsonschema = $env->findSchema($env->getOption("latestJSONSchemaSchemaURI"));
$explicit_schema = $env->findSchema("http://example.com/addressbook.json");
$referring_schema = $env->findSchema("http://example.com/addressbook_ref.json");
$card_schema = $env->findSchema("http://example.com/subdir/card.json");

$test->assertSame($addressbook, $explicit_schema, 'explicit');
$test->assertSame($addressbookref, $referring_schema, 'referring');
$test->assertSame($card, $card_schema, 'card');

$test->assertSchemaValidate($jsonschema->validate($explicit_schema), 'validate explicit');
$test->assertSchemaValidate($jsonschema->validate($referring_schema), 'validate referring');
$test->assertSchemaValidate($jsonschema->validate($card), 'validate card');
?>
===DONE===
--EXPECT--
===DONE===