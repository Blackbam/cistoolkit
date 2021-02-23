<?php
namespace CisTools;


class Html
{

    /**
     * Checks if a given string is a valid HTML attribute.
     * @param string $attributeName
     * @return bool: True if the given attribute name is a valid HTML attribute name.
     */
    public static function isAttributeNameValid(string $attributeName): bool {
        return $attributeName === self::sanitizeAttributeName($attributeName);
    }

    /**
     * Sanitizes a string to be a valid HTML5 attribute name.
     * @param string $attributeName
     * @return string
     */
    public static function sanitizeAttributeName(string $attributeName): string {
        return preg_replace("/^[A-Za-z]+[\w\-\:\.]*$/",'',$attributeName);
    }

}