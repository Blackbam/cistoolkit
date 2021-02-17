<?php

declare(strict_types=1);

namespace CisTools;

/**
 * Class Color
 * @package CisTools
 * @author David Stöckl <admin@blackbam.at>
 * @todo Test some functions (especially hsl/hsla/hsb/hsv and add the correct required input types). This class is in beta.
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
class Color
{

    public const COLOR_MAX = 16777215; // 256^3 possible colors
    protected int $color = 0x0;
    protected float $alpha = 1.0;

    public function __construct()
    {
    }

    /*
    ╔═╗┌─┐┌┬┐┌┬┐┌─┐┬─┐┌─┐
    ╚═╗├┤  │  │ ├┤ ├┬┘└─┐
    ╚═╝└─┘ ┴  ┴ └─┘┴└─└─┘
    */

    /**
     * @param int $color
     * @param float $alpha
     */
    public function setByInt(int $color, float $alpha = 1.0): void
    {
        $this->color = Math::rangeInt($color, 0, self::COLOR_MAX);
        $this->alpha = Math::rangeFloat($alpha,0.0,1.0);
    }

    /**
     * @param string $color
     */
    public function setByHexString(string $color): void
    {
        $colorAlphaTuple = self::colorHexRgbaToIntWithAlpha($color);
        $this->color = $colorAlphaTuple['color'];
        $this->alpha = $colorAlphaTuple['alpha'];
    }

    /**
     * Hex color to RGB color
     *
     * @param $hexCode : Hexadecimal color code
     * @return int: The RGB color value
     */
    public static function colorHexToInt(string $hexCode): int
    {
        $hexCode = substr(trim($hexCode), self::colorSanitizeHexString($hexCode));

        if (strlen($hexCode) === 3) {
            $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
        }

        $r = hexdec($hexCode[0] . $hexCode[1]);
        $g = hexdec($hexCode[2] . $hexCode[3]);
        $b = hexdec($hexCode[4] . $hexCode[5]);

        return (int)$b + ($g << 0x8) + ($r << 0x10);
    }

    /**
     * Hex RGBA color
     * @param string $hexCode: Hexadecimal color code with alpha
     * @return array: A tuple with color int and alpha float
     */
    public static function colorHexRgbaToIntWithAlpha(string $hexCode): array
    {
        $hexCode = ltrim(trim($hexCode),"#");
        $length = strlen($hexCode);

        if($length === 4) {
            return [
                'color' => self::colorHexToInt(substr($hexCode,0,3)),
                'alpha' => self::hexToAlpha(substr($hexCode,-1))
            ];
        }

        if($length > 6) {
            return [
                'color' => self::colorHexToInt(substr($hexCode,0,6)),
                'alpha' => self::hexToAlpha(($length > 7) ? $hexCode[7] . $hexCode[8] : $hexCode[7])
            ];
        }
        return [
            'color' => self::colorHexToInt($hexCode),
            'alpha' => 1.0
        ];
    }

    /**
     * @param string $hexCode
     * @return float
     */
    public static function hexToAlpha(string $hexCode): float {
        $hexCode = ltrim(trim($hexCode),"#");
        if(strlen($hexCode) === 1) {
            $hexCode .= $hexCode;
        }
        return Math::rangeInt(hexdec($hexCode),0,100) / 100.00;
    }

    /**
     * Takes a color string which is expected to be a hex color (with or without hashtag) and returns
     * a valid hex color with 6 digits and prefixed with hashtag.
     *
     * @param string $color : The color to sanitize in hex notation
     * @param string $default : Default color to return if color is not useable
     * @return string: A hex color prefixed with hashtag (e.g. #ffffff)
     */
    public static function colorSanitizeHexString(string $color, string $default = "#ffffff"): string
    {
        if ($color && $color[0] !== "#") {
            $color = "#" . $color;
        }

        // 3 or 6 hex digits, or the empty string.
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }

        return $default;
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param float $alpha
     */
    public function setByRgba(int $red, int $green, int $blue, float $alpha = 1.0): void
    {
        $this->color = self::rgbToInt($red, $green, $blue);
        $this->setAlpha($alpha);
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @return int
     */
    public static function rgbToInt(int $red, int $green, int $blue): int
    {
        return 0xFFFF * Math::rangeInt($red, 0, 255) + 0xFF * Math::rangeInt($green, 0, 255) + Math::rangeInt(
                $blue,
                0,
                255
            );
    }

    /**
     * @param float $alpha
     */
    public function setAlpha(float $alpha = 1.0): void
    {
        $this->alpha = Math::rangeFloat($alpha, 0.0, 1.0);
    }


    /*
    ╔═╗┌─┐┌┬┐┌┬┐┌─┐┬─┐┌─┐
    ║ ╦├┤  │  │ ├┤ ├┬┘└─┐
    ╚═╝└─┘ ┴  ┴ └─┘┴└─└─┘
    */

    /**
     * @param $hue
     * @param $saturation
     * @param $lightness
     * @param float $alpha
     */
    public function setByHsla($hue, $saturation, $lightness, float $alpha = 1.0): void
    {
        $rgb = self::hslToRgb($hue, $saturation, $lightness);
        $this->color = self::rgbToInt($rgb[0], $rgb[1], $rgb[2]);
        $this->setAlpha($alpha);
    }

    /**
     * @param int $h
     * @param int $s
     * @param int $l
     * @return array
     */
    public static function hslToRgb(int $h, int $s, int $l): array
    {
        $c = (1 - abs(2 * ($l / 100) - 1)) * $s / 100;
        $x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
        $m = ($l / 100) - ($c / 2);
        if ($h < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } elseif ($h < 120) {
            $r = $x;
            $g = $c;
            $b = 0;
        } elseif ($h < 180) {
            $r = 0;
            $g = $c;
            $b = $x;
        } elseif ($h < 240) {
            $r = 0;
            $g = $x;
            $b = $c;
        } elseif ($h < 300) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }
        return [floor(($r + $m) * 255), floor(($g + $m) * 255), floor(($b + $m) * 255)];
    }

    /**
     * @param $hue
     * @param $saturation
     * @param $brightness
     */
    public function setByHsv($hue, $saturation, $brightness): void
    {
        $rgb = $this->hsvToRgb($hue, $saturation, $brightness);
        $this->color = self::rgbToInt($rgb[0], $rgb[1], $rgb[2]);
        $this->alpha = 1.0;
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
    function hsvToRgb($h, $s, $v): array
    {
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

    /**
     * @param $cyan
     * @param $magenta
     * @param $yellow
     * @param $key
     */
    public function setByCmyk($cyan, $magenta, $yellow, $key): void
    {
        $rgb = $this->cmykToRgb($cyan, $magenta, $yellow, $key);
        $this->color = self::rgbToInt($rgb[0], $rgb[1], $rgb[2]);
        $this->alpha = 1.0;
    }

    /**
     * @param $c
     * @param $m
     * @param $y
     * @param $k
     * @return array
     */
    function cmykToRgb($c, $m, $y, $k)
    {
        $c /= 100;
        $m /= 100;
        $y /= 100;
        $k /= 100;

        $r = 1 - ($c * (1 - $k)) - $k;
        $g = 1 - ($m * (1 - $k)) - $k;
        $b = 1 - ($y * (1 - $k)) - $k;

        $r = round($r * 255);
        $g = round($g * 255);
        $b = round($b * 255);

        return [$r, $g, $b];
    }

    /**
     * @return int
     */
    public function getInt(): int
    {
        return $this->color;
    }

    /**
     * @return array
     */
    public function getRgb(): array
    {
        return self::intToRgb($this->color);
    }

    /*
    ╔═╗┌─┐┌┐┌┬  ┬┌─┐┬─┐┌┬┐┌─┐┬─┐┌─┐
    ║  │ ││││└┐┌┘├┤ ├┬┘ │ ├┤ ├┬┘└─┐
    ╚═╝└─┘┘└┘ └┘ └─┘┴└─ ┴ └─┘┴└─└─┘
    */

    /**
     * @param int $color
     * @return array
     */
    public static function intToRgb(int $color): array
    {
        [$r, $g, $b] = sscanf(str_pad(dechex($color), 6, "0", STR_PAD_LEFT), "%02x%02x%02x");
        return [$r, $g, $b];
    }

    /**
     * @return array
     */
    public function getRgba(): array
    {
        $rgb = self::intToRgb($this->color);
        $rgb[] = $this->alpha;
        return $rgb;
    }

    /**
     * @return array
     */
    public function getHsl(): array
    {
        $rgb = self::intToRgb($this->color);
        return self::rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * @param int $r
     * @param int $g
     * @param int $b
     * @return array
     */
    public static function rgbToHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;
        if ($d === 0) {
            $h = $s = 0;
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
        return [round($h, 0), round($s * 100, 0), round($l * 100, 0)];
    }

    /**
     * @return array
     */
    public function getHsla(): array
    {
        $rgb = self::intToRgb($this->color);
        $hsl = self::rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
        $hsl[] = $this->alpha;
        return $hsl;
    }

    /**
     * @return array
     */
    public function getHsv(): array
    {
        $rgb = self::intToRgb($this->color);
        return $this->rgbToHsv($rgb[0], $rgb[1], $rgb[2]);
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
    function rgbToHsv(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $h = $s = $v = $max;

        $d = $max - $min;
        $s = ($max === 0) ? 0 : $d / $max;

        if ($max === $min) {
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
     * @return array
     */
    public function getCmyk(): array
    {
        $rgb = self::intToRgb($this->color);
        return $this->rgbToCmyk($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * @param $r
     * @param $g
     * @param $b
     * @return array
     */
    function rgbToCmyk($r, $g, $b): array
    {
        $c = (255 - $r) / 255.0 * 100;
        $m = (255 - $g) / 255.0 * 100;
        $y = (255 - $b) / 255.0 * 100;

        $b = min([$c, $m, $y]);
        $c -= $b;
        $m -= $b;
        $y -= $b;

        return ['c' => $c, 'm' => $m, 'y' => $y, 'k' => $b];
    }


    /*
    ╦ ╦┌─┐┬  ┌─┐┌─┐┬─┐┌─┐
    ╠═╣├┤ │  ├─┘├┤ ├┬┘└─┐
    ╩ ╩└─┘┴─┘┴  └─┘┴└─└─┘
     */

    /**
     * @param float $threshold
     * @return bool
     */
    public function isDark(float $threshold = 127.0): bool
    {
        $threshold = Math::rangeFloat($threshold, 0.0, 256.0);

        $trel = $threshold * 100.0 / 256.0;
        $rgb = self::intToRgb($this->color);
        $hsl = self::rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
        return !($hsl[2] > $trel);
    }

    /*
    ╔═╗┌┬┐┌─┐┌┬┐┌─┐  ┬┌┐┌┌─┐┌─┐
    ╚═╗ │ ├─┤ │ ├┤   ││││├┤ │ │
    ╚═╝ ┴ ┴ ┴ ┴ └─┘  ┴┘└┘└  └─┘
    */

    /**
     * @param bool $rgba
     * @return string
     */
    public function cssGetHex(bool $rgba = false): string
    {
        return "#" . str_pad($this->getHexString($rgba), 6, "0", STR_PAD_LEFT);
    }

    /*
    ╔═╗┌─┐┬─┐┌┬┐┌─┐┌┬┐┌┬┐┌─┐┬─┐┌─┐
    ╠╣ │ │├┬┘│││├─┤ │  │ ├┤ ├┬┘└─┐
    ╚  └─┘┴└─┴ ┴┴ ┴ ┴  ┴ └─┘┴└─└─┘
     */

    /**
     * @param bool $rgba
     * @return string
     */
    public function getHexString(bool $rgba = false): string
    {
        return dechex($this->color) . (($rgba) ? $this->getHexAlpha() : "");
    }

    /**
     * @return string
     */
    public function getHexAlpha(): string
    {
        return dechex((int) ($this->alpha * 100));
    }

    /**
     * @return string
     */
    public function cssGetRgba(): string
    {
        $rgb = self::intToRgb($this->color);
        return "rgba(" . $rgb[0] . "," . $rgb[1] . "," . $rgb[2] . "," . $this->alpha . ")";
    }
}