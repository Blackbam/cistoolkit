<?php

namespace CisTools;

use CisTools\Enum\GoldenRatioMode;

class Math
{
    /**
     *
     * @param int $rel_1x
     * @param int $rel_1y
     * @param int $rel_2x
     * @param bool $round
     * @return float: Result is related to variable 3, like variable 2 is related to variable 1.
     */
    public static function ruleOfThree(int $rel_1x, int $rel_1y, int $rel_2x, bool $round = true): float
    {
        if ($round) {
            return round($rel_1y * $rel_2x / $rel_1x);
        }
        return (float)$rel_1y * (float)$rel_2x / (float)$rel_1x;
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
     * @param mixed $length : The value to calculate the golden cut for (will be converted to float).
     * @param GoldenRatioMode $mode :
     * OVERALL_GIVEN (0): The overall available length is given
     * LONGSIDE_GIVEN (1): The longer part is given.
     * SHORTSIDE_GIVEN (2): The shorter part is given.
     * @param bool $rounded : Round the result to an integer? Default false.
     * @param int $max_decimal_places : Round the result to a certain amount of decimal places? Default -1 (no).
     * @return array: A tuple containg the length of the longer side first, and the shorter side second.
     */
    public static function goldenRatio(
        $length,
        GoldenRatioMode $mode,
        bool $rounded = false,
        int $max_decimal_places = -1
    ) {
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
            return array_map(
                function ($a) use ($max_decimal_places) {
                    return round($a, $max_decimal_places);
                },
                $result
            );
        }
        return ($rounded) ? array_map('intval', $result) : $result;
    }

    /**
     * @param int $int : The integer to be in a certain range.
     * @param int $min : The minimum value
     * @param int $max : The maximum value
     * @return int: The integer which might has been set to min or max
     */
    public static function rangeInt(int $int, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        if ($int < $min) {
            return $min;
        }
        if ($int > $max) {
            return $max;
        }
        return $int;
    }

    /**
     * @param float $float : The float to be in a certain range.
     * @param float $min : The minimum value
     * @param float $max : The maximum value
     * @return float: The float which might has been set to min or max
     */
    public static function rangeFloat(float $float, float $min = PHP_FLOAT_MIN, float $max = PHP_FLOAT_MAX): float
    {
        if ($float < $min) {
            return $min;
        }
        if ($float > $max) {
            return $max;
        }
        return $float;
    }
}