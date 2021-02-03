<?php

namespace CisTools;

/**
 * Class Image: Intended to work with e.g. the Gregwar Image library
 * @package CisTools
 */
class Image {

    protected string $imageserverUrl;

    public function __construct(string $imageserverUrl) {
        if (!filter_var($imageserverUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception("Invalid Imageserver URL given to class constructor.");
        }
        $this->imageserverUrl = $imageserverUrl;
    }

    /**
     * Print the timthumb path for a given image and specifiy width, height, quality and crop mode ...
     *
     * Classic way without srcset.
     *
     * @param string $src : The image URL
     * @param integer $w : The image width in pixels
     * @param integer $h : The image height in pixels
     * @param integer $q : The image quality (0-100)
     * @param integer $zc : Timthumb zoom crop (0-3)
     * @return string: The URL
     */
    public function generateUrl(string $src, int $w = -1, int $h = -1, int $q = 80, int $zc = 1): string {
        $src = trim($src);
        if (substr($src, -4) === ".svg") {
            return $src;
        }
        return $this->imageserverUrl . "?src=" . $src . (($w > -1) ? '&w=' . $w : "") . (($h > -1) ? '&h=' . $h : "") . '&q=' . $q . '&zc=' . $zc;
    }


    /**
     * Delivers the srcset attribute for a given image based on the given parameters.
     *
     * Note: Do not forget to add the "sizes" attribute if the images does not expand accross the whole screen width.
     *
     * @param string $src : The source of the base image.
     * @param float $wh_relation : Width / Height relation. If you want an image of 200 width and 100 height pass 0.5. Pass false or Zero for auto height.
     * @param integer|array $step : If an integer is passed, two steps above and two steps below for viewport. 2 Steps above for density based. If an array is passes the exact widths. Only updwards for density.
     * @param bool $device_pixel_mode : True for device pixel based selection, viewport based selection otherwise.
     * @param integer $q : Quality of timthumb image.
     * @param integer $zc : Zoom Crop mode of timthumb.
     * @param bool $as_data
     * @param string $imgsrvParamAdd : A get string to add to each link request to the image server.
     *
     * @return string The prepared src & srcset attributes for the given source
     */
    public function generateSrcset(string $src, float $wh_relation = -1, $step = 2048, bool $device_pixel_mode = false, int $q = 80, int $zc = 1, bool $as_data = false, string $imgsrvParamAdd = ""): string {

        $data_attr = ' ';
        if ($as_data === true) {
            $data_attr = " data-";
        }

        if (!is_array($step) && !is_int($step)) {
            $step = (is_numeric($step)) ? (int)$step : 2048;
        }

        $srcset_values = $this->generateSrcsetValue($src, $wh_relation, $step, $device_pixel_mode, false, $q, $zc, true, $imgsrvParamAdd);

        $out = "";
        if ($device_pixel_mode) {
            $out .= $data_attr . 'src="' . reset($srcset_values[1]) . '" ';
            $out .= $data_attr . 'srcset="' . $srcset_values[0] . '" ';
        } else {
            if (is_array($step)) {
                $base_step = $step[(int)floor(count($step) / 2)];
            } else {
                $base_step = $step;
            }
            $out .= $data_attr . 'src="' . $srcset_values[1][$base_step] . '" ';
            $out .= $data_attr . 'srcset="' . $srcset_values[0] . '" ';
        }
        return $out;
    }

    /**
     * Delivers the the imageset attribute for a given image based on the given parameters with background-image as CSS property.
     *
     * @param string $src : The source of the base image.
     * @param float $wh_relation : Width / Height relation. If you want an image of 200 width and 100 height pass 0.5. Pass false or Zero for auto height.
     * @param integer|array $step : If an integer is passed, two steps above and two steps below for viewport. 2 Steps above for density based. If an array is passes the exact widths. Only updwards for density.
     * @param boolean $device_pixel_mode : True for device pixel based selection, viewport based selection otherwise.
     * @param integer $q : Quality of timthumb image.
     * @param integer $zc : Zoom Crop mode of timthumb.
     * @param string $imgsrvParamAdd : A get string to add to each link request to the image server.
     *
     * @return string The ready CSS Properties for the background image, including image-set
     */
    public function generateImageset(string $src, float $wh_relation = -1, $step = 2048, bool $device_pixel_mode = true, int $q = 80, int $zc = 1, string $imgsrvParamAdd = ""): string {
        $srcset_values = $this->generateSrcsetValue($src, $wh_relation, $step, $device_pixel_mode, true, $q, $zc, true, $imgsrvParamAdd);

        if ($device_pixel_mode) {
            $out = 'background-image: url(' . reset($srcset_values[1]) . ');';
            $out .= 'background-image: image-set(' . $srcset_values[0] . ');';
        } else {
            if (is_array($step)) {
                $base_step = $step[(int)floor(count($step) / 2)];
            } else {
                $base_step = $step;
            }
            $out = 'background-image: url(' . $srcset_values[1][$base_step] . ');';
            $out .= 'background-image: image-set(' . $srcset_values[0] . ');';
        }
        return $out;
    }

    /**
     * Delivers the srcset or image set attribute value for a given image based on the given parameters.
     *
     * @param string $src : The source of the base image.
     * @param int $wh_relation : Width / Height relation. If you want an image of 200 width and 100 height pass 0.5. Pass false or Zero for auto height.
     * @param int|array $step : If an integer is passed, two steps above and two steps below for viewport. 2 Steps above for density based. If an array is passes the exact widths. Only updwards for density.
     * @param bool $device_pixel_mode : True for device pixel based selection, viewport based selection otherwise.
     * @param bool $imageset : True to use imageset
     * @param integer $q : Quality of timthumb image.
     * @param integer $zc : Zoom Crop mode of timthumb.
     * @param bool $with_urls
     * @param string $imgsrvParamAdd : A get string to add to each link request to the image server.
     * @return string|array
     */
    function generateSrcsetValue(string $src, int $wh_relation = -1, $step = 2048, bool $device_pixel_mode = false, bool $imageset = false, int $q = 80, int $zc = 1, bool $with_urls = false, string $imgsrvParamAdd = "") {

        $steps = array();

        if (is_array($step)) {
            $steps = $step;
        } else {
            $step = (int)$step;
            if (!$device_pixel_mode) {
                array_push($steps, round($step / 4), round($step / 2), $step, $step * 1.5, $step * 2);
            } else {
                array_push($steps, $step, $step * 1.5, $step * 2);
            }
        }

        $urls = $this->generateSrcsetUrls($src, $wh_relation, $steps, $q, $zc, $imgsrvParamAdd);

        $out = "";
        if ($device_pixel_mode) {
            $base = key($urls);

            $comma = false;
            foreach ($urls as $width => $url) {
                if ($comma) {
                    $out .= ",";
                } else {
                    $comma = true;
                }
                if ($imageset) {
                    $out .= "url('" . $url . "') " . ($width / $base) . "x";
                } else {
                    $out .= $url . " " . ($width / $base) . "x";
                }
            }
        } else {
            $comma = false;
            foreach ($urls as $width => $url) {
                if ($comma) {
                    $out .= ",";
                } else {
                    $comma = true;
                }
                if ($imageset) {
                    $out .= "url('" . $url . "') " . $width . "w";
                } else {
                    $out .= $url . " " . $width . "w";
                }
            }
        }
        if ($with_urls) {
            return array($out, $urls);
        }
        return $out;
    }

    /**
     * Get timthumb URLs for a set of widths.
     * @param string $src : URL to the original image
     * @param int $wh_relation :  Width / Height relation. If you want an image of 200 width and 100 height pass 0.5.
     * @param array $steps : An array of all widths which should be made
     * @param int $q : Quality according to timthumb documentation.
     * @param int $zc : Zoom Crop according to timthumb documentation.
     * @param string $imgsrvParamAdd : A get string to add to each link request to the image server.
     * @return array: An array of URLs with width as keys, ascending by width.
     */
    function generateSrcsetUrls(string $src, int $wh_relation = -1, array $steps = [512, 1024, 2048, 3072, 4096], int $q = 80, int $zc = 1, string $imgsrvParamAdd = ""): array {

        $steps = array_filter(array_map('intval', $steps), function ($a) {
            return $a > 1 && $a < PHP_INT_MAX;
        });
        sort($steps);

        if (empty($steps)) {
            trigger_error('Invalid sizes given to image calculator.');
        }

        $urls = array();

        foreach ($steps as $step) {
            $height = -1;
            if ((float)$wh_relation > 0.0) {
                $height = (1 / (float)$wh_relation * $step);
            }
            $urls[$step] = $this->generateUrl($src, $step, $height, $q, $zc) . $imgsrvParamAdd;
        }

        return $urls;
    }

}