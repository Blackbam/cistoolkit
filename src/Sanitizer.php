<?php

namespace CisTools;

use CisTools\Enum\Primitive;

class Sanitizer
{

    /**
     * In case you are unsure if an array key/object property exists and you want to get a (possibly typesafe) defined result.
     *
     * @param $var array|object: An array or object with the possible key/property
     * @param $key array|string: The key. For a multidimensional associative array, you can pass an array.
     * @param mixed $empty : If the key does not exist, this value is returned.
     * @param int $primitive : The type of the given variable (-1 for ignoring this feature).
     *
     * @return mixed: The (sanitized) value at the position key or the given default value if nothing found.
     */
    public static function resempty(
        array|object $var,
        array|string $key,
        mixed $empty = "",
        int $primitive = -1
    ): mixed {
        $tcast = static function (mixed $var, int $primitive): mixed {
            return match (true) {
                $primitive === Primitive::STR => (string)$var,
                $primitive === Primitive::INT => (int)$var,
                $primitive === Primitive::BOOL => (bool)$var,
                $primitive === Primitive::FLOAT => (float)$var,
                default => $var
            };
        };


        if (is_object($var)) {
            if (is_array($key)) {
                $tpclass = $var;
                $dimensions = count($key);
                for ($i = 0; $i < $dimensions; $i++) {
                    if (property_exists($tpclass, $key[$i])) {
                        if ($i === $dimensions - 1) {
                            $obj_key = $key[$i];
                            return $tcast($tpclass->$obj_key, $primitive);
                        }

                        $obj_key = $key[$i];
                        $tpclass = $tpclass->$obj_key;
                    } else {
                        return $tcast($empty, $primitive);
                    }
                }
                return $tcast($empty, $primitive);
            }

            if (property_exists($var, $key)) {
                return $tcast($var->$key, $primitive);
            }
        } elseif (is_array($var)) {
            if (is_array($key)) {
                $tpar = $var;
                $dimensions = count($key);

                for ($i = 0; $i < $dimensions; $i++) {
                    if (array_key_exists($key[$i], $tpar)) {
                        if ($i === $dimensions - 1) {
                            return $tcast($tpar[$key[$i]], $primitive);
                        }

                        $tpar = $tpar[$key[$i]];
                    } else {
                        return $tcast($empty, $primitive);
                    }
                }
                return $tcast($empty, $primitive);
            }

            if (array_key_exists($key, $var)) {
                return $tcast($var[$key], $primitive);
            }
        }
        return $tcast($empty, $primitive);
    }

    /**
     * If the variable is empty return the default, return the variable otherwise.
     *
     * @param mixed $var : Any variable
     * @param mixed $default : The default to use if the variable is empty
     * @return mixed: The default value if false. True otherwise.
     */
    public static function defempty(mixed $var, mixed $default): mixed
    {
        return (empty($var)) ? $default : $var;
    }

}