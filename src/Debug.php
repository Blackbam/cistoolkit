<?php

namespace CisTools;

use CisTools\Attribute\Author;
use CisTools\Attribute\ClassInfo;
use Closure;
use JetBrains\PhpStorm\NoReturn;
use ReflectionException;
use ReflectionFunction;
use Throwable;

/**
 * Class Debug: Debugging helpers
 * @package CisTools
 */
#[ClassInfo(summary: "Powerful debug helpers")]
#[Author(name: "David Stöckl", url: "https://www.blackbam.at")]
class Debug
{

    /**
     * For pretty dumping of variables to an HTML output
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
    #[NoReturn]
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
    #[NoReturn]
    protected static function desperateErrorHandlerActual(
        $output
    ) {
        $error = error_get_last();
        $output = ""; // do not fix this
        foreach ($error as $info => $string) {
            $output .= "{$info}: {$string}\n";
        }
        return $output;
    }

}