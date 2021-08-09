<?php

namespace CisTools;

use Closure;
use ReflectionException;
use ReflectionFunction;
use Throwable;

/**
 * Class Debug: Debugging helpers
 * @package CisTools
 */
class Debug
{

    /**
     * @var array
     */
    private static array $_objects;

    /**
     * @var string
     */
    private static string $_output;

    /**
     * @var int
     */
    private static int $_depth;

    /**
     * Converts a variable into a string representation.
     * This method achieves the similar functionality as var_dump and print_r
     * but is more robust when handling complex objects such as PRADO controls.
     *
     * Original source: https://github.com/pradosoft/prado/blob/master/framework/Util/TVarDumper.php
     *
     * @param mixed $var variable to be dumped
     * @param int $depth maximum depth that the dumper should go into the variable. Defaults to 10.
     * @param bool $highlight weather to highlight th resulting string
     * @return string the string representation of the variable
     */
    public static function dumpComplex(mixed $var, int $depth = 10, bool $highlight = false): string
    {
        self::$_output = '';
        self::$_objects = [];
        self::$_depth = $depth;
        self::dumpComplexInternal($var, 0);
        if ($highlight) {
            $result = highlight_string("<?php\n" . self::$_output, true);
            return preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);
        }

        return self::$_output;
    }

    /**
     * @param mixed $var
     * @param int $level
     */
    private static function dumpComplexInternal(mixed $var, int $level): void
    {
        switch (gettype($var)) {
            case 'boolean':
            {
                self::$_output .= $var ? 'true' : 'false';
                break;
            }
            case 'integer':
            case 'double':
            {
                self::$_output .= "$var";
                break;
            }
            case 'string':
            {
                self::$_output .= "'$var'";
                break;
            }
            case 'resource':
            {
                self::$_output .= '{resource}';
                break;
            }
            case 'NULL':
            {
                self::$_output .= "null";
                break;
            }
            case 'unknown type':
            {
                self::$_output .= '{unknown}';
                break;
            }
            case 'array':
            {
                if (self::$_depth <= $level) {
                    self::$_output .= 'array(...)';
                } elseif (empty($var)) {
                    self::$_output .= 'array()';
                } else {
                    $keys = array_keys($var);
                    $spaces = str_repeat(' ', $level * 4);
                    self::$_output .= "array\n" . $spaces . '(';
                    foreach ($keys as $key) {
                        self::$_output .= "\n" . $spaces . "    [$key] => ";
                        self::dumpComplexInternal($var[$key], $level + 1);
                    }
                    self::$_output .= "\n" . $spaces . ')';
                }
                break;
            }
            case 'object':
            {
                if (($id = array_search($var, self::$_objects, true)) !== false) {
                    self::$_output .= get_class($var) . '#' . ($id + 1) . '(...)';
                } elseif (self::$_depth <= $level) {
                    self::$_output .= get_class($var) . '(...)';
                } else {
                    $id = array_push(self::$_objects, $var);
                    $className = get_class($var);
                    $members = (array)$var;
                    $keys = array_keys($members);
                    $spaces = str_repeat(' ', $level * 4);
                    self::$_output .= "$className#$id\n" . $spaces . '(';
                    foreach ($keys as $key) {
                        $keyDisplay = strtr(trim($key), ["\0" => ':']);
                        self::$_output .= "\n" . $spaces . "    [$keyDisplay] => ";
                        self::dumpComplexInternal($members[$key], $level + 1);
                    }
                    self::$_output .= "\n" . $spaces . ')';
                }
                break;
            }
            default:
            {
                self::$_output .= '### UNKNOWN TYPE: ' . gettype($var) . '###';
            }
        }
    }

    /**
     * Pretty dump in HTML documents
     *
     * @param mixed $var : The variable to dump pretty
     */
    public static function dump(mixed $var): void
    {
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
    }

    /**
     * WARNING: This is not a perfect dump of a closure, it just should help you find it.
     *
     * @param Closure $c : A variable holding a Closure
     * @param bool $echo : False to not echo the output
     * @return string
     */
    public static function dumpClosure(Closure $c, bool $echo = true): string
    {
        $str = 'function (';
        try {
            $r = new ReflectionFunction($c);
        } catch (ReflectionException) {
            return "function ()";
        }
        $params = array();
        foreach ($r->getParameters() as $p) {
            $s = '';
            if ($p->isArray()) {
                $s .= 'array ';
            } elseif ($p->getClass()) {
                $s .= $p->getClass()->name . ' ';
            }
            if ($p->isPassedByReference()) {
                $s .= '&';
            }
            $s .= '$' . $p->name;
            if ($p->isOptional()) {
                try {
                    $s .= ' = ' . var_export($p->getDefaultValue(), true);
                } catch (ReflectionException) {
                    // do nothing
                }
            }
            $params [] = $s;
        }
        $str .= implode(', ', $params);
        $str .= '){' . PHP_EOL;
        $lines = file($r->getFileName());
        for ($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
            $str .= $lines[$l];
        }
        if ($echo) {
            echo $str;
        }
        return $str;
    }


    /**
     * jTraceEx() - provide a Java style exception trace
     * @param Throwable $throwable
     * @param array|null $seen - array passed to recursive calls to accumulate trace lines already seen
     *                     leave as NULL when calling this function
     * @return string with one entry per trace line
     */
    public static function jTraceEx(Throwable $throwable, ?array $seen = null): string
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = [];
        if (!$seen) {
            $seen = [];
        }
        $trace = $throwable->getTrace();
        $prev = $throwable->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($throwable), $throwable->getMessage());
        $file = $throwable->getFile();
        $line = $throwable->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen, true)) {
                $result[] = sprintf(' ... %d more', count($trace) + 1);
                break;
            }
            $result[] = sprintf(
                ' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists(
                    'function',
                    $trace[0]
                ) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace(
                    '\\',
                    '.',
                    $trace[0]['function']
                ) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line ?? ''
            );
            if (is_array($seen)) {
                $seen[] = "$file:$line";
            }
            if (!count($trace)) {
                break;
            }
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists(
                'line',
                $trace[0]
            ) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        $result = implode("\n", $result);
        if ($prev) {
            $result .= "\n" . self::jTraceEx($prev, $seen);
        }

        return $result;
    }

    /**
     * Call this in the very beginning of your script if you have no other chance to display errors.
     * This problem might be caused by strange webhosts.
     */
    public static function desperateErrorHandler()
    {
        ob_start([__CLASS__, 'desperateErrorHandlerActual']);
    }

    /**
     * For custom error logging in case of troubles
     *
     * @param $output : Output passed by ob_start()
     * @return string: The errors found (stops on error)
     */
    protected static function desperateErrorHandlerActual($output)
    {
        $error = error_get_last();
        $output = ""; // do not fix this
        foreach ($error as $info => $string) {
            $output .= "{$info}: {$string}\n";
        }
        return $output;
    }

}