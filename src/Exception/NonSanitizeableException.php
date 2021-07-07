<?php

namespace CisTools\Exception;

use CisTools\Attribute\Author;
use CisTools\Attribute\ClassInfo;
use Exception;
use JetBrains\PhpStorm\Pure;

/**
 * Class NonSanitizeableException
 * @package CisTools\Exception
 */
#[ClassInfo(summary: "Thrown is something can not be sanitized")]
#[Author(name: "David Stöckl", url: "https://www.blackbam.at")]
class NonSanitizeableException extends Exception
{

    /**
     * NonSanitizeableException constructor.
     * @inheritdoc
     */
    #[Pure]
    public function __construct(
        $message,
        $code = 0,
        Exception $previous = null
    ) {
        $message = "Non-sanitizeable value: " . $message;
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
