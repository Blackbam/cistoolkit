<?php

namespace CisTools;

use CisTools\Exception\InvalidArgumentException;

/**
 * Implements simple double-submit cookie pattern protection.
 */
class CSRFProtector
{

    public const COOKIE_NAME = "_cpt";
    private static ?string $currentCookie = null;

    /**
     * @return void
     * @throws InvalidArgumentException : If there is an issue with generating the random string, not very likely to happen.
     */
    public static function setCsrfCookie(): void
    {
        $randomString = StringGenerator::generateSecureRandomString(40, StringGenerator::LOWERCASE | StringGenerator::UPPERCASE | StringGenerator::NUMBERS);
        setcookie(
            self::COOKIE_NAME,
            $randomString,
            [
                'expires' => 0,
                'path' => '/',
                'domain' => parse_url($_SERVER['PHP_SELF'], PHP_URL_HOST),
                'secure' => true,
                'httponly' => false,
                'samesite' => 'Strict',
            ]
        );
        self::$currentCookie = $randomString;
    }

    /**
     * @return string|null
     */
    public static function getCurrentCsrfCookie(): ?string
    {
        return self::$currentCookie;
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getOrSetCurrentCsrfCookie(): string
    {
        if(is_null(self::$currentCookie)) {
            self::setCsrfCookie();
        }
        return self::$currentCookie;
    }

    /**
     * Compare this in the answer request.
     *
     * @param string $token
     * @return bool
     */
    public static function isValidToken(string $token): bool
    {
        return $token === $_COOKIE[self::COOKIE_NAME];
    }
}
