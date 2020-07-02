<?php
declare(strict_types=1);

namespace CisTools;

/**
 * Class Color
 * @package CisTools
 * @author David Stöckl <admin@blackbam.at>
 * @todo Test some functions (especially hsl/hsla/hsb/hsv and add the correct required input types)
 *
 * Representations supported:
 * - Int is the integer representation
 * - Hex is the hexadecimal string
 * - RGB is an array of [red,green,blue]
 * - RGBA is an array of [red,green,blue,alpha]
 * - HSL is an array of [hue,saturation,lightness]
 * - HSLA is an array of [hue,saturation,lightness,alpha]
 * - HSB/HSV is an array of [hue,saturation,brightness]
 * - CMYK is an array of [cyan,magenta,yellow,key]
 */
class Color {

    protected int $color = 0x0;
    protected float $alpha = 1.0;

    public function __construct() {
    }

    /*
    ╔═╗┌─┐┌┬┐┌┬┐┌─┐┬─┐┌─┐
    ╚═╗├┤  │  │ ├┤ ├┬┘└─┐
    ╚═╝└─┘ ┴  ┴ └─┘┴└─└─┘
    */

    public function setByInt(int $color): void {
        $this->color = self::range16581375($color);
        $this->alpha = 1.0;
    }

    public function setByHexString(string $color): void {
        $this->color = self::colorHexToInt($color);
        $this->alpha = 1.0;
    }

    public function setByRgba(int $red, int $green, int $blue, float $alpha = 1.0): void {
        $this->color = self::rgbToInt($red, $green, $blue);
        self::setAlpha($alpha);
    }

    public function setByHsla($hue, $saturation, $lightness, float $alpha = 1.0): void {
        $rgb = self::hslToRgb($hue, $saturation, $lightness);
        $this->color = self::rgbToInt($rgb[0], $rgb[1], $rgb[2]);
        self::setAlpha($alpha);
    }

    public function setByHsv($hue, $saturation, $brightness): void {
        $rgb = self::hsvToRgb($hue, $saturation, $brightness);
        $this->color = self::rgbToInt($rgb[0], $rgb[1], $rgb[2]);
        $this->alpha = 1.0;
    }

    public function setByCmyk($cyan, $magenta, $yellow, $key): void {
        $rgb = self::cmykToRgb($cyan, $magenta, $yellow, $key);
        $this->color = self::rgbToInt($rgb[0], $rgb[1], $rgb[2]);
        $this->alpha = 1.0;
    }

    public function setAlpha(float $alpha = 1.0): void {
        $this->alpha = self::range01($alpha);
    }


    /*
    ╔═╗┌─┐┌┬┐┌┬┐┌─┐┬─┐┌─┐
    ║ ╦├┤  │  │ ├┤ ├┬┘└─┐
    ╚═╝└─┘ ┴  ┴ └─┘┴└─└─┘
    */
    public function getInt(): int {
        return $this->color;
    }

    public function getHexString($noHashtag = false): string {
        $ht = "";
        if (!$noHashtag) $ht = "#";
        return $ht . dechex($this->color);
    }

    public function getRgb(): array {
        return self::intToRgb($this->color);
    }

    public function getRgba(): array {
        $rgb = self::intToRgb($this->color);
        array_push($rgb, $this->alpha);
        return $rgb;
    }

    public function getHsl(): array {
        $rgb = self::intToRgb($this->color);
        return self::rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
    }

    public function getHsla(): array {
        $rgb = self::intToRgb($this->color);
        $hsl = self::rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
        array_push($hsl, $this->alpha);
        return $hsl;
    }

    public function getHsv(): array {
        $rgb = self::intToRgb($this->color);
        return self::rgbToHsv($rgb[0], $rgb[1], $rgb[2]);
    }

    public function getCmyk(): array {
        $rgb = self::intToRgb($this->color);
        return self::rgbToCmyk($rgb[0], $rgb[1], $rgb[2]);
    }

