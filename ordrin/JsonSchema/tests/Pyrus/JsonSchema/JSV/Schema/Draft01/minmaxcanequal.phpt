--TEST--
JsonSchema: minimumCanEqual/maximumCanEqual, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$test->assertSchemaValidate($env->validate(1, array('minimum' => 1, 'maximum' => 10,
                                                    'minimumCanEqual' => true, 'maximumCanEqual' => true)), "1 min 1 max 10");
$test->assertSchemaValidate($env->validate(5, array('minimum' => 1, 'maximum' => 10,
                                                    'minimumCanEqual' => true, 'maximumCanEqual' => true)), "5 min 1 max 10");
$test->assertSchemaValidate($env->validate(10, array('minimum' => 1, 'maximum' => 10,
                                                     'minimumCanEqual' => true, 'maximumCanEqual' => true)), "10 min 1 max 10");
$test->assertSchemaValidate($env->validate(1, array('minimum' => 1, 'maximum' => 1,
                                                    'minimumCanEqual' => true, 'maximumCanEqual' => true)), "1 min 1 max 1");

$test->assertSchemaValidate($env->validate(1.0001, array('minimum' => 1, 'maximum' => 10,
                                                    'minimumCanEqual' => false, 'maximumCanEqual' => false)), "1.0001 min 1 max 10 false");
$test->assertSchemaValidate($env->validate(5, array('minimum' => 1, 'maximum' => 10,
                                                    'minimumCanEqual' => false, 'maximumCanEqual' => false)), "5 min 1 max 10 false");
$test->assertSchemaValidate($env->validate(9.9999, array('minimum' => 1, 'maximum' => 10,
                                                     'minimumCanEqual' => true, 'maximumCanEqual' => true)), "9.9999 min 1 max 10 false");

?>
===DONE===
--EXPECT--
===DONE===