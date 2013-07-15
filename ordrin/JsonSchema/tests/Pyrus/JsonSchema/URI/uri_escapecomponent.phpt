--TEST--
JsonSchema: URI - escapeComponent
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$input = "";
$output = "";
for ($d = 32; $d < 128; ++$d) {
        $input .= chr($d);
        if (preg_match("/[0-9A-Za-z\-\.\_\~\!\$\&\'\(\)\*\+\,\;\=]/", chr($d))) {
            $output .= chr($d);
        } else {
            $output .= "%" . strtoupper(dechex($d));
        }
}
$input .= "\xc0\000\xa2\x30";
$output .= "%C3%80%E3%82%A2";

$test->assertEquals($output, $uri->escapeComponent($input), 'test');
?>
===DONE===
--EXPECT--
===DONE===