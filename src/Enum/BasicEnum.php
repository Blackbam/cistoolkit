<?php

declare(strict_types=1);

namespace CisTools\Enum;

use ReflectionClass;
use ReflectionException;

/**
 * Class BasicEnum
 * @package CisTools\Enum
 *
 * A PHP enum class (https://stackoverflow.com/questions/254514/php-and-enumerations).
 */
abstract class BasicEnum
{
    private static ?array $constCacheArray = null;

    /**
     * BasicEnum constructor: Enums should not be constructed.
     */
    private function __construct()
    {
    }

    /**
     * @param string $name : Class constant name
     * @param bool $strict : Take casing into account?
     * @return bool
     * @throws ReflectionException
     */
    public static function isValidName(string $name, bool $strict = false): bool
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys, true);
    }

    /**
     * @return array: Associative array of class constants (key and value).
     * @throws ReflectionException
     */
    public static function getConstants(): array
    {
        if (self::$constCacheArray === null) {
            self::$constCacheArray = [];
        }
        $calledClass = static::class;
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    /**
     * @param $value : Value to check
     * @param bool $strict
     * @return bool
     * @throws ReflectionException
     */
    public static function isValidValue($value, bool $strict = true): bool
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }

    /**
     * @return array: All defined constant names
     * @throws ReflectionException
     */
    public static function getNames(): array
    {
        return array_keys(self::getConstants());
    }

    /**
     * @param string $key : The  name of the class constant
     * @param bool $strict : Take casing into account
     * @return mixed: The constants actual value, but NULL if it does not exist
     * @throws ReflectionException
     */
    public static function getValue(string $key, bool $strict = false): mixed
    {
        $constants = self::getConstants();

        foreach ($constants as $cname => $cvalue) {
            if (!$strict) {
                if (strtolower($key) === strtolower($cname)) {
                    return $cvalue;
                }
            } elseif ($key === $cname) {
                return $cvalue;
            }
        }
        return $constants[$key] ?? null;
    }
}