<?php

namespace CisTools\Attribute;

use Attribute;
use CisTools\Exception\BadAttributeMetadataException;

/**
 * For declaring an author of a class or method
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE | A)]
class Author
{

    /**
     * Define an author in a class header
     * @param string $name
     * @param string $email
     * @param string $url
     * @throws BadAttributeMetadataException
     */
    public function __construct(string $name, string $email = "", string $url = "")
    {
        if ($name === '') {
            throw new BadAttributeMetadataException("Author name must not be empty.");
        }

        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BadAttributeMetadataException("If using author email it has to be a valid email address.");
        }

        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new BadAttributeMetadataException("If using author url the url has to be valid.");
        }
    }

}