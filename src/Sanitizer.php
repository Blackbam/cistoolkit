<?php
namespace CisTools;

use CisTools\Enum\Primitive;

class Sanitizer {

    /**
     * In case you are unsure if an array key/object property exists and you want to get a (possibly typesafe) defined result.
     *
     * @param $var array|object: An array or object with the possible key/property
     * @param $key array|string: The key. For a multidimensional associative array, you can pass an array.
     * @param $empty : If the key does not exist, this value is returned.
     * @param Primitive $primitive : The type of the given variable (-1 for ignoring this feature).
     *
     * @return mixed: The (sanitized) value at the position key or the given default value if nothing found.
     */
    public static function resempty(&$var, $key, $empty = "",Primitive $primitive = null) {

        $tcast = function ($var, $primitive) {
            switch (true):
                case $primitive === Primitive::STR:
                    $var = strval($var);
                    break;
                case $primitive === Primitive::INT:
                    $var = intval($var);
                    break;
                case $primitive === Primitive::BOOL:
                    $var = boolval($var);
                    break;
                case $primitive === Primitive::FLOAT:
                    $var = floatval($var);
                    break;
            endswitch;
            return $var;
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
                        } else {
                            $obj_key = $key[$i];
                            $tpclass = $tpclass->$obj_key;
                        }
                    } else {
                        return $tcast($empty, $primitive);
                    }
                }
                return $tcast($empty, $primitive);
            }

            if (property_exists($var, $key)) {
                return $tcast($var->$key, $primitive);
            }
        } else if (is_array($var)) {
            if (is_array($key)) {
                $tpar = $var;
                $dimensions = count($key);

                for ($i = 0; $i < $dimensions; $i++) {
                    if (array_key_exists($key[$i], $tpar)) {
                        if ($i === $dimensions - 1) {
                            return $tcast($tpar[$key[$i]], $primitive);
                        } else {
                            $tpar = $tpar[$key[$i]];
                        }
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
}