<?php

namespace CisTools;

/**
 * @deprecated Is not IterableArtist.
 */
class ArrayArtist {

    public static function flatUpShift(iterable $iterable, $key = 0, string $callbackMethod = "", ...$callbackArguments): array {
        return IterableArtist::flatUpShift($iterable,$key,$callbackMethod,...$callbackArguments);
    }

    public static function flatten(array $array, int $maxDepth = -1): array {
        return IterableArtist::flatten($array,$maxDepth);
    }

    public static function hasIncreasingValues(array $array): bool {
        return IterableArtist::hasIncreasingValues($array);
    }

    public static function autoVivifyDynamicKeyPath(array &$out, array $pathKeys, $val): void  {
        IterableArtist::autoVivifyDynamicKeyPath($out,$pathKeys,$val);
    }

}