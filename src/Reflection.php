<?php

namespace CisTools;

use CisTools\Exception\NonSanitizeableException;
use Closure;

class Reflection
{

    /**
     * Check if a variable is an anonymous function.
     *
     * @param mixed $t : Variable to test
     * @return bool: True if the passed variable is an anonymous function
     */
    public static function isClosure($t): bool
    {
        return is_object($t) && ($t instanceof Closure);
    }

    /**
     * Like the class_name_sanitize, but for a string with multiple classes seperated by space.
     *
     * @param string $names
     * @return string
     * @throws NonSanitizeableException
     */
    public static function classNameSanitizeMulti(string $names): string
    {
        $classes = explode(" ", $names);
        foreach ($classes as $key => $class) {
            $classes[$key] = self::classNameSanitize($class);
        }
        return implode(" ", $classes);
    }

    /**
     * Simple class name sanitizer for backends. Does not allow hyphens in the beginning or non-ASCII characters.
     *
     * @param string $name
     * @return string
     * @throws NonSanitizeableException
     */
    public static function classNameSanitize(string $name): string
    {
        $name = strtolower($name); // we dislike uppercase classes
        $name = preg_replace('/[^-_a-zA-Z0-9]+/', '', $name);
        $name = ltrim(ltrim($name, "-"), '0..9');
        if(!$name) {
            throw new NonSanitizeableException("Unable to sanitize class name.");
        }
        return $name;
    }

    /**
     * Get a class name without the class path.
     *
     * @param object $object : The object to get the class short name for
     * @return string: The class shortname
     */
    public static function getClassShortName(object $object): string
    {
        return basename(str_replace('\\', '/', get_class($object)));
    }
}