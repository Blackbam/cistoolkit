<?php
namespace CisTools;

/**
 * Class ArrayArtist: For advanced operations on arrays.
 *
 * @package CisTools
 */
class ArrayArtist {

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

}