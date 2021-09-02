<?php

namespace CisTools;

use CisTools\Exception\InvalidParameterException;
use CisTools\Exception\NonSanitizeableException;
use Closure;
use JetBrains\PhpStorm\Pure;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class Reflection
{

    /**
     * Get a reflection function object for a callable.
     * @param callable $callable
     * @return ReflectionFunctionAbstract
     * @throws ReflectionException
     */
    public static function reflectionOf(callable $callable): ReflectionFunctionAbstract
    {
        if ($callable instanceof Closure) {
            return new ReflectionFunction($callable);
        }
        if (is_string($callable)) {
            $pcs = explode('::', $callable);
            return count($pcs) > 1 ? new ReflectionMethod($pcs[0], $pcs[1]) : new ReflectionFunction($callable);
        }
        if (!is_array($callable)) {
            $callable = [$callable, '__invoke'];
        }
        return new ReflectionMethod($callable[0], $callable[1]);
    }

    /**
     * Check if a variable is an anonymous function.
     *
     * @param mixed $t : Variable to test
     * @return bool: True if the passed variable is an anonymous function
     */
    #[Pure]
    public static function isClosure(
        mixed $t
    ): bool {
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
        if (!$name) {
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

    /**
     * @param object|string $objectOrClass
     * @param string $nameOfTrait
     * @param bool $autoload: Used like in class_uses()
     * @return bool
     * @throws InvalidParameterException If the class you pass does not exist this exception is thrown.
     */
    public static function usesTrait(object|string $objectOrClass,string $nameOfTrait,bool $autoload = true): bool
    {
        if(is_object($objectOrClass)) {
            $class = $objectOrClass::class;
        } else {
            $class = $objectOrClass;
        }

        if(!class_exists($class)) {
            throw new InvalidParameterException("The class " . $class . " passed to the usesTrait function does not exist. Please check your dependencies.");
        }

        $traits = class_uses($class,$autoload);
        return in_array($nameOfTrait, $traits, true);
    }
}