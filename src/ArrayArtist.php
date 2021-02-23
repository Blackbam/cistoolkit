<?php

namespace CisTools;

/**
 * @deprecated Is now IterableArtist.
 */
class ArrayArtist
{

    /**
     * @deprecated
     */
    public static function flatUpShift(
        iterable $iterable,
        $key = 0,
        string $callbackMethod = "",
        ...$callbackArguments
    ): array {
        return IterableArtist::flatUpShift($iterable, $key, $callbackMethod, ...$callbackArguments);
    }

    /**
     * @deprecated
     */
    public static function flatten(array $array, int $maxDepth = -1): array
    {
        return IterableArtist::flatten($array, $maxDepth);
    }

    /**
     * @deprecated
     */
    public static function hasIncreasingValues(array $array): bool
    {
        return IterableArtist::hasIncreasingValues($array);
    }

    /**
     * @deprecated
     */
    public static function autoVivifyDynamicKeyPath(array &$out, array $pathKeys, $val): void
    {
        IterableArtist::autoVivifyDynamicKeyPath($out, $pathKeys, $val);
    }

}