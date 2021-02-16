<?php

namespace CisTools\Enum;

/**
 * Constants representing the PHP primitive types.
 */
abstract class Primitive extends BasicEnum
{
    public const NONE = -1;
    public const STR = 0;
    public const INT = 1;
    public const BOOL = 2;
    public const FLOAT = 3;
}