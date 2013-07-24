--TEST--
JsonSchema: Draft 3, forward referral
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/schema.setup.php.inc';

try {
    $env->createSchema(array(
                        "type"=> "object",
                        "id"=> "http://example.com/addressbook.json",
                        "properties"=> array(
                            "cards"=> array(
                                "type"=> "array",
                                "items"=> array(
                                    '$ref'=> "http://example.com/subdir/card.json"
                                ),
                                "required"=> true
                            )
                        ),
                        '$schema'=>"http://json-schema.org/draft-03/schema#"
                    ));
} catch (Exception $e) {
    $test->assertTrue(false, 'schema should create without error');
}
?>
===DONE===
--EXPECT--
===DONE===