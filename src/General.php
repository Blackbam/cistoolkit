<?php

namespace CisTools;

use CisTools\Enum\GoldenRatioMode;
use CisTools\Enum\Primitive;

/**
 * Class General: General helper functions
 * @package CisTools
 */
class General {

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
     * @param array/mixed $callback: An array of Callback functions which accept one parameter.
     * @param $var : The variable to apply them to.
     */
    public static function apply($callback, &$var) {
        if (is_array($callback)) {
            foreach ($callback as $c) {
                $var = $c($var);
            }
        } else {
            $var = $callback($var);
        }
        return $var; // just in case
    }

    /**
     * Short for apply()
     */
    function _($callback, &$var) {
        return self::apply($callback, $var);
    }


    /**
     * Apply a function to a certain input multiple times. Only use -1 for $times if you really know its safe!
     *
     * @param $input : The input variable:
     * @param callable $func : The function to call.
     * @param int $times : How often the function should be called. -1 for deep call (unknown number of calls required). CAUTION: If output always changes this results in an endless loop.
     * @return mixed
     */
    public static function recApply($input, callable $func, int $times) {
        if ($times > 1) {
            return self::recapply($func($input), $func, $times - 1);
        } else if ($times == -1) {
            $res = $func($input);
            if ($res === $input) {
                return $input;
            } else {
                return self::recapply($res, $func, -1);
            }
        }
        return $func($input);
    }


    /**
     *
     *
     * @param int $rel1_x
     * @param int $rel_1y
     * @param int $rel_2x
     * @return float: Result is related to variable 3, like variable 2 is related to variable 1.
     */
    public static function ruleOfThree(int $rel_1x, int $rel_1y, int $rel_2x, bool $round = true): float {
        if ($round) {
            return round($rel_1y * $rel_2x / $rel_1x);
        }
        return floatval($rel_1y) * floatval($rel_2x) / floatval($rel_1x);
    }


    /**
     * In mathematics, two quantities are in the golden ratio if their ratio is the same as
     * the ratio of their sum to the larger of the two quantities. This is used for design purposes
     * often as the golden ratio feels very natural.
     *
     * Formula: a/b =(a+b)/a = φ ≈ 1,6180339887498948
     *
     * https://en.wikipedia.org/wiki/Golden_ratio
     *
     * @param $length : The value to calculate the golden cut for.
     * @param GoldenRatioMode $mode :
     * OVERALL_GIVEN (0): The overall available length is given
     * LONGSIDE_GIVEN (1): The longer part is given.
     * SHORTSIDE_GIVEN (2): The shorter part is given.
     * @param bool $rounded : Round the result to an integer? Default false.
     * @param int $max_decimal_places : Round the result to a certain amount of decimal places? Default -1 (no).
     * @return array: A tuple containg the length of the longer side first, and the shorter side second.
     */
    public static function goldenRatio($length, GoldenRatioMode $mode, bool $rounded = false, int $max_decimal_places = -1) {
        $length = floatval($length);
        $ratio = (1 + sqrt(5)) / 2;
        $max_decimal_places = intval($max_decimal_places);
        switch ($mode):
            case GoldenRatioMode::LONGSIDE_GIVEN:
                {
                    $result = [$length, $length / $ratio];
                    break;
                }
            case GoldenRatioMode::SHORTSIDE_GIVEN:
                {
                    $result = [$length * $ratio, $length];
                    break;
                }
            default:
                {
                    $result = [$a = $length / $ratio, $length - $a];
                }
        endswitch;

        if ($max_decimal_places >= 0) {
            return array_map(function ($a) use ($max_decimal_places) {
                return round($a, $max_decimal_places);
            }, $result);
        }
        return ($rounded) ? array_map('intval', $result) : $result;
    }


