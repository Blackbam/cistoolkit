<?php
namespace CisTools;

class Reflection {

    /**
     * Check if a variable is an anonymous function.
     *
     * @param mixed $t : Variable to test
     * @return bool: True if the passed variable is an anonymous function
     */
    public static function isClosure($t): bool {
        return is_object($t) && ($t instanceof \Closure);
    }

    /**
     * Simple class name sanitizer for backends. Does not allow hyphens in the beginning or non-ASCII characters.
     *
     * @param $name
     * @return string
     */
    public static function classNameSanitize(string $name): string {
        $name = strtolower($name); // we dislike uppercase classes
        $name = preg_replace('/[^-_a-zA-Z0-9]+/', '', $name);
        return ltrim(ltrim($name, "-"), '0..9');
    }


    /**
     * Like the class_name_sanitize, but for a string with multiple classes seperated by space.
     *
     * @param $names
     * @return string
     */
    public static function classNameSanitizeMulti(string $names): string {
        $classes = explode(" ", $names);
        foreach ($classes as $key => $class) {
            $classes[$key] = self::classNameSanitize($class);
        }
        return implode(" ", $classes);
    }

}