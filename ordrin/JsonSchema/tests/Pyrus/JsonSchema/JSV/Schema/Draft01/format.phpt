--TEST--
JsonSchema: format, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$env->createSchema(array('enum' => array('Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul', 'Aug', 'Sep', 'Nov',
                                         'Dec')), null, 'http://example.com/months#');

$test->assertSchemaValidate($env->validate('Jan', array('format' => 'http://example.com/months#')), "Jan");
$test->assertSchemaValidate($env->validate('2010-10-08T23:15:16Z', array('format' => 'date-time')), "date-time");
$test->assertSchemaValidate($env->validate('2010-10-08', array('format' => 'date')), "date");
$test->assertSchemaValidate($env->validate('23:15:16', array('format' => 'time')), "time");
$test->assertSchemaValidate($env->validate('http://test:foo@example.com/my/%20path/blah.php?thing=mine#so',
                                           array('format' => 'uri')), "uri");
?>
===DONE===
--EXPECT--
===DONE===