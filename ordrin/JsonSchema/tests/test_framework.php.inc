<?php
$__e = error_reporting();
error_reporting(E_ERROR|E_NOTICE|E_WARNING);
spl_autoload_register($a = function($class) {
	if (!file_exists(__DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php')) {
            return false;
        }
	include __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
	return true;
});
@include_once 'Text/Diff.php';
@include_once 'Text/Diff/Renderer.php';
@include_once 'Text/Diff/Renderer/unified.php';
error_reporting($__e);
class PEAR2_PHPT
{
    var $_diffonly;
    function __construct($diffonly = false)
    {
        $this->_diffonly = $diffonly;
        $this->_errors = array();
    }

    function assertTrue($test, $message)
    {
        if ($test === true) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-true value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertIsa($control, $test, $message)
    {
        if (is_a($test, $control)) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-$control object: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNull($test, $message)
    {
        if ($test === null) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-null value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotNull($test, $message)
    {
        if ($test !== null) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected null: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertSame($test, $test1, $message)
    {
        if ($test === $test1) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpectedly two vars are not the same thing: \n";
        echo "\n'$message'\n";
        return false;
    }

    function assertNotSame($test, $test1, $message)
    {
        if ($test !== $test1) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpectedly two vars are the same thing: \n";
        echo "\n'$message'\n";
        return false;
    }

    function assertFalse($test, $message)
    {
        if ($test === false) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected non-false value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotTrue($test, $message)
    {
        if (!$test) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected loose true value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertNotFalse($test, $message)
    {
        if ($test) {
            return true;
        }
        $this->_failTest(debug_backtrace(), $message);
        echo "Unexpected loose false value: \n";
        var_export($test);
        echo "\n'$message'\n";
        return false;
    }

    function assertRegex($regex, $test, $message)
    {
        if (!preg_match($regex, $test)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "Expecting:\nText Matching Regular Expression $regex\n";
            echo "\nReceived:\n";
            var_export($test);
            echo "\n";
            return false;
        }
        return true;
    }

    function assertException($exception, $class, $emessage, $message, $trace = null)
    {
        if (!$trace) {
            $trace = debug_backtrace();
        }
        $success = true;
        if (!($exception instanceof $class)) {
            $this->_failTest($trace, $message);
            echo "Expecting class $class, got ", get_class($exception);
            $success = false;
        }
        $success = $success && $this->assertEquals($emessage, $exception->getMessage(), $message, $trace);
        return $success;
    }

    function assertEquals($control, $test, $message, $trace = null)
    {
        if (!$trace) {
            $trace = debug_backtrace();
        }
        if (str_replace(array("\r", "\n"), array('', ''),
            var_export($control, true)) != str_replace(array("\r", "\n"), array('', ''),
            var_export($test, true))) {
            $this->_failTest($trace, $message);
            if (class_exists('Text_Diff')) {
                echo "Diff of expecting/received:\n";
                $diff = new Text_Diff(
                    explode("\n", var_export($control, true)),
                    explode("\n", var_export($test, true)));

                // Output the diff in unified format.
                $renderer = new Text_Diff_Renderer_unified();
                echo $renderer->render($diff);
                if ($this->_diffonly) {
                    return false;
                }
            }
            echo "Expecting:\n";
            var_export($control);
            echo "\nReceived:\n";
            var_export($test);
            echo "\n";
            return false;
        }
        return true;
    }

    function assertFileExists($fname, $message)
    {
        if (!@file_exists($fname)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "File '$fname' does not exist, and should\n";
            return false;
        }
        return true;
    }

    function assertFileNotExists($fname, $message)
    {
        if (@file_exists($fname)) {
            $this->_failTest(debug_backtrace(), $message);
            echo "File '$fname' exists, and should not\n";
            return false;
        }
        return true;
    }

    function assertSchemaValidate($report, $message)
    {
        if ($this->assertEquals(0, count($report->errors), $message)) return true;
        $trace = debug_backtrace();
        foreach ($report->errors as $error) {
            $this->_failTest($trace, "validation failed with message: " . $error->getMessage());
        }
        return false;
    }

    function assertSchemaValidateFail($expected, $report, $message)
    {
        settype($expected, 'array');
        if (!$this->assertEquals(count($expected), count($report->errors), "Error count did not match for " . $message)) {
            if (!count($report->errors)) return false;
        }
        $trace = debug_backtrace();
        $result = true;
        foreach ($report->errors as $i => $error) {
            if (!isset($expected[$i])) $expected[$i] = '';
            if ($expected[$i] != $error->getMessage()) {
                $this->_failTest($trace, 'Expected message "' . $expected[$i] . '" but got "' . $error->getMessage() . '"');
                $result = false;
            }
        }
        return $result;
    }

    function assertSchemaException($expected, Closure $func, $message)
    {
        $trace = debug_backtrace();
        try {
            $func();
            throw new Exception('$$$');
        } catch (Exception $e) {
            if ($e->getMessage() == '$$$') {
                $this->_failTest($trace, 'no exception thrown: ' . $message);
                return false;
            }
            if (!$this->assertException($e, 'Pyrus\JsonSchema\JSV\Environment\Exception', 'Schema validation failed', $message, $trace)) {
                return false;
            }
        }
        $report = $e->report;
        $result = true;
        foreach ($report->errors as $i => $error) {
            if (!isset($expected[$i])) $expected[$i] = '';
            if ($expected[$i] != $error->getMessage()) {
                $this->_failTest($trace, 'Expected message "' . $expected[$i] . '" but got "' . $error->getMessage() . '"');
                $result = false;
            }
        }
        return $result;
    }

    function _failTest($trace, $message)
    {
        echo 'Test Failure: "' . $message  . "\"\n in " . $trace[0]['file'] . ' line ' .
            $trace[0]['line'] . "\n";
    }

    function showAll()
    {
        $this->_diffonly = false;
    }
}
$test = new PEAR2_PHPT();
?>
