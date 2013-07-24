--TEST--
JsonSchema: Draft 3, self-identifying schemas
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/schema.setup.php.inc';

$foo = $env->createSchema(array( "id"=> "http://example.com/foo.json",
                   '$schema'=>"http://json-schema.org/draft-03/hyper-schema#"
                 ));

$test->assertSame($foo, $env->findSchema("http://example.com/foo.json"), "found schema by id");

?>
===DONE===
--EXPECT--
===DONE===