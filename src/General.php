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
    public static function recapply($input, callable $func, int $times) {
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
     * Shorten a String pattern to a maximum of characters without breaking words, by giving a String, maximum length and closing pattern if true.
     *
     * @param $pattern
     * @param int $charlength
     * @param string $after
     * @param bool $echo
     * @return bool|string
     */
    public static function limitWords(string $pattern, int $charlength = 200, string $after = " [...]", bool $echo = true): string {
        $charlength++;
        $ready = "";
        if (strlen($pattern) > $charlength) {
            $subex = substr($pattern, 0, $charlength - 5);
            $exwords = explode(" ", $subex);
            $excut = -(strlen($exwords[count($exwords) - 1]));
            if ($excut < 0) {
                $ready = substr($subex, 0, $excut);
            } else {
                $ready = $subex;
            }
            $ready .= $after;
        } else {
            $ready .= $pattern;
        }
        if ($echo === true) {
            echo $ready;
            return "";
        } else {
            return $ready;
        }
    }

    /**
     * Clean text from HTML
     * @param $text
     * @return string
     */
    public static function cleanTextFromHtml(string $text): string {
        return trim(preg_replace('!\s+!', " ", str_replace(array("\n", "\r", "\t"), ' ', html_entity_decode(strip_shortcodes(strip_tags($text))))));
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
     *
     * @param string $color : Hex color (only 7 chars, prefixed with #).
     */
    public static function validateHexColor($color): bool  {
        if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
            return true;
        }
        return false;
    }

    /**
     * Takes a text string, searches for everything which looks like a Web Address e.g. example.com, https://www.example.com, example.com/?code=only
     * and makes an HTML Link from it.
     *
     * @param string $text : A text with plain text web addresses.
     * @param boolean $label_strip_params : Strip GET parameters within the label. Default false.
     * @param boolean $label_strip_protocol : Strip protocol (like http://) in the label. Default false.
     * @return string: HTML containing links.
     */
    public static function urlToHtmlLink(string $text, bool $label_strip_params = false, bool $label_strip_protocol = true): string {

        $webAddressToHTML = function ($url) use ($label_strip_params, $label_strip_protocol) {
            $label = $url;
            if ($label_strip_params) {
                $label = rtrim(preg_replace('/\?.*/', '', $label), "/");
            }
            if ($label_strip_protocol) {
                $label = preg_replace('#^https?://#', '', $label);
            }
            return '<a href="' . ((!preg_match("~^(?:f|ht)tps?://~i", $url)) ? "http://" . $url : $url) . '">' . $label . '</a>';
        };

        preg_match_all('@(http(s)?://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@', $text, $matched_urls);
        return str_replace($matched_urls[0], array_map($webAddressToHTML, $matched_urls[0]), $text);
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
     * Returns a random URL-valid string.
     *
     * @param int $length
     * @return string
     */
    public static function getRandomURLValidString(int $length = 8): string {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$-_'.+!*(),";
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function getRandomAlnumString(int $length = 8): string {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
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
     * Takes any text and creates a slug with only alnum, lowercase characters and minus from it.
     *
     * @param $text
     * @param $remove_leading_numbers
     * @return mixed|string
     */
    public static function slugify(string $text, bool $remove_leading_numbers = false): string {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if ($remove_leading_numbers) {
            $text = ltrim($text, '0123456789');
        }

        if (empty($text) || !is_string($text)) {
            return "";
        }

        return $text;
    }

    /**
     * Guarantees to return a valid hex color.
     *
     * @param $color
     * @return string
     */
    public static function cis_sanitize_hex_color(string $color, string $default = "#ffffff"): string {
        if (substr($color, 0, 1) !== "#") {
            $color = "#" . $color;
        }

        // 3 or 6 hex digits, or the empty string.
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }

        return "#ffffff";
    }

    /**
     * Date inputs work different across browsers in different languages. This sanitizes the input to standard date.
     *
     * Can be used before saving as well as before setting as value for a date input.
     *
     * @param $val : Any possible date accepted by strtotime
     * @return false|string|null: Date in the format YYYY-MM-DD
     */
    public static function sanitizeDateInput($val) {
        $tstamp = strtotime($val);
        if ($tstamp < 100) {
            return null;
        }
        return date("Y-m-d", $tstamp);
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
     * Minify CSS on the fly (e.g. dynamic CSS from user input).
     *
     * Note: For bigger amounts of CSS you might be better of with an advanced methodology.
     *
     * @param $css : All CSS as (concetenated) string.
     * @return string: The minified CSS.
     */
    public static function minifyCss(string $css): string {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove space after colons
        $css = str_replace(': ', ':', $css);
        // Remove whitespace
        return str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
    }

    /**
     * Minify JS on the fly. Only for very small generated snippets. Basically only strips whitespaces.
     *
     * @param $js : JS as string.
     * @return string: The minified JS.
     */
    public static function minifyJs(string $js): string {
        return preg_replace(array("/\s+\n/", "/\n\s+/", "/ +/"), array("\n", "\n ", " "), $js);
    }

    /**
     * Check if a string starts with ...
     *
     * @param $haystack : The string to be checked
     * @param $needle : The start string
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    /**
     * @param $haystack : The string to be checked
     * @param $needle : The end string
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool {
        $length = strlen($needle);
        return ($length != 0) ? (substr($haystack, -$length) === $needle) : true;
    }

    /**
     * Validates and sanitizes a string of comma seperated numbers.
     *
     * @param $numstr : A string of comma seperated numbers to sanitize.
     * @param bool $flat : Return a string of numbers again instead of an array
     * @return array|string: The sanitized result.
     */
    public static function numstrArr(string $numstr, bool $flat = false) {
        $sanitized = preg_replace("/[^0-9,]/", "", $numstr);
        if (strpos($sanitized, ',') !== false) {
            $raw = explode(',', $sanitized);
        } else {
            $raw = [$sanitized];
        }
        $result = array_unique(array_filter(array_map('intval', $raw), function ($a) {
            return $a > 0;
        }));
        if (!$flat) {
            return $result;
        }
        return implode(",", $result);
    }

    /**
     * Check if a variable is an anonymous function.
     *
     * @param mixed $t : Variable to test
     * @return bool: True if the passed variable is an anonymous function
     */
    public static function is_closure($t): bool {
        return is_object($t) && ($t instanceof \Closure);
    }
}