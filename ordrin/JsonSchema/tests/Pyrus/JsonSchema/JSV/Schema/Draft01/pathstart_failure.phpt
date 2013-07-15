--TEST--
JsonSchema: pathStart, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$instance = $env->createInstance(array(), 'http://foo.example.com/4');

$test->assertSchemaValidateFail(array('Instance\'s URI does not start with http://test.example.com/4 [schema path: #]'),
                                $env->validate(array(),
                                           array(
                                            'pathStart' => 'http://test.example.com/4')),
                            "pathStart");
?>
===DONE===
--EXPECT--
===DONE===