<?php

namespace CisTools\Enum;

abstract class GoldenRatioMode extends BasicEnum {
    public const OVERALL_GIVEN = 0;
    public const LONGSIDE_GIVEN = 1;
    public const SHORTSIDE_GIVEN = 2;
}