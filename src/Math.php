<?php
namespace CisTools;

use CisTools\Enum\GoldenRatioMode;

class Math {
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
}