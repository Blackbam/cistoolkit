<?php

namespace CisTools\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;

/**
 * Class InvalidParameterException
 * @description Usually thrown in constructors of classes which can not be used without valid constructor parameters
 */
class InvalidParameterException extends Exception
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
        $message = "Invalid parmeter: " . $message;
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
