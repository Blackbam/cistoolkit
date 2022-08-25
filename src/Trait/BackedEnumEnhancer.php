<?php
namespace CisTools\Trait;

use ReflectionEnum;
use ReflectionException;

trait BackedEnumEnhancer {

    /**
     * For creating an enum by its backed enum name.
     *
     * @param string $name
     * @return static|null
     * @throws ReflectionException
     */
    public static function tryFromName(string $name): ?static
    {
        $reflection = new ReflectionEnum(static::class);

        return $reflection->hasCase($name)
            ? $reflection->getCase($name)->getValue()
            : null;
    }

}