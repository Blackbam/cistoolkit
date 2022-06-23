<?php

namespace CisTools;

use BackedEnum;
use InvalidArgumentException;

class EnumArtist
{

    /**
     * Get all values from a backend enum.
     * @param array $enums
     * @return array
     */
    public static function getBackedEnumNames(array $enums): array
    {
        $result = [];
        foreach($enums as $enum) {
            if(!($enum instanceof BackedEnum)) {
                throw new InvalidArgumentException("You have to pass an array of backed enums.");
            }
            $result[] = $enum->name;
        }
        return $result;
    }


    /**
     * Get all values from a backend enum.
     *
     * @param array $enums
     * @return array
     */
    public static function getBackedEnumValues(array $enums): array
    {
        $result = [];
        foreach($enums as $enum) {
            if(!($enum instanceof BackedEnum)) {
                throw new InvalidArgumentException("You have to pass an array of backed enums.");
            }
            $result[] = $enum->value;
        }
        return $result;
    }

}