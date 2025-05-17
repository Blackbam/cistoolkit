<?php

namespace CisTools;

use CisTools\Exception\InvalidArgumentException;

/**
 * Implements simple double-submit cookie pattern protection.
 *
 * Use like: if (!CSRFProtector::isValidToken((string) $cpt)) - $cpt must be within a header (or, less good, in a form field).
 */
class CSRFProtector
{

    public const COOKIE_NAME = "_cpt";

    /**
     * @return void
     * @throws InvalidArgumentException: If there is an issue with generating the random string, not very likely to happen.
     */
    public static function setCsrfCookie(): void
    {
        setcookie(
            self::COOKIE_NAME,
            StringGenerator::generateSecureRandomString(40, StringGenerator::LOWERCASE | StringGenerator::UPPERCASE | StringGenerator::NUMBERS),
            [
                'expires' => 0,
                'path' => '/',
                'domain' => parse_url($_SERVER['PHP_SELF'], PHP_URL_HOST),
                'secure' => true,
                'httponly' => false,
                'samesite' => 'Strict',
            ]
        );

    }

    public static function isValidToken(string $token): bool
    {
        return $token === $_COOKIE[self::COOKIE_NAME];
    }
}
