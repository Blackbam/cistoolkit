<?php

namespace CisTools;

class Encoding
{

    /**
     * Like json_encode, but the characters are fixed in case of a JSON_ERROR_UTF8 .
     *
     * CAUTION: This will only make sure the json_encode works, no guarantee for unwanted conversion.
     *
     * @param mixed $dataToEncode
     * @return string
     * @throws \JsonException
     */
    public static function jsonEncodeEncodingFix(mixed $dataToEncode): string
    {
        try {
            return json_encode($dataToEncode, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            if (json_last_error() === JSON_ERROR_UTF8) {
                return json_encode(self::forceUtf8NoMatterWhat($dataToEncode), JSON_THROW_ON_ERROR);
            }
            throw $e;
        }
    }

    /**
     * Forces UTF-8 on mixed data. Will solve encoding problems, but no guarantee it will preserve the original text correctly.
     *
     * @param mixed $dataToEncode : Any type of data, if strings in it they are converted to UTF-8.
     * @return array|string
     */
    public static function forceUtf8NoMatterWhat(mixed $dataToEncode): mixed
    {
        if (is_string($dataToEncode)) {
            return mb_convert_encoding($dataToEncode, 'UTF-8', 'UTF-8');
        }
        if (is_array($dataToEncode)) {
            $ret = [];
            foreach ($dataToEncode as $i => $d) {
                $ret[$i] = self::forceUtf8NoMatterWhat($d);
            }
            return $ret;
        }
        if (is_object($dataToEncode)) {
            foreach ($dataToEncode as $i => $d) {
                $dataToEncode->$i = self::forceUtf8NoMatterWhat($d);
            }
            return $dataToEncode;
        }
        return $dataToEncode;
    }
}