    /*
    ╔═╗┌─┐┌┐┌┬  ┬┌─┐┬─┐┌┬┐┌─┐┬─┐┌─┐
    ║  │ ││││└┐┌┘├┤ ├┬┘ │ ├┤ ├┬┘└─┐
    ╚═╝└─┘┘└┘ └┘ └─┘┴└─ ┴ └─┘┴└─└─┘
    */

    public static function rgbToInt(int $red, int $green, int $blue): int {
        return (int)0xFFFF * self::range255($red) + 0xFF * self::range255($green) + self::range255($blue);
    }

    public static function intToRgb(int $color): array {
        list($r, $g, $b) = sscanf(dechex($color), "%02x%02x%02x");
        return [$r, $g, $b];
    }

    /**
     * Hex color to RGB color
     *
     * @param $hexCode : Hexadecimal color code
     * @return int: The RGB color value
     */
    public static function colorHexToInt(string $hexCode): int {
        $hexCode = self::colorSanitizeHexString($hexCode);

        if ($hexCode[0] == '#')
            $hexCode = substr($hexCode, 1);

        if (strlen($hexCode) == 3) {
            $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
        }

        $r = hexdec($hexCode[0] . $hexCode[1]);
        $g = hexdec($hexCode[2] . $hexCode[3]);
        $b = hexdec($hexCode[4] . $hexCode[5]);

        return (int)$b + ($g << 0x8) + ($r << 0x10);
    }

    public static function rgbToHsl(int $r, int $g, int $b): array {

        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $h = null;
        $s = null;
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if ($d == 0) {
            $h = $s = 0; // achromatic
        } else {
            $s = $d / (1 - abs(2 * $l - 1));

            switch ($max) {
                case $r:
                    $h = 60 * fmod((($g - $b) / $d), 6);
                    if ($b > $g) {
                        $h += 360;
                    }
                    break;

                case $g:
                    $h = 60 * (($b - $r) / $d + 2);
                    break;

                case $b:
                    $h = 60 * (($r - $g) / $d + 4);
                    break;
            }
        }

        return array($h, $s, $l);
    }

    public static function hslToRgb($h, $s, $l) {
        $r = null;
        $g = null;
        $b = null;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
        $m = $l - ($c / 2);

        if ($h < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else if ($h < 120) {
            $r = $x;
            $g = $c;
            $b = 0;
        } else if ($h < 180) {
            $r = 0;
            $g = $c;
            $b = $x;
        } else if ($h < 240) {
            $r = 0;
            $g = $x;
            $b = $c;
        } else if ($h < 300) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }

        $r = ($r + $m) * 255;
        $g = ($g + $m) * 255;
        $b = ($b + $m) * 255;

        return array(floor($r), floor($g), floor($b));
    }

    function rgbToCmyk($r, $g, $b) {
        $c = (255 - $r) / 255.0 * 100;
        $m = (255 - $g) / 255.0 * 100;
        $y = (255 - $b) / 255.0 * 100;

        $b = min(array($c, $m, $y));
        $c = $c - $b;
        $m = $m - $b;
        $y = $y - $b;

        $cmyk = array('c' => $c, 'm' => $m, 'y' => $y, 'k' => $b);
        return $cmyk;
    }

    function cmykToRgb($c, $m, $y, $k) {
        $c = $c / 100;
        $m = $m / 100;
        $y = $y / 100;
        $k = $k / 100;

        $r = 1 - ($c * (1 - $k)) - $k;
        $g = 1 - ($m * (1 - $k)) - $k;
        $b = 1 - ($y * (1 - $k)) - $k;

        $r = round($r * 255);
        $g = round($g * 255);
        $b = round($b * 255);

        return [$r, $g, $b];
    }

