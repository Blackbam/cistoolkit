<?php

namespace CisTools\Attribute;

use Attribute;
use CisTools\Exception\BadAttributeMetadataException;

/**
 * For defining a proper class header information
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ClassInfo
{

    /**
     * Define an author in a class header
     * @param string $summary
     * @param string $description
     * @throws BadAttributeMetadataException
     */
    public function __construct(string $summary, string $description = "", string $referenceUrl = "")
    {
        if (!$summary) {
            throw new BadAttributeMetadataException("The summary is mandatory for the ClassInfo Attribute.");
        }

        if($referenceUrl && !filter_var($referenceUrl,FILTER_VALIDATE_URL)) {
            throw new BadAttributeMetadataException("If using a reference in the ClassInfo attribute it has to be a URL.");
        }
    }
}