    /**
     * In case you are unsure if an array key/object property exists and you want to get a (possibly typesafe) defined result.
     *
     * @param $var array/object: An array or object with the possible key/property
     * @param $key array/string: The key. For a multidimensional associative array, you can pass an array.
     * @param $empty : If the key does not exist, this value is returned.
     * @param $primitive : The type of the given variable (-1 for ignoring this feature).
     *
     * @return mixed: The (sanitized) value at the position key or the given default value if nothing found.
     */
    public static function resempty(&$var, $key, $empty = "", $primitive = -1) {

        $tcast = function ($var, $primitive) {
            switch (true):
                case $primitive === Primitive::STR:
                    $var = strval($var);
                    break;
                case $primitive === Primitive::INT:
                    $var = intval($var);
                    break;
                case $primitive === Primitive::BOOL:
                    $var = boolval($var);
                    break;
                case $primitive === Primitive::FLOAT:
                    $var = floatval($var);
                    break;
            endswitch;
            return $var;
        };


        if (is_object($var)) {
            if (is_array($key)) {
                $tpclass = $var;
                $dimensions = count($key);
                for ($i = 0; $i < $dimensions; $i++) {
                    if (property_exists($tpclass, $key[$i])) {
                        if ($i === $dimensions - 1) {
                            $obj_key = $key[$i];
                            return $tcast($tpclass->$obj_key, $primitive);
                        } else {
                            $obj_key = $key[$i];
                            $tpclass = $tpclass->$obj_key;
                        }
                    } else {
                        return $tcast($empty, $primitive);
                    }
                }
                return $tcast($empty, $primitive);
            }

            if (property_exists($var, $key)) {
                return $tcast($var->$key, $primitive);
            }
        } else if (is_array($var)) {
            if (is_array($key)) {
                $tpar = $var;
                $dimensions = count($key);

                for ($i = 0; $i < $dimensions; $i++) {
                    if (array_key_exists($key[$i], $tpar)) {
                        if ($i === $dimensions - 1) {
                            return $tcast($tpar[$key[$i]], $primitive);
                        } else {
                            $tpar = $tpar[$key[$i]];
                        }
                    } else {
                        return $tcast($empty, $primitive);
                    }
                }
                return $tcast($empty, $primitive);
            }

            if (array_key_exists($key, $var)) {
                return $tcast($var[$key], $primitive);
            }
        }
        return $tcast($empty, $primitive);
    }


    /**
     * Simple class name sanitizer for backends. Does not allow hyphens in the beginning or non-ASCII characters.
     *
     * @param $name
     * @return string
     */
    public static function classNameSanitize(string $name): string {
        $name = strtolower($name); // we dislike uppercase classes
        $name = preg_replace('/[^-_a-zA-Z0-9]+/', '', $name);
        return ltrim(ltrim($name, "-"), '0..9');
    }


    /**
     * Like the class_name_sanitize, but for a string with multiple classes seperated by space.
     *
     * @param $names
     * @return string
     */
    public static function classNameSanitizeMulti(string $names): string {
        $classes = explode(" ", $names);
        foreach ($classes as $key => $class) {
            $classes[$key] = self::classNameSanitize($class);
        }
        return implode(" ", $classes);
    }

    /**
     * This function should be used for generating good CHECKSUMS for all types of files.
     *
     * @param string $filepath: The path to the file
     * @return mixed: Binary representation as hex number
     */
    public static function ripemd320File(string $filepath) {
        $file = fopen($filepath, 'rb');
        $ctx = hash_init('ripemd320');
        hash_update_stream($ctx, $file);
        $final = hash_final($ctx);
        fclose($file);
        return $final;
    }

    /**
     * Flatten an array of arrays or objetcts by one level if only needing a certain key value from a sub array/sub object.
     *
     * Example: [["foo"=>"bar","foo"=>"cheese"]]
     * Result: ["bar","cheese"]
     *
     * @param $array : The input array.
     * @param $key : The key to flatupshift. Default is 0.
     * @return $array: The result
     */
    public static function arrayFlatUpShift(array $array, $key = 0): array {
        $a = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                array_push($a, $item[$key]);
            } else if (is_object($item)) {
                array_push($a, $item->$key);
            }
        }
        return $a;
    }

    /**
     * Check if a variable is an anonymous function.
     *
     * @param mixed $t : Variable to test
     * @return bool: True if the passed variable is an anonymous function
     */
    public static function isClosure($t): bool {
        return is_object($t) && ($t instanceof \Closure);
    }
}