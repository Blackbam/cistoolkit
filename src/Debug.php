<?php

namespace CisTools;

use Closure;
use ReflectionException;
use ReflectionFunction;

/**
 * Class Debug: Debugging helpers
 * @package CisTools
 */
class Debug
{

    /**
     * For pretty dumping of variables to an HTML output
     *
     * @param mixed $var : The variable to dump pretty
     */
    public static function dump($var)
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
        } catch (ReflectionException $exception) {
            // do nothing
        }
        $params = array();
        foreach ($r->getParameters() as $p) {
            $s = '';
            if ($p->isArray()) {
                $s .= 'array ';
            } else {
                if ($p->getClass()) {
                    $s .= $p->getClass()->name . ' ';
                }
            }
            if ($p->isPassedByReference()) {
                $s .= '&';
            }
            $s .= '$' . $p->name;
            if ($p->isOptional()) {
                try {
                    $s .= ' = ' . var_export($p->getDefaultValue(), true);
                } catch (ReflectionException $exception) {
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