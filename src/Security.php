<?php

namespace CisTools;

use CisTools\Exception\InvalidParameterException;

class Security
{

    /**
     * Encrypts / decrypts a value using symmetric encryption. You could e.g. share secret parameters along multiple requests.
     *
     * @param string $secretKey : A secure secret key
     * @param string $secretIv : A secure secret IV: You can provide an empty string, but then identical messages always look the same.
     * @param string $subject : A subject to encrypt / decrypt
     * @param bool $decrypt : Set to true if you want the value to be decrypted instead of encrypted.
     * @return string|false: Encrypted / decrypted subject
     * @throws InvalidParameterException
     */
    public static function symmetricCipher(string $secretKey, string $secretIv, string $subject, bool $decrypt = false): string|false
    {
        if (strlen($secretKey) < 10 || strlen($secretIv) < 6) {
            throw new InvalidParameterException("Your secret parameters for the cipher have to be longer.");
        }

        $key = hash('sha256', $secretKey);
        $iv = substr(hash('sha256', $secretIv), 0, 16);
        
        // Check whether encryption or decryption
        if (!$decrypt) {
            // We are encrypting
            $output = base64_encode(openssl_encrypt($subject, "AES-256-CBC", $key, 0, $iv));
        } else {
            // We are decrypting
            $output = openssl_decrypt(base64_decode($subject), "AES-256-CBC", $key, 0, $iv);
        }
        // Return the final value
        return $output;
    }

}