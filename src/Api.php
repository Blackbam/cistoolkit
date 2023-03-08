<?php

namespace CisTools;

use CisTools\Exception\XMLCreationException;
use Exception;
use SimpleXMLElement;

/**
 * Class Api: Helpers for API communication
 * @package CisTools
 */
class Api
{
    /**
     * Creates a .csv file from an array and returns the result as string.
     *
     * Works similar to fputcsv()
     *
     * @param array $array : The array to convert to CSV
     * @param bool $head : Use the array keys as column headings.
     * @param string $delimiter : The optional delimiter parameter sets the field delimiter (one character only). Default is "," but e.g. MS Excel may require ";"
     * @param string $enclosure : The optional enclosure parameter sets the field enclosure (one character only).
     * @param string $escapeChar : The optional escape_char parameter sets the escape character (at most one character). An empty string ("") disables the proprietary escape mechanism.
     * @return string: The CSV string.
     */
    public static function array2Csv(
        array &$array,
        bool $head = true,
        string $delimiter = ",",
        string $enclosure = '"',
        string $escapeChar = "\\"
    ): string {
        if (empty($array)) {
            return "";
        }
        ob_start();
        $df = fopen("php://output", 'wb');
        if ($head) {
            fputcsv($df, array_keys(reset($array)), $delimiter, $enclosure, $escapeChar);
        }
        foreach ($array as $row) {
            fputcsv($df, $row, $delimiter, $enclosure, $escapeChar);
        }
        fclose($df);
        return ob_get_clean();
    }


    /**
     * @param string $csvHeadline : The first line (columns) of the CSV as a string
     * @return string
     */
    public static function csvDetectDelimiter(string $csvHeadline): string
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($csvHeadline, $delimiter));
        }

        return array_search(max($delimiters), $delimiters, true);
    }


    /**
     * Converts an array to XML.
     *
     * @param array $data
     * @param string $rootTag
     * @return string
     * @throws XMLCreationException
     */
    public static function array2Xml(array $data, string $rootTag): string
    {
        $element = self::array2SimpleXMLElement($data, $rootTag);
        $out = $element->asXML();
        if (!$out) {
            throw new XMLCreationException("Unable to create XML from SimpleXmlElement.");
        }
        return $out;
    }

    /**
     * Converts an array to a SimpleXMLElement object
     *
     * @param array $data
     * @param string $rootTag
     * @return SimpleXMLElement
     * @throws XMLCreationException
     */
    public static function array2SimpleXMLElement(array $data, string $rootTag): SimpleXMLElement
    {
        // creating object of SimpleXMLElement
        try {
            $element = new SimpleXMLElement('<?xml version="1.0"?><'.$rootTag.'></'.$rootTag.'>');
        } catch (Exception $e) {
            throw new XMLCreationException("Unable to create XML element. Check if your root tag is valid. Details: ".$e->getMessage());
        }
        return self::addArray2SimpleXMLElement($data, $element);
    }

    /**
     * Adds an array to an existing SimpleXMLElement.
     *
     * @param array $data
     * @param SimpleXMLElement $simpleXMLElement
     * @return SimpleXMLElement
     * @throws XMLCreationException
     */
    public static function addArray2SimpleXMLElement(array $data, SimpleXMLElement $simpleXMLElement): SimpleXMLElement
    {
        foreach ($data as $tag => $tagContent) {
            if (is_array($tagContent)) {
                $subnode = $simpleXMLElement->addChild($tag);
                if (is_null($subnode)) {
                    throw new XMLCreationException("Unable to add child: '".$tag."'. Maybe invalid XML tag name?");
                }
                self::addArray2SimpleXMLElement($tagContent, $subnode);
            } else {
                $simpleXMLElement->addChild((string)$tag, htmlspecialchars((string)$tagContent));
            }
        }

        return $simpleXMLElement;
    }
}