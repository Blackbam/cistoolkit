<?php
namespace CisTools\Enum;

enum Type
{
    case STRING;
    case BOOLEAN;
    case INTEGER;
    case FLOAT;
    case ARRAY;
    case OBJECT;
    case NULL;
    case RESOURCE;
    case ENUM;
}