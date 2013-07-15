--TEST--
JsonSchema: disallow, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaValidate($env->validate(new \stdClass, array('disallow' => array('null', 'boolean', 'number', 'integer', 'string', 'array'))),
                            "Object");
$test->assertSchemaValidate($env->validate(array(2), array('disallow' => array('null', 'boolean', 'number', 'integer', 'string', 'object'))),
                            "array");
$test->assertSchemaValidate($env->validate('', array('disallow' => array('null', 'boolean', 'number', 'integer', 'array', 'object'))),
                            "string");
$test->assertSchemaValidate($env->validate(0.1, array('disallow' => array('null', 'boolean', 'string', 'integer', 'array', 'object'))),
                            "number");
$test->assertSchemaValidate($env->validate(1, array('disallow' => array('null', 'boolean', 'string', 'array', 'object'))),
                            "integer");
$test->assertSchemaValidate($env->validate(false, array('disallow' => array('null', 'integer', 'string', 'number', 'array', 'object'))),
                            "bool");
$test->assertSchemaValidate($env->validate(null, array('disallow' => array('integer', 'boolean', 'string', 'number', 'array', 'object'),
                                                       'optional' => true)),
                            "null");
?>
===DONE===
--EXPECT--
===DONE===