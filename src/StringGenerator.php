<?php

namespace CisTools;

use CisTools\Exception\InvalidParameterException;
use Exception;
use JetBrains\PhpStorm\ArrayShape;

/**
 * A random string generator which can e.g. be used for generating secure passwords.
 *
 * Class StringGenerator
 * @package CisTools
 */
class StringGenerator
{

    // Character types
    public const LOWERCASE = 0x1;
    public const UPPERCASE = 0x2;
    public const NUMBERS = 0x4;
    public const SPECIAL = 0x8;
    public const ALL = 0xF;

    // Character sets alnum
    public const CHARSET_LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    public const CHARSET_UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const CHARSET_NUMBERS = '0123456789';

    // Other character sets
    public const CHARSET_SPECIAL_PASSWORD = ' !"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~'; // OWASP password special characters (https://owasp.org/www-community/password-special-characters)
    public const CHARSET_SPECIAL_URL = "$-_'.+!*(),"; // special characters allowed in URL

    /**
     * @param int $length
     * @param int $flags
     * @param string $allowedNonAlnumChars
     * @param array $minRequiredByFlag
     * @return string
     * @throws InvalidParameterException
     * @throws Exception
     */
    public static function generateSecureRandomString(
        int $length = 10,
        int $flags = self::ALL,
        string $allowedNonAlnumChars = "",
        #[ArrayShape([
            self::LOWERCASE => 'int',
            self::UPPERCASE => 'int',
            self::NUMBERS => 'int',
            self::SPECIAL => 'int'
        ])] array $minRequiredByFlag = []
    ): string {
        $length = Math::rangeInt($length, 0);

        foreach ($minRequiredByFlag as $key => $mr) {
            if (!($flags & $key)) {
                unset($minRequiredByFlag[$key]);
            }
        }

        if (array_sum($minRequiredByFlag) > $length) {
            throw new InvalidParameterException(
                "String generator failed to generate random string: Your required types of characters are higher than the required length of the string."
            );
        }

        $randStr = "";
        foreach ($minRequiredByFlag as $key => $mr) {
            for ($i = 0; $i < $mr; $i++) {
                $randStr .= self::getRandomCharacter($key, $allowedNonAlnumChars);
            }
        }

        for ($i = strlen($randStr); $i < $length; $i++) {
            $randStr .= self::getRandomCharacter($flags, $allowedNonAlnumChars);
        }
        return str_shuffle($randStr);
    }

    ### Specific functions ###

    /**
     * @param int $flags
     * @param string $allowedNonAlnumChars
     * @return string
     * @throws Exception
     */
    public static function getRandomCharacter(int $flags = self::ALL, string $allowedNonAlnumChars = ""): string
    {
        $set = '';
        if ($flags & self::LOWERCASE) {
            $set .= self::CHARSET_LOWERCASE;
        }
        if ($flags & self::UPPERCASE) {
            $set .= self::CHARSET_UPPERCASE;
        }
        if ($flags & self::NUMBERS) {
            $set .= self::CHARSET_NUMBERS;
        }
        if ($flags & self::SPECIAL) {
            $set .= preg_replace('/[a-zA-Z0-9]+/Ui', '', $allowedNonAlnumChars);
        }
        $set = count_chars($set, 3);
        if ($set === '') {
            throw new InvalidParameterException("Can not get random character for an empty set of characters.");
        }
        $max = strlen($set) - 1;
        return $set[random_int(0, $max)];
    }

    /**
     * @param int $length
     * @return string
     * @throws InvalidParameterException
     */
    public static function getSecureRandomPassword(int $length = 10): string
    {
        return self::generateSecureRandomString($length, self::ALL, self::CHARSET_SPECIAL_PASSWORD);
    }

    /**
     * Returns a random URL-valid string (with any common possible characters mixed).
     * @param int $length
     * @return string
     * @throws InvalidParameterException
     */
    public static function getRandomUrlValidString(int $length = 8): string
    {
        return self::generateSecureRandomString($length, self::ALL, self::CHARSET_SPECIAL_URL);
    }
}