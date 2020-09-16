<?php
namespace CisTools\Enum;

/**
 * Constats representing the PHP primitive types.
 */
abstract class Primitive extends BasicEnum {
    const STR = 0;
    const INT = 1;
    const BOOL = 2;
    const FLOAT = 3;
}