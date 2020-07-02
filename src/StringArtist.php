<?php
namespace CisTools;

class StringArtist {
    /**
     * Returns a randum alphanumeric string.
     *
     * @param int $length: The desired length.
     * @param bool $with_numbers: If true, the string does only contain lowercase characters - no numbers.
     * @return string: The alpha(numeric) string
     */
    public static function getRandomAlnumString(int $length = 8, bool $with_numbers = false): string {
        $characters = "abcdefghijklmnopqrstuvwxyz";
        if($with_numbers) {
            $characters = "0123456789";
        }
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * Returns a random URL-valid string (with any common possible characters mixed).
     *
     * @param int $length: The desired length.
     * @return string: The randum URL-valid string.
     */
    public static function getRandomUrlValidString(int $length = 8): string {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$-_'.+!*(),";
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * Takes any text and creates a slug with only alnum, lowercase characters and minus from it.
     *
     * @param string $text: The text to slugify.
     * @return string: The slugified string (empty string if something went wrong).
     */
    public static function slugify(string $text): string {
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
     * @deprecated Use Color class
     */
    public static function sanitizeHexColor(string $color, string $default = "#ffffff"): string {
        return Color::colorSanitizeHexString($color,$default);
    }

    /**
     * Date inputs work different across browsers in different languages. This sanitizes the input to standard date.
     *
     * Can be used before saving as well as before setting as value for a date input.
     *
     * @param $val: Any possible date accepted by strtotime
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
     * Check if a string starts with ...
     *
     * @param string $haystack : The string to be checked
     * @param string $needle : The start string
     * @return bool: True, if the string starts with ...
     */
    public static function startsWith(string $haystack, string $needle): bool {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    /**
     * @param string $haystack: The string to be checked
     * @param string $needle: The end string
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool {
        $length = strlen($needle);
        return ($length != 0) ? (substr($haystack, -$length) === $needle) : true;
    }

    /**
     * Validates and sanitizes a string of comma seperated numbers.
     *
     * @param string $numstr : A string of comma seperated numbers to sanitize.
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
     * Takes a text string, searches for everything which looks like a Web Address e.g. example.com, https://www.example.com, example.com/?code=only
     * and makes an HTML Link from it.
     *
     * @param string $text : A text with plain text web addresses.
     * @param boolean $label_strip_params : Strip GET parameters within the label. Default false.
     * @param boolean $label_strip_protocol : Strip protocol (like http://) in the label. Default false.
     * @return string: HTML containing links.
     */
    public static function urlToHtmlAnchors(string $text, bool $label_strip_params = false, bool $label_strip_protocol = true): string {

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
     * Clean text from HTML
     *
     * @param string $text: The HTML to be returned as text only.
     * @return string: The clean text.
     */
    public static function cleanTextFromHtml(string $text): string {
        return trim(preg_replace('!\s+!', " ", str_replace(array("\n", "\r", "\t"), ' ', html_entity_decode(strip_shortcodes(strip_tags($text))))));
    }

    /**
     * Shorten a string pattern to a maximum of characters without breaking words, by giving a String, maximum length and closing pattern if true.
     *
     * @param string $pattern: The string pattern
     * @param int $charlength: The maximum charlength as integer.
     * @param string $after: If the string is cutted, this is added at the end.
     * @return string
     */
    public static function limitWords(string $pattern, int $charlength = 200, string $after = " [...]"): string {
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

    /******* Minification & Co ***********/

    /**
     * Minify CSS on the fly (e.g. dynamic CSS from user input).
     *
     * Note: For bigger amounts of CSS you might be better of with an advanced methodology.
     *
     * @param string $css : All CSS as (concetenated) string.
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
     * @param string $js : JS as string.
     * @return string: The minified JS.
     */
    public static function minifyJs(string $js): string {
        return preg_replace(array("/\s+\n/", "/\n\s+/", "/ +/"), array("\n", "\n ", " "), $js);
    }
}