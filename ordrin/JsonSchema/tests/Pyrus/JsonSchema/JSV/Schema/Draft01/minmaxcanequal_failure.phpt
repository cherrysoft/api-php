--TEST--
JsonSchema: minimumCanEqual/maximumCanEqual, schema validation, failure test
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertSchemaException(array('Property requires sibling property "minimum" [schema path: #.minimumCanEqual]',
                                   'Property requires sibling property "maximum" [schema path: #.maximumCanEqual]'), function() use ($env) {
    $env->validate(0, array('minimumCanEqual' => true, 'maximumCanEqual' => true));
}, "simple");
$test->assertSchemaException(array('Property requires sibling property "minimum" [schema path: #.minimumCanEqual]',
                                   'Property requires sibling property "maximum" [schema path: #.maximumCanEqual]'), function() use ($env) {
    $env->validate(0, array('minimumCanEqual' => false, 'maximumCanEqual' => false));
}, "simple false");

$test->assertSchemaValidateFail(array('Number is less than the required minimum value [schema path: #]'),
                                $env->validate(0, array('minimum' => 1, 'maximum' => 10,
                                                        'minimumCanEqual' => true, 'maximumCanEqual' => true)), "0 min 1 max 10");
$test->assertSchemaValidateFail(array('Number is greater than the required maximum value [schema path: #]'),
                                $env->validate(11, array('minimum' => 1, 'maximum' => 10,
                                                         'minimumCanEqual' => true, 'maximumCanEqual' => true)), "11 min 1 max 10");

$test->assertSchemaValidateFail(array('Number is less than the required minimum value [schema path: #]'),
                                $env->validate(0, array('minimum' => 1, 'maximum' => 10,
                                                        'minimumCanEqual' => false, 'maximumCanEqual' => false)), "0 min 1 max 10 false");
$test->assertSchemaValidateFail(array('Number is greater than the required maximum value [schema path: #]'),
                                $env->validate(11, array('minimum' => 1, 'maximum' => 10,
                                                         'minimumCanEqual' => false, 'maximumCanEqual' => false)), "11 min 1 max 10 false");
$test->assertSchemaValidateFail(array('Number is less than the required minimum value [schema path: #]'),
                                $env->validate(1, array('minimum' => 1, 'maximum' => 10,
                                                        'minimumCanEqual' => false, 'maximumCanEqual' => false)), "1 min 1 max 10 false");
$test->assertSchemaValidateFail(array('Number is greater than the required maximum value [schema path: #]'),
                                $env->validate(10, array('minimum' => 1, 'maximum' => 10,
                                                         'minimumCanEqual' => false, 'maximumCanEqual' => false)), "10 min 1 max 10 false");
$test->assertSchemaValidateFail(array('Number is less than the required minimum value [schema path: #]',
                                      'Number is greater than the required maximum value [schema path: #]'),
                                $env->validate(1, array('minimum' => 1, 'maximum' => 1,
                                                         'minimumCanEqual' => false, 'maximumCanEqual' => false)), "1 min 1 max 1 false");

?>
===DONE===
--EXPECT--
===DONE===