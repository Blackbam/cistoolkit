<?php

namespace CisTools;

use CisTools\Exception\InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

define("CIS_STR_LEFT", 0x1);
define("CIS_STR_RIGHT", 0x2);

class StringArtist
{
    /**
     * Takes any text and creates a slug with only alnum, lowercase characters and minus from it.
     *
     * @param string $text : The text to slugify.
     * @return string: The slugified string (empty string if something went wrong).
     */
    public static function slugify(string $text): string
    {
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

        if (empty($text) || !is_string($text)) {
            return "";
        }

        return $text;
    }

    /**
     * Date inputs work different across browsers in different languages. This sanitizes the input to standard date.
     *
     * Can be used before saving as well as before setting as value for a date input.
     *
     * @param $val : Any possible date accepted by strtotime()
     * @return false|string|null: Date in the format YYYY-MM-DD
     */
    #[Pure]
    public static function sanitizeDateInput(
        $val
    ): bool|string|null {
        $timestamp = strtotime($val);
        if ($timestamp < 100) {
            return null;
        }
        return date("Y-m-d", $timestamp);
    }

    /**
     * @deprecated Use str_starts_with
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * @deprecated Use str_ends_with
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * Validates and sanitizes a string of comma seperated numbers.
     *
     * @param string $numstr : A string of comma seperated numbers to sanitize.
     * @param bool $flat : Return a string of numbers again instead of an array
     * @return array|string: The sanitized result.
     */
    public static function numstrArr(string $numstr, bool $flat = false): array|string
    {
        $sanitized = preg_replace("/[^0-9,]/", "", $numstr);
        if (str_contains($sanitized, ',')) {
            $raw = explode(',', $sanitized);
        } else {
            $raw = [$sanitized];
        }
        $result = array_unique(
            array_filter(
                array_map('intval', $raw),
                static function ($a) {
                    return $a > 0;
                }
            )
        );
        if (!$flat) {
            return $result;
        }
        return implode(",", $result);
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
    public static function urlToHtmlAnchors(
        string $text,
        bool $label_strip_params = false,
        bool $label_strip_protocol = true
    ): string {
        $webAddressToHTML = static function ($url) use ($label_strip_params, $label_strip_protocol) {
            $label = $url;
            if ($label_strip_params) {
                $label = rtrim(preg_replace('/\?.*/', '', $label), "/");
            }
            if ($label_strip_protocol) {
                $label = preg_replace('#^https?://#', '', $label);
            }
            return '<a href="' . ((!preg_match(
                    "~^(?:f|ht)tps?://~i",
                    $url
                )) ? "http://" . $url : $url) . '">' . $label . '</a>';
        };

        preg_match_all('@(http(s)?://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@', $text, $matched_urls);
        return str_replace($matched_urls[0], array_map($webAddressToHTML, $matched_urls[0]), $text);
    }

    /**
     * Removes all possible whitespace from a string (also special UTF-8 characters e.g. zero-width non-breaking space).
     *
     * @param string $string
     * @return string: The string without any whitespace.
     */
    public static function removeAllWhitespace(string $string): string
    {
        return preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $string);
    }

    /**
     * Clean text from HTML
     *
     * @param string $text : The HTML to be returned as text only.
     * @return string: The clean text.
     */
    public static function cleanTextFromHtml(string $text): string
    {
        return trim(
            preg_replace('!\s+!', " ", str_replace(array("\n", "\r", "\t"), ' ', html_entity_decode(strip_tags($text))))
        );
    }

    /**
     * Shorten a string pattern to a maximum of characters without breaking words, by giving a String, maximum length and closing pattern if true.
     *
     * @param string $pattern : The string pattern
     * @param int $charlength : The maximum charlength as integer.
     * @param string $after : If the string is cutted, this is added at the end.
     * @return string
     */
    public static function limitWords(string $pattern, int $charlength = 200, string $after = " [...]"): string
    {
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
        return $ready;
    }

    /**
     * Split a string at a certain position and get both parts.
     *
     * @param string $string : The string to split
     * @param int $num : The position to split at
     * @return array: $array[0] is the first part of the splitted string, $array[1] the second
     */
    #[Pure]
    public static function splitAt(
        string $string,
        int $num
    ): array {
        $num = Math::rangeInt($num, 1);
        $length = strlen($string);
        $output[0] = substr($string, 0, $num);
        $output[1] = substr($string, $num, $length);
        return $output;
    }

