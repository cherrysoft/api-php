--TEST--
JsonSchema: links, schema validation, Draft 03
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidate($env->validate(array('a' => array(), 'b' => array()),
                                           array(
                                            'type' => 'object',
                                            'additionalProperties' =>
                                            array('$ref' => '#')
                                                 )), 'link full');

$schema = $env->createSchema(array(
                                   "id" => "http://test.example.com/3",
                                   "properties" => array(
                                                         "test" => array("type" => "object")
                                                        ),
                                   "extends" => array('$ref' => "http://json-schema.org/hyper-schema")
                                  ), null, "http://test.example.com/3");
$test->assertSchemaValidate($env->validate(array('a' => array(), 'b' => array()),
                                           array(
                                            '$schema' => 'http://test.example.com/3',
                                            'test' => array())), 'link describedby');

$schema = $env->createSchema(array(
                                   "id" => "http://test.example.com/2",
                                   "properties" => array(
                                        "two" => array(
                                            "type" => "object"
                                            )
                                        )
                                  ), null, "http://not.example.com/2");
$test->assertSchemaValidate($env->validate(array('two' => array()),
                                           array(
                                            '$schema' => 'http://test.example.com/2',
                                            'two' => array())), 'link self');

$schema = $env->createSchema(array(
                            "links" => array(
                                array(
                                      "rel" => "bar",
                                      "href" => "http:{@}#"
                                     )
                                )
                            ));
$instance = $env->createInstance("foo");
$test->assertEquals("http:foo#", $schema->getLink("bar", $instance), "'bar' link and self reference @");


$schema = $env->createSchema(array(
                            "links" => array(
                                array(
                                      "rel" => "bar",
                                      "href" => "http:{nonexisting}#"
                                     )
                                )
                            ));
$instance = $env->createInstance("foo");
$test->assertEquals("http:#", $schema->getLink("bar", $instance), "'nonexisting' link to unknown value");

?>
===DONE===
--EXPECT--
===DONE===