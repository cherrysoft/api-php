--TEST--
JsonSchema: Draft 3, validate addressbook
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/schema.setup.php.inc';

$a = function ($test_name, $schema_uri) use ($test, $env) {
    $schema = array( '$ref'=> $schema_uri );

    $test->assertSchemaValidate($env->validate(array( "cards"=> array(array("foo", "bar"))), $schema),
          "good addressbook with one card " . $test_name);

    $test->assertSchemaValidate($env->validate(array( "cards"=> array(array("foo", "bar"), array("bar", "foo"))), $schema),
          "good addressbook with two cards " . $test_name);
};

$a("Explicit Schema", "http://example.com/addressbook.json");
$a("Referring Schema", "http://example.com/addressbook_ref.json");
$a("Extends Schema", "http://example.com/addressbook_extends.json");
?>
===DONE===
--EXPECT--
===DONE===