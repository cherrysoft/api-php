--TEST--
JsonSchema: links, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidateFail(array('Instance is not a required type: object [schema path: #.b]'),
                                $env->validate(array('a' => array(), 'b' => 1),
                                           array(
                                            'type' => 'object',
                                            'additionalProperties' =>
                                            array('$ref' => '#')
                                                 )),
                            "link full");

$schema = $env->createSchema(array(
                                   "id" => "http://test.example.com/3",
                                   "properties" => array(
                                                         "test" => array("type" => "object")
                                                        ),
                                   "extends" => array('$ref' => "http://json-schema.org/hyper-schema")
                                  ), null, "http://test.example.com/3");

$test->assertSchemaException(array('Instance is not a required type: object [schema path: #.test]'), function() use ($env) {
    $env->validate(array('a' => array(), 'b' => 1),
                   array(
                        '$schema' => 'http://test.example.com/3',
                        'test' => 0
                        ));
}, "link describedby");


$schema = $env->createSchema(array(
                                   "id" => "http://test.example.com/2",
                                   "properties" => array(
                                        "two" => array(
                                            "type" => "object"
                                            )
                                        )
                                  ), null, "http://not.example.com/2");


$test->assertSchemaException(array('Instance is not a required type: object [schema path: #.two]'), function() use ($env) {
    $env->validate(array(),
                   array(
                        '$schema' => 'http://not.example.com/2',
                        'two' => 0
                        ));
}, "link describedby");
?>
===DONE===
--EXPECT--
===DONE===