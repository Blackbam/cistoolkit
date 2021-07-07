<?php
namespace CisTools\Attribute\Validator;

use CisTools\Attribute\Author;
use CisTools\Attribute\ClassInfo;
use ReflectionClass;

/**
 * Class Validator
 * @package CisTools\Attribute\Validator
 */
#[ClassInfo(summary: "Powerful debug helpers")]
#[Author(name: "David Stöckl", url: "https://www.blackbam.at")]
class Validator
{
    public const TARGET_CLASS = 1;
    public const TARGET_FUNCTION = 2;
    public const TARGET_METHOD = 4;
    public const TARGET_PROPERTY = 8;
    public const TARGET_CLASS_CONSTANT = 16;
    public const TARGET_PARAMETER = 32;
    public const TARGET_ALL = 64;

    protected array $attributesToValidate;

    public static function validateAttributes(array $attributesToValidate)

    /**
     * Validate attributes of all registered classes
     */
    public static function validateClasses(array $attributesToValidate,bool $withMethods = true, bool $withConstants = true): void
    {
        foreach (get_declared_classes() as $class) {
            try {
                $reflectionClass = new ReflectionClass($class);
            } catch (\Throwable $throwable) {
                continue;
            }
            $attributes = $reflectionClass->getAttributes();

            // only do this for the defined attributes
            foreach ($attributes as $attribute) {
                if(in_array($attribute->getName(),$attributesToValidate,true)) {
                    $attribute->newInstance();
                }
            }

            if($withMethods) {
                $reflectionMethods = $reflectionClass->getMethods();
                foreach($reflectionMethods as $method) {
                    $methodAttributes = $method->getAttributes();
                    foreach ($methodAttributes as $methodAttribute) {
                        if(in_array($methodAttribute->getName(),$attributesToValidate,true)) {
                            $methodAttribute->newInstance();
                        }
                    }
                    if($withParameters) {
                        $reflectionParameters = $method->getParameters();
                        foreach($reflectionParameters as $parameter) {
                            $parameterAttributes = $parameter->getAttributes();
                            foreach ($parameterAttributes as $parameterAttribute) {
                                if(in_array($parameterAttribute->getName(),$attributesToValidate,true)) {
                                    $parameterAttribute->newInstance();
                                }
                            }
                        }
                    }
                }
            }

            if($withConstants) {
                $reflectionConstants = $reflectionClass->getConstants();
                foreach($reflectionConstants as $constant) {
                    $constantAttributes = $constant->getAttributes();
                    foreach ($constantAttributes as $constantAttribute) {
                        if(in_array($constantAttribute->getName(),$attributesToValidate,true)) {
                            $constantAttribute->newInstance();
                        }
                    }
                }
            }
        }
    }

    public static function validateFunctions(array $attributesToValidate): void {
        $reflectionFunctions = get_defined_functions();
        foreach($reflectionFunctions as $function) {
            $functionAttributes = $function->getAttributes();
            foreach ($functionAttributes as $functionAttribute) {
                if(in_array($functionAttribute->getName(),$attributesToValidate,true)) {
                    $functionAttribute->newInstance();
                }
            }
        }
    }
}