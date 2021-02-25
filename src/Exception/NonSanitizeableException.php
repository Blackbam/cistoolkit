<?php
namespace CisTools\Exception;

/**
 * Class NotImplementedException
 * @package Brainformance\ClassificationStoreApiBundle\Exception
 * @description To be used for not yet implemented functionality
 */
class NonSanitizeableException extends \Exception
{

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        $message = "Non-sanitizeable value: " . $message;

        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
