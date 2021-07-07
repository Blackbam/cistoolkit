<?php

namespace CisTools\Enum;

use CisTools\Attribute\Author;
use CisTools\Attribute\ClassInfo;

/**
 * Class Primitive
 * @package CisTools\Enum
 */
#[ClassInfo(summary: "Helper enum for PHP primitive types")]
#[Author(name: "David Stöckl", url: "https://www.blackbam.at")]
abstract class Primitive extends BasicEnum
{
    public const NONE = -1;
    public const STR = 0;
    public const INT = 1;
    public const BOOL = 2;
    public const FLOAT = 3;
}