<?php

namespace CisTools\Enum;

use CisTools\Attribute\Author;
use CisTools\Attribute\ClassInfo;

/**
 * Class GoldenRatioMode
 * @package CisTools\Enum
 */
#[ClassInfo(summary: "Helper enum for golden ratio function")]
#[Author(name: "David Stöckl", url: "https://www.blackbam.at")]
abstract class GoldenRatioMode extends BasicEnum
{
    public const OVERALL_GIVEN = 0;
    public const LONGSIDE_GIVEN = 1;
    public const SHORTSIDE_GIVEN = 2;
}