<?php
namespace CisTools;

use CisTools\Enum\GoldenRatioMode;
use CisTools\Enum\Primitive;

/**
 * @deprecated Was originally for functions without a concrete class
 */
class General {

    /**
     * @deprecated Use Map instead
     */
    public static function apply($callback, &$var) {
        return Map::apply($callback, $var);
    }


    /**
     * @deprecated Use Map instead
     */
    public static function recApply($input, callable $func, int $times) {
        return Map::recApply($input,$func,$times);
    }


    /**
     * @deprecated Will be removed. Use Math class instead.
     */
    public static function ruleOfThree(int $rel_1x, int $rel_1y, int $rel_2x, bool $round = true): float {
        return Math::ruleOfThree($rel_1x,$rel_1y,$rel_2x,$round);
    }


    /**
     * @deprecated Will be removed. Use Math class instead.
     */
    public static function goldenRatio($length, GoldenRatioMode $mode, bool $rounded = false, int $max_decimal_places = -1) {
        return Math::goldenRatio($length, $mode,$rounded,$max_decimal_places);
    }


    /**
     * @deprecated Use Sanitizer class instead
     */
    public static function resempty(&$var, $key, $empty = "", Primitive $primitive = null) {
        return Sanitizer::resempty($var,$key,$empty,$primitive);
    }


    /**
     * @deprecated Use Reflection class instead
     */
    public static function classNameSanitize(string $name): string {
        return Reflection::classNameSanitize($name);
    }


    /**
     * @deprecated Use Reflection class instead
     */
    public static function classNameSanitizeMulti(string $names): string {
        return Reflection::classNameSanitizeMulti($names);
    }

    /**
     * @deprecated Use File class instead
     */
    public static function ripemd320File(string $filepath) {
        File::ripemd320File($filepath);
    }

    /**
     * @deprecated Use ArrayArtist class instead
     */
    public static function arrayFlatUpShift(array $array, $key = 0): array {
        return ArrayArtist::arrayFlatUpShift($array,$key);
    }

    /**
     * @deprecated Use Reflection instead
     */
    public static function isClosure($t): bool {
        return is_object($t) && ($t instanceof \Closure);
    }
}