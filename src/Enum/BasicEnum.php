<?php
declare(strict_types = 1);

namespace CisTools\Enum;

/**
 * Class BasicEnum
 * @package CisTools\Enum
 *
 * A PHP enum class (https://stackoverflow.com/questions/254514/php-and-enumerations).
 */
abstract class BasicEnum {
    private static $constCacheArray = NULL;

    /**
     * BasicEnum constructor: Enums should not be constructed.
     */
    private function __construct() {
    }

    /**
     * @return array: Associative array of class constants (key and value).
     * @throws \ReflectionException
     */
    public static function getConstants(): array {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new \ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    /**
     * @param string $name: Class constant name
     * @param bool $strict: Take casing into account?
     * @return bool
     * @throws \ReflectionException
     */
    public static function isValidName(string $name, bool $strict = false): bool {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    /**
     * @param $value: Value to check
     * @param bool $strict
     * @return bool
     * @throws \ReflectionException
     */
    public static function isValidValue($value, bool $strict = true): bool {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }

    /**
     * @return array: All defined constant names
     * @throws \ReflectionException
     */
    public static function getNames(): array {
        return array_keys(self::getConstants());
    }

    /**
     * @param string $key: The  name of the class constant
     * @param bool $strict: Take casing into account
     * @return mixed|null: The constants actual value, but NULL if it does not exist
     * @throws \ReflectionException
     */
    public static function getValue(string $key, bool $strict = false) {
        $constants = self::getConstants();

        foreach($constants as $cname => $cvalue) {
            if(!$strict) {
                if(strtolower($key) === strtolower($cname)) {
                    return $cvalue;
                }
            } else {
                if($key === $cname) {
                    return $cvalue;
                }
            }
        }
        if(array_key_exists($key,$constants)) {
            return $constants[$key];
        }
        return null;
    }
}