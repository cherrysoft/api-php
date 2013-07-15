--TEST--
JsonSchema: Draft 3, validate addressbook, type is object
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/schema.setup.php.inc';

$a = function ($test_name, $schema_uri) use ($test, $env) {
    $schema = array( '$ref'=> $schema_uri );

    $test->assertSchemaValidateFail(array('Instance is not a required type: object [schema path: #]'), $env->validate('', $schema),
                                    "addressbook is object " . $test_name);
};

$a("Explicit Schema", "http://example.com/addressbook.json");
$a("Referring Schema", "http://example.com/addressbook_ref.json");
$a("Extends Schema", "http://example.com/addressbook_extends.json");
?>
===DONE===
--EXPECT--
===DONE===