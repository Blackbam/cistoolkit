<?php

namespace CisTools\Exception;

use CisTools\Attribute\Author;
use CisTools\Attribute\ClassInfo;
use Exception;
use JetBrains\PhpStorm\Pure;

/**
 * Class BadAttributeMetadataException
 * @package CisTools\Exception
 */
#[ClassInfo(summary: "For issues with bad attribute metadata")]
#[Author(name: "David Stöckl", url: "https://www.blackbam.at")]
class BadAttributeMetadataException extends Exception
{

    /**
     * BadAttributeMetadataException constructor.
     * @param $message
     * @param int $code
     * @param Exception|null $previous
     */
    #[Pure]
    public function __construct(
        $message,
        $code = 0,
        Exception $previous = null
    ) {
        $message = "You have declared an PHP Attribute (annotation) in a class but the parameters do not match the required format: " . $message;
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
