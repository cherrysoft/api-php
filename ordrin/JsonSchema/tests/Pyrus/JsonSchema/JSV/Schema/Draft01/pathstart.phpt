--TEST--
JsonSchema: pathStart, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$instance = $env->createInstance(array(), 'http://test.example.com/4');

$test->assertSchemaValidate($env->validate($instance,
                                           array(
                                            'pathStart' => 'http://test.example.com')), 'pathstart');

?>
===DONE===
--EXPECT--
===DONE===