--TEST--
JsonSchema: minItems/maxItems, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidate($env->validate(array(1), array('minItems' => 1, 'maxItems' => 1)), "1 min 1 max 1");
$test->assertSchemaValidate($env->validate(array(1), array('minItems' => 1, 'maxItems' => 3)), "1 min 1 max 3");
$test->assertSchemaValidate($env->validate(array(1, 2), array('minItems' => 1, 'maxItems' => 3)), "2 min 1 max 3");
$test->assertSchemaValidate($env->validate(array(1, 2, 3), array('minItems' => 1, 'maxItems' => 3)), "3 min 1 max 3");

?>
===DONE===
--EXPECT--
===DONE===