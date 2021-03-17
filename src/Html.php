<?php

namespace CisTools;

use CisTools\Exception\NonSanitizeableException;

class Html
{
    public const ATTRIBUTE_NAME_MATCHER = "/[\s\p{Cc}\x{0000}\x{0022}\x{0027}\x{003E}\x{002F}\x{003D}\x{200B}-\x{200D}\x{FDD0}-\x{FDEF}\x{FEFF}]+/u";

    /**
     * Checks if a given string is a valid HTML attribute name.
     * @param string $attributeName
     * @return bool: True if the given attribute name is a valid HTML attribute name.
     */
    public static function isAttributeNameValid(string $attributeName): bool
    {
        return !preg_match(self::ATTRIBUTE_NAME_MATCHER, $attributeName);
    }

    /**
     * Sanitizes a string to be a valid HTML5 attribute name.
     * @param string $attributeName
     * @return string
     * @throws NonSanitizeableException
     */
    public static function sanitizeAttributeName(string $attributeName): string
    {
        $sanitizedAttributeName = preg_replace(self::ATTRIBUTE_NAME_MATCHER, '', $attributeName);
        if (!$sanitizedAttributeName) {
            throw new NonSanitizeableException("Attribute name had no salvageable characters");
        }
        return $sanitizedAttributeName;
    }
}