    /**
     * Minify CSS on the fly (e.g. dynamic CSS from user input).
     *
     * Note: For bigger amounts of CSS you might be better of with an advanced methodology.
     *
     * @param string $css : All CSS as (concetenated) string.
     * @return string: The minified CSS.
     */
    public static function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove space after colons
        // Remove whitespace
        return str_replace(
            array(': ', "\r\n", "\r", "\n", "\t", '  ', '    ', '    '),
            array(':', '', '', '', '', '', '', ''),
            $css
        );
    }

    /******* Minification & Co ***********/

    /**
     * Minify JS on the fly. Only for very small generated snippets. Basically only strips whitespaces.
     *
     * @param string $js : JS as string.
     * @return string: The minified JS.
     */
    public static function minifyJs(string $js): string
    {
        return preg_replace(array("/\s+\n/", "/\n\s+/", "/ +/"), array("\n", "\n ", " "), $js);
    }

    /**
     * @throws InvalidArgumentException
     * @deprecated Please use StringGenerator::generateSecureRandomString();
     */
    public static function getRandomAlnumString(int $length = 8, bool $with_numbers = false): string
    {
        return StringGenerator::generateSecureRandomString(
            $length,
            ($with_numbers) ? StringGenerator::LOWERCASE | StringGenerator::NUMBERS : StringGenerator::LOWERCASE
        );
    }

    /**
     * @param string $string
     * @return string: The string with only alphanumeric characters.
     */
    public static function removeNonAlnum(string $string): string
    {
        return preg_replace('/\W+/', '', $string);
    }

    /**
     * @deprecated Please use StringGenerator::getRandumUrlValidString();
     */
    public static function getRandomUrlValidString(int $length = 8): string
    {
        return StringGenerator::getRandomUrlValidString($length);
    }

    /**
     * Takes a string and turns it into a "code-friendly" value
     * E.g "I am aweseome" will become "IAmAwesome"
     *
     * @param string $text Input text
     * @param bool $capitalizeFirstLetter (optional) if set to `false` the first letter will be lower case
     * @return string: A nice class name or method name (if not empty after sanitation)
     */
    public static function textToCodeName(string $text, bool $capitalizeFirstLetter = false): string
    {
        $text = ltrim(
            iconv(
                'utf-8',
                'us-ascii//TRANSLIT',
                preg_replace("/\s+/", "", ucwords(trim(preg_replace('/[^a-z0-9]+/i', ' ', $text))))
            ),
            '0..9'
        );
        if ($capitalizeFirstLetter) {
            return ucfirst($text);
        }
        return lcfirst($text);
    }

    /**
     * @param string $subject: The subject to be trimmed / wrapped.
     * @param string $wrap: The characters to trim from the string / wrap the string with.
     * @param int $times: The amount of time the wrap shall be repeated - use 0 for trimming only.
     * @param int $mode: A flag to indicate if you want to trim / wrap only the left / right side of the string, default is both sides of the string.
     * @return string: The trimmed / wrapped string.
     */
    public static function charTrimWrap(string $subject, string $wrap, int $times = 0, int $mode = CIS_STR_LEFT | CIS_STR_RIGHT): string
    {
        $times = Math::rangeInt($times,0);
        $preparedWrap = str_repeat($wrap,$times);
        if(!$mode || $mode > 3) {
            trigger_error("The function CisTools\StringArtist::charTrimWrap was called without a valid mode and will not do anything.",E_USER_WARNING);
            return $subject;
        }
        if($mode === 1) {
            return $preparedWrap.ltrim($subject,$wrap);
        }
        if($mode === 2) {
            return rtrim($subject,$wrap).$preparedWrap;
        }
        return $preparedWrap.trim($subject,$wrap).$preparedWrap;
    }


    /**
     * Split a text string into its single lines.
     *
     * @param string $subject: A text with multiple lines (e.g. a validation file).
     * @return array: The lines as array without line delimiter characters \r and \n
     */
    public static function stringToLines(string $subject): array
    {
        return array_map(static function ($line) {
            return preg_replace("/[\r\n]/", "", $line);
        }, explode(PHP_EOL, $subject));
    }

}