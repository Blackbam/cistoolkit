<?php

declare(strict_types=1);

namespace CisTools;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

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

    /**
     * Color constructor.
     */
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
     */
    public function setInt(int $color): void
    {
        $this->color = Math::rangeInt($color, 0, self::COLOR_MAX);
    }

    /**
     * @param string $color
     */
    public function setHexString(string $color): void
    {
        $colorAlphaTuple = self::colorHexRgbaToIntWithAlpha($color);
        $this->color = $colorAlphaTuple['color'];
        $this->alpha = $colorAlphaTuple['alpha'];
    }

    /**
     * Hex RGBA color
     * @param string $hexCode : Hexadecimal color code with alpha
     * @return array: A tuple with color int and alpha float
     */
    #[ArrayShape(['color' => "int", 'alpha' => "float"])]
    public static function colorHexRgbaToIntWithAlpha(
        string $hexCode
    ): array {
        $hexCode = ltrim(trim($hexCode), "#");
        $length = strlen($hexCode);

        if ($length === 4) {
            return [
                'color' => self::colorHexToInt(substr($hexCode, 0, 3)),
                'alpha' => self::hexToAlpha(substr($hexCode, -1))
            ];
        }

        if ($length > 6) {
            return [
                'color' => self::colorHexToInt(substr($hexCode, 0, 6)),
                'alpha' => self::hexToAlpha(($length > 7) ? $hexCode[7] . $hexCode[8] : $hexCode[7])
            ];
        }
        return [
            'color' => self::colorHexToInt($hexCode),
            'alpha' => 1.0
        ];
    }

    /**
     * Hex color to RGB color
     *
     * @param $hexCode : Hexadecimal color code
     * @return int: The RGB color value
     */
    public static function colorHexToInt(string $hexCode): int
    {
        $hexCode = substr(self::colorSanitizeHexString($hexCode), 1);

        if (strlen($hexCode) === 3) {
            $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
        }

        $r = hexdec($hexCode[0] . $hexCode[1]);
        $g = hexdec($hexCode[2] . $hexCode[3]);
        $b = hexdec($hexCode[4] . $hexCode[5]);

        return (int)$b + ($g << 0x8) + ($r << 0x10);
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
        $color = trim($color);
        $regex = '|^#([A-Fa-f0-9]{3}){1,2}$|';

        if ($color && $color[0] !== "#") {
            $color = "#" . $color;
        }

        // 3 or 6 hex digits, or the empty string.
        if (preg_match($regex, $color)) {
            return $color;
        }

        if (preg_match($regex, $default)) {
            return $default;
        }

        return '#ffffff';
    }

    /**
     * @param string $hexCode
     * @return float
     */
    #[Pure]
    public static function hexToAlpha(
        string $hexCode
    ): float {
        $hexCode = ltrim(trim($hexCode), "#");
        if (strlen($hexCode) === 1) {
            $hexCode .= $hexCode;
        }
        return Math::rangeInt(hexdec($hexCode), 0, 100) / 100.00;
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param float $alpha
     */
    public function setRgba(int $red, int $green, int $blue, float $alpha = 1.0): void
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
    #[Pure] public static function rgbToInt(int $red, int $green, int $blue): int
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

    /**
     * @param $hue
     * @param $saturation
     * @param $lightness
     * @param float $alpha
     */
    public function setHsla($hue, $saturation, $lightness, float $alpha = 1.0): void
    {
        $rgb = self::hslToRgb($hue, $saturation, $lightness);
        $this->color = self::rgbToInt($rgb[0], $rgb[1], $rgb[2]);
        $this->setAlpha($alpha);
    }


    /*
    ╔═╗┌─┐┌┬┐┌┬┐┌─┐┬─┐┌─┐
    ║ ╦├┤  │  │ ├┤ ├┬┘└─┐
    ╚═╝└─┘ ┴  ┴ └─┘┴└─└─┘
    */

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
    public function setHsv($hue, $saturation, $brightness): void
    {
        $rgb = self::hsvToRgb($hue, $saturation, $brightness);
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
    public static function hsvToRgb(int $h, int $s, int $v): array
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
    public function setCmyk($cyan, $magenta, $yellow, $key): void
    {
        $rgb = self::cmykToRgb($cyan, $magenta, $yellow, $key);
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
    #[Pure]
    public static function cmykToRgb(
        $c,
        $m,
        $y,
        $k
    ): array {
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
     * @param int $red
     */
    public function setR(int $red): void
    {
        $rgb = $this->getRgb();
        $this->color = self::rgbToInt(Math::rangeInt($red, 0, 255), $rgb[1], $rgb[2]);
    }

    /**
     * @return array
     */
    public function getRgb(): array
    {
        return self::intToRgb($this->color);
    }

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
     * @param int $green
     */
    public function setG(int $green): void
    {
        $rgb = $this->getRgb();
        $this->color = self::rgbToInt($rgb[0], Math::rangeInt($green, 0, 255), $rgb[2]);
    }

    /**
     * @param int $blue
     */
    public function setB(int $blue): void
    {
        $rgb = $this->getRgb();
        $this->color = self::rgbToInt($rgb[0], $rgb[1], Math::rangeInt($blue, 0, 255));
    }

    /**
     * @return int
     */
    public function getInt(): int
    {
        return $this->color;
    }

    /**
     * @return int
     */
    public function getR(): int
    {
        return $this->getRgb()[0];
    }

    /**
     * @return int
     */
    public function getG(): int
    {
        return $this->getRgb()[1];
    }

    /**
     * @return int
     */
    public function getB(): int
    {
        return $this->getRgb()[2];
    }

    /*
    ╔═╗┌─┐┌┐┌┬  ┬┌─┐┬─┐┌┬┐┌─┐┬─┐┌─┐
    ║  │ ││││└┐┌┘├┤ ├┬┘ │ ├┤ ├┬┘└─┐
    ╚═╝└─┘┘└┘ └┘ └─┘┴└─ ┴ └─┘┴└─└─┘
    */

    /**
     * @return array
     */
    public function getCmyk(): array
    {
        $rgb = self::intToRgb($this->color);
        return self::rgbToCmyk($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * @param float|int $r
     * @param float|int $g
     * @param float|int $b
     * @return array
     */
    #[ArrayShape(['c' => "float|int", 'm' => "float|int", 'y' => "float|int", 'k' => "float|int"])]
    public static function rgbToCmyk(
        int|float $r,
        int|float $g,
        int|float $b
    ): array {
        $c = (255 - $r) / 255.0 * 100;
        $m = (255 - $g) / 255.0 * 100;
        $y = (255 - $b) / 255.0 * 100;

        $b = min([$c, $m, $y]);
        $c -= $b;
        $m -= $b;
        $y -= $b;

        return ['c' => $c, 'm' => $m, 'y' => $y, 'k' => $b];
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
        $h = $s = 0;
        if ($d !== 0) {
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
        return [round($h), round($s * 100), round($l * 100)];
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
        return self::rgbToHsv($rgb[0], $rgb[1], $rgb[2]);
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
    #[Pure]
    public static function rgbToHsv(
        int $r,
        int $g,
        int $b
    ): array {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $v = $max;

        $d = $max - $min;
        $s = ($max === 0) ? 0 : $d / $max;

        if ($max === $min) {
            $h = 0; // achromatic
        } else {
            $h = match ($max) {
                $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
                $g => ($b - $r) / $d + 2,
                $b => ($r - $g) / $d + 4,
            };
            $h /= 6;
        }

        return [$h, $s, $v];
    }

    /**
     * @return string
     */
    public function getCssRgba(): string
    {
        $rgb = self::intToRgb($this->color);
        return "rgba(" . $rgb[0] . "," . $rgb[1] . "," . $rgb[2] . "," . $this->alpha . ")";
    }

    /**
     * @param bool $withAlpha
     * @return string
     */
    #[Pure] public function getHexRgba(bool $withAlpha = false): string
    {
        $out = "#" . str_pad($this->getHexString(), 6, "0", STR_PAD_LEFT);
        if ($withAlpha) {
            $out .= str_pad($this->getHexAlpha(), 2, "0", STR_PAD_LEFT);
        }
        return $out;
    }

    /**
     * @return string
     */
    #[Pure] public function getHexString(): string
    {
        return dechex($this->color);
    }


    /*
    ╔═╗┌┬┐┌─┐┌┬┐┌─┐  ┬┌┐┌┌─┐┌─┐
    ╚═╗ │ ├─┤ │ ├┤   ││││├┤ │ │
    ╚═╝ ┴ ┴ ┴ ┴ └─┘  ┴┘└┘└  └─┘
    */

    /**
     * @return string
     */
    #[Pure] public function getHexAlpha(): string
    {
        return dechex((int)($this->alpha * 100));
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
        return $hsl[2] <= $trel;
    }

}