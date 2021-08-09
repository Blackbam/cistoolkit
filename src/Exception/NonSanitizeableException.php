<?php

namespace CisTools\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;

/**
 * Class NotImplementedException
 * @description To be used for not yet implemented functionality
 */
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
