--TEST--
JsonSchema: format, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$env->createSchema(array('enum' => array('Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul', 'Aug', 'Sep', 'Nov',
                                         'Dec')), null, 'http://example.com/months#');

$test->assertSchemaValidateFail(array('Instance "Oops" is not one of the possible values "Jan", "Feb", "Mar", "Apr", ' .
                                      '"Jun", "Jul", "Aug", "Sep", "Nov", "Dec" [schema path: #]',
                                      'String is not in the required format [schema path: #]'),
                                $env->validate('Oops', array('format' => 'http://example.com/months#')), "uri format");
$test->assertSchemaValidateFail(array('Date-time "2010-13-08T23:15:16Z" is not valid [schema path: #]',
                                      'String is not in the required format [schema path: #]'),
                                $env->validate('2010-13-08T23:15:16Z', array('format' => 'date-time')), "date-time");
$test->assertSchemaValidateFail(array('Date "2010-13-08" is not valid [schema path: #]',
                                      'String is not in the required format [schema path: #]'),
                                $env->validate('2010-13-08', array('format' => 'date')), "date");
$test->assertSchemaValidateFail(array('Time "23:15:69" is not valid [schema path: #]',
                                      'String is not in the required format [schema path: #]'),
                                $env->validate('23:15:69', array('format' => 'time')), "time");
$test->assertSchemaValidateFail(array('URI "http://test:foo@example.com /my/%20path/blah.php?thing=mine#so" is not valid [schema path: #]',
                                      'String is not in the required format [schema path: #]'),
                                $env->validate('http://test:foo@example.com /my/%20path/blah.php?thing=mine#so',
                                           array('format' => 'uri')), "uri");

?>
===DONE===
--EXPECT--
===DONE===