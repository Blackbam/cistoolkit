<?php

namespace CisTools;

/**
 * Class Map: For advanced mapping functions
 * @package CisTools
 */
class Map
{
    /**
     * Apply 1..n functions to the given variable.
     *
     * Primarly intended for being lazy and skipping variable assignment.
     *
     * Example:
     * $a = "1";
     * apply('intval',$a);
     * var_dump($a); // int(1)
     *
     * $a = "2";
     * apply(['intval','sqrt'],$a);
     * var_dump($a); // float(1.4...)
     *
     * @param array|string $callback : An array of Callback functions which accept one parameter or a single callback as string.
     * @param $var : The variable to apply them to.
     * @return mixed
     */
    public static function apply(array|string $callback, mixed &$var): mixed
    {
        if (is_array($callback)) {
            foreach ($callback as $c) {
                $var = $c($var);
            }
        } else {
            $var = $callback($var);
        }
        return $var;
    }


    /**
     * Apply a function to a certain input multiple times. Only use -1 for $times if you really know its safe!
     *
     * @param $input : The input variable:
     * @param callable $func : The function to call.
     * @param int $times : How often the function should be called. -1 for deep call (unknown number of calls required). CAUTION: If output always changes this results in an endless loop.
     * @return mixed
     */
    public static function recApply($input, callable $func, int $times): mixed
    {
        if ($times > 1) {
            return self::recapply($func($input), $func, $times - 1);
        }

        if ($times === -1) {
            $res = $func($input);
            if ($res === $input) {
                return $input;
            }

            return self::recapply($res, $func, -1);
        }
        return $func($input);
    }
}