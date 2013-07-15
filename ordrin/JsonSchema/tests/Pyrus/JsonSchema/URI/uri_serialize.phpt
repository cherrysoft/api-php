--TEST--
JsonSchema: URI - serialize
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$components = new Pyrus\JsonSchema\URI\Components;
$components->scheme = '%http';
$components->userinfo = 'babyface:boo';
$components->host = 'far.example.com';
$components->port = '8080';
$components->path = '/hi.my.thingy/./there';
$components->query = 'my=1&there=there';
$components->fragment = '#';
$test->assertEquals('http://babyface:boo@far.example.com/hi.my.thingy/there?my=1&there=there#%23', $uri->serialize($components), 'serialize');
?>
===DONE===
--EXPECT--
===DONE===