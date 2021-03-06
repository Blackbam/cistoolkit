<?php

namespace CisTools;

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
     * @param string $escape_char : The optional escape_char parameter sets the escape character (at most one character). An empty string ("") disables the proprietary escape mechanism.
     * @return string: The CSV string.
     */
    public static function array2Csv(
        array &$array,
        bool $head = true,
        string $delimiter = ",",
        string $enclosure = '"',
        string $escape_char = "\\"
    ): string {
        if (empty($array)) {
            return "";
        }
        ob_start();
        $df = fopen("php://output", 'wb');
        if ($head) {
            fputcsv($df, array_keys(reset($array)), $delimiter, $enclosure, $escape_char);
        }
        foreach ($array as $row) {
            fputcsv($df, $row, $delimiter, $enclosure, $escape_char);
        }
        fclose($df);
        return ob_get_clean();
    }


    /**
     * @param string $csvHeadline: The first line (columns) of the CSV as a string
     * @return string
     */
    public static function csvDetectDelimiter(string $csvHeadline): string {

            $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

            foreach ($delimiters as $delimiter => &$count) {
                $count = count(str_getcsv($csvHeadline, $delimiter));
            }

            return array_search(max($delimiters), $delimiters, true);
    }
}