    /**
     * Converts an RGB color value to HSV. Conversion formula
     * adapted from http://en.wikipedia.org/wiki/HSV_color_space.
     * Assumes r, g, and b are contained in the set [0, 255] and
     * returns h, s, and v in the set [0, 1].
     *
     * @param int $r : The red color value
     * @param int $g : The green color value
     * @param int $b : The blue color value
     * @return  array: The HSV representation
     */
    function rgbToHsv(int $r, int $g, int $b): array {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $h = $s = $v = $max;

        $d = $max - $min;
        $s = ($max == 0) ? 0 : $d / $max;

        if ($max == $min) {
            $h = 0; // achromatic
        } else {
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }

        return [$h, $s, $v];
    }

    /**
     * Converts an HSV color value to RGB. Conversion formula
     * adapted from http://en.wikipedia.org/wiki/HSV_color_space.
     * Assumes h, s, and v are contained in the set [0, 1] and
     * returns r, g, and b in the set [0, 255].
     *
     * @param int $h : The hue
     * @param int $s : The saturation
     * @param int $v : The value
     * @return array: The RGB representation
     */
    function hsvToRgb($h, $s, $v) {
        $r = null;
        $g = null;
        $b = null;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [$r * 255, $g * 255, $b * 255];
    }


    /*
    ╦ ╦┌─┐┬  ┌─┐┌─┐┬─┐┌─┐
    ╠═╣├┤ │  ├─┘├┤ ├┬┘└─┐
    ╩ ╩└─┘┴─┘┴  └─┘┴└─└─┘
     */

    /**
     * Takes a color string which is expected to be a hex color (with or without hashtag) and returns
     * a valid hex color with 6 digits.
     *
     * @param string $color : The color to sanitize in hex notation
     * @param string $default : Default color to return if color is not useable
     * @return string: A hex color prefixed with hashtag (e.g. #ffffff)
     */
    public static function colorSanitizeHexString(string $color, string $default = "#ffffff"): string {
        if (substr($color, 0, 1) !== "#") {
            $color = "#" . $color;
        }

        // 3 or 6 hex digits, or the empty string.
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }

        return $default;
    }

    /**
     * Makes sure an integer is between 0 and 255
     *
     * @param int $int
     * @return int
     */
    public static function range255(int $int): int {
        if ($int < 0) $int = 0;
        if ($int > 255) $int = 255;
        return $int;
    }

    /**
     * Make sure an integer is in the full integer color range
     *
     * @param int $int
     * @return int
     */
    public static function range16581375(int $int): int {
        if ($int > 16581375) $int = 16581375;
        if ($int < 0) $int = 0;
        return $int;
    }

    /**
     * Makes sure a float is between 0.0 and 1.0
     *
     * @param float $float
     * @return float
     */
    public static function range01(float $float): float {
        if ($float < 0.0) $float = 0.0;
        if ($float > 1.0) $float = 1.0;
        return $float;
    }

    /*
    ╔═╗┌┬┐┌─┐┌┬┐┌─┐  ┬┌┐┌┌─┐┌─┐
    ╚═╗ │ ├─┤ │ ├┤   ││││├┤ │ │
    ╚═╝ ┴ ┴ ┴ ┴ └─┘  ┴┘└┘└  └─┘
    */
    public function isDark(int $threshold = 127): bool {
        $threshold = self::range255($threshold);

        $rgb = self::intToRgb($this->color);
        $hsl = self::rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
        if ($hsl[2] < $threshold) return true;
        return false;
    }

    /*
    ╔═╗┌─┐┬─┐┌┬┐┌─┐┌┬┐┌┬┐┌─┐┬─┐┌─┐
    ╠╣ │ │├┬┘│││├─┤ │  │ ├┤ ├┬┘└─┐
    ╚  └─┘┴└─┴ ┴┴ ┴ ┴  ┴ └─┘┴└─└─┘
     */
    public function cssGetHex(): string {
        return "#" . dechex($this->color);
    }

    public function cssGetRgba(): string {
        $rgb = self::intToRgb($this->color);
        return "rgba(" . $rgb[0] . "," . $rgb[1] . "," . $rgb[2] . "," . $this->alpha . ")";
    }
}