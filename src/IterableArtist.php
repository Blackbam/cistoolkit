<?php

namespace CisTools;

use JetBrains\PhpStorm\Pure;

/**
 * Class IterableArtist: For advanced operations on arrays and other iterables.
 *
 * @package CisTools
 */
class IterableArtist
{

    /**
     * Flatten an array of arrays or objects by one level if only needing a certain key value from a sub array/sub object.
     *
     * Example: $example = [["foo"=>"bar"],["foo"=>"cheese"]]
     * Call: flatUpShift($example,"foo")
     * Result: ["bar","cheese"]
     *
     * @param iterable $iterable : The input array.
     * @param mixed $key : The key to shift into the extracted array. Default is 0.
     * @param string $callbackMethod : For an array of objects you can use a callback function instead of a key. The key is ignored if this argument is used.
     * @param mixed ...$callbackArguments : Any arguments for the callback function.
     * @return array: The result array
     */
    public static function flatUpShift(
        iterable $iterable,
        mixed $key = 0,
        string $callbackMethod = "",
        ...$callbackArguments
    ): array {
        $a = [];
        foreach ($iterable as $item) {
            if (is_array($item)) {
                $a[] = $item[$key];
            } elseif (is_object($item)) {
                if ($callbackMethod) {
                    $a[] = $item->$callbackMethod(...$callbackArguments);
                } else {
                    $a[] = $item->$key;
                }
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
    public static function flatten(array $array, int $maxDepth = -1): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && $maxDepth !== 0) {
                $result = array_merge(...[$result, self::flatten($value, ($maxDepth > 0) ? $maxDepth - 1 : -1)]);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Check if all values within an array are increasing (e.g. -1,1,2,4,19,18 returns true, but 1,4,2 returns false).
     *
     * @param $array : Input array.
     * @return bool: True if the array values are increasing.
     */
    public static function hasIncreasingValues(array $array): bool
    {
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
     * Merge an arbitrary amount of arrays together and eliminiate duplicates.
     * @param array ...$arrays
     * @return array
     */
    #[Pure]
    public static function fusion(
        array ...$arrays
    ): array {
        return array_unique(array_merge(...$arrays), SORT_REGULAR);
    }

    /**
     * Count array dimensions
     * @param array $array
     * @return int: The dimensions of an array
     */
    public static function countDim(array $array): int
    {
        if (is_array(reset($array))) {
            $result = self::countDim(reset($array)) + 1;
        } else {
            $result = 1;
        }
        return $result;
    }

    /**
     * @param array $array
     * @return array
     */
    #[Pure]
    public static function getDuplicates(
        array $array
    ): array {
        return array_unique(array_diff_assoc($array, array_unique($array)));
    }

    /**
     * Remove duplicate elements from an array by callback.
     *
     * @param array $array : An array to eliminate duplicates by callback
     * @param callable $callback : Callback accepting an array element returning the value to compare.
     * @param bool $preserveKeys : Add true if the keys should be perserved (note that if duplicates eliminated the first key is used).
     * @return array: An array unique by the given callback
     */
    public static function unique(array $array, callable $callback, bool $preserveKeys = false): array
    {
        $unique = array_intersect_key($array, array_unique(array_map($callback, $array)));
        return ($preserveKeys) ? $unique : array_values($unique);
    }

    /**
     * Add to a path within a dynamic array no matter if the path already exists or not.
     *
     * @param array $out
     * @param array $pathKeys
     * @param $val : Whatever value
     * @return void
     */
    public static function autoVivifyDynamicKeyPath(array &$out, array $pathKeys, $val): void
    {
        $cursor = &$out;
        foreach ($pathKeys as $key) {
            if (!isset($cursor[$key]) || !is_array($cursor[$key])) {
                $cursor[$key] = array();
            }
            $cursor = &$cursor[$key];
        }
        $cursor = $val;
    }

    /**
     * Creates an HTML table from a two-dimensional array.
     *
     * @param array $twoDim: The two dimensional array.
     * @param array $head: The head for the table (optional one dimensional array).
     * @param bool $useRowKeyVerticalHead: The ready table HTML.
     * @return string
     */
    public static function toHtmlTable(array $twoDim, array $head = [], bool $useRowKeyVerticalHead = false): string
    {
        $thead = !empty($head) ? '<tr><th>' . implode('</th><th>', $head) . '</th></tr>' : '';

        $tbody = "";

        array_walk($twoDim, static function($val, $key) use(&$tbody,$useRowKeyVerticalHead) {
            $safeVal = array_map('htmlentities', $val);
            $rowHead = ($useRowKeyVerticalHead) ? '<th>' . htmlentities($key) . '</th>' : '';
            $tbody .= '<tr>' . $rowHead . '<td>' . implode('</td><td>', $safeVal) . '</td></tr>';
        });

        return '<table>' . $thead . $tbody . '</table>';
    }

}