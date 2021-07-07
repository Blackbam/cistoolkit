<?php

namespace CisTools;

use CisTools\Attribute\Author;
use CisTools\Attribute\ClassInfo;

/**
 * Class HtmlForm
 * @package CisTools
 */
#[ClassInfo(summary: "HTML form related helpers")]
#[Author(name: "David Stöckl", url: "https://www.blackbam.at")]
class HtmlForm
{
    /**
     * Returns a ready HTML selector for a given PHP array.
     *
     * @param array $array : The array to build the selector from. You can pass two-dimensional arrays for creating a selector with opt-groups
     * @param array $attributes : An associative array of attributes in the form $attr_name => $attr_value. If the key is not a valid HTML attribute name then we try value="value".
     * @param array $preSelect : An array of strings with values to preSelect. If this is not a multiselect, only the first entry will be noticed.
     * @param boolean $addEmptyOption (optional, default true): If there should be the possibility to select nothing.
     * @param boolean $valuesAreOptionKeys (optional, default false): Select true, if the option VALUES should be the array KEYs.
     * @return string: The ready HTML.
     * @throws Exception\NonSanitizeableException
     */
    public static function selectFromArray(
        array $array,
        array $attributes = [],
        array $preSelect = [],
        bool $addEmptyOption = false,
        bool $valuesAreOptionKeys = false
    ): string {
        $twoDimensional = (IterableArtist::countDim($array) > 1);

        $multiple = false;
        if (in_array("multiple", $attributes, true)) {
            $multiple = true;
        }

        $name = "select";
        if (array_key_exists("name", $attributes)) {
            $name = htmlspecialchars($attributes["name"], ENT_COMPAT | ENT_HTML5);
        }

        // if multiple, make sure name is array
        if ($multiple && (substr($name, -2) !== "[]")) {
            $name .= "[]";
        }

        // prepare all attributes
        $readyAttributes = [];
        foreach ($attributes as $attKey => $attValue) {
            if (!is_numeric($attKey)) {
                $readyAttributes[Html::sanitizeAttributeName($attKey)] = htmlspecialchars(
                    $attValue,
                    ENT_COMPAT | ENT_HTML5
                );
            } else {
                $readyAttributes[Html::sanitizeAttributeName($attValue)] = htmlspecialchars(
                    $attValue,
                    ENT_COMPAT | ENT_HTML5
                );
            }
        }

        $readyAttributes["name"] = $name;

        // generate the output
        $out = '<select ';
        foreach ($readyAttributes as $attName => $attValue) {
            $out .= $attName . '="' . $attValue . '" ';
        }
        $out .= ">";
        $out .= ($addEmptyOption) ? '<option value=""></option>' : '';

        $genOpt = static function ($options) use ($valuesAreOptionKeys, $preSelect) {
            $out = "";
            foreach ($options as $option => $optionValue) {
                $key = ($valuesAreOptionKeys) ? $option : $optionValue;
                $out .= '<option value="' . htmlspecialchars($key, ENT_COMPAT | ENT_HTML5) . '" ' . (in_array(
                        $key,
                        $preSelect,
                        true
                    ) ? 'selected="selected"' : '') . '>' . htmlspecialchars(
                        $optionValue,
                        ENT_NOQUOTES | ENT_HTML5
                    ) . '</option>';
            }
            return $out;
        };

        if (!$twoDimensional) {
            $out .= $genOpt($array);
        } else {
            foreach ($array as $optgroupLabel => $optgroupOptions) {
                $out .= '<optgroup label="' . htmlspecialchars($optgroupLabel, ENT_COMPAT | ENT_HTML5) . '">' . $genOpt(
                        $optgroupOptions
                    ) . '</optgroup>';
            }
        }
        return $out . '</select>';
    }
}