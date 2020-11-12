<?php

namespace CisTools;

/**
 * Class ArrayArtist: For advanced operations on arrays.
 *
 * @package CisTools
 */
class ArrayArtist {

    /**
     * Flatten an array of arrays or objects by one level if only needing a certain key value from a sub array/sub object.
     *
     * Example: $example = [["foo"=>"bar"],["foo"=>"cheese"]]
     * Call: flatUpShift($example,"foo")
     * Result: ["bar","cheese"]
     *
     * @param array $array : The input array.
     * @param mixed $key : The key to flatupshift. Default is 0.
     * @return array: The result array
     */
    public static function flatUpShift(array $array, $key = 0): array {
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

    /**
     * Flatten an multidimensional array.
     *
     * Note: Keys are not preserved!
     *
     * @param $array : The array to flatten
     * @param int $maxDepth : Set the maximum number of dimensions to flatten. A negative number (default) means to
     * flatten it completely, a positive number e.g. two means that only the first two dimensions are flattened.
     * @return array: The flattened result.
     */
    public static function flatten(array $array, int $maxDepth = -1): array {
        $return = array();
        foreach ($array as $key => $value) {
            if (is_array($value) && $maxDepth != 0) {
                $return = array_merge($return, self::flatten($value, ($maxDepth > 0) ? $maxDepth - 1 : -1));
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Check if all values within an array are increasing (e.g. -1,1,2,4,19,18 returns true, but 1,4,2 returns false).
     *
     * @param $array : Input array.
     * @return bool: True if the array values are increasing.
     */
    public static function hasIncreasingValues(array $array): bool {

        if (count($array) <= 1) {
            return true;
        }

        $reversed = array_reverse($array);
        $acFirst = array_pop($reversed);
        if (end($reversed) <= $acFirst) {
            return false;
        }
        return self::hasIncreasingValues(array_reverse($reversed));
    }

    /**
     * Add to a path within a dynamic array no matter if the path already exists or not.
     *
     * @param array $out
     * @param array $pathKeys
     * @param $val: Whatever value
     * @return void
     */
    public static function autoVivifyDynamicKeyPath(array &$out, array $pathKeys, $val): void  {
        $cursor = & $out;
        foreach ($pathKeys as $key) {
            if (!isset($cursor[$key]) || !is_array($cursor[$key])) {
                $cursor[$key] = array();
            }
            $cursor = &$cursor[$key];
        }
        $cursor = $val;
    }
}