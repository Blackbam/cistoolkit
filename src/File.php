<?php
namespace CisTools;

/**
 * Class File: For operation on files.
 * @package CisTools
 */
class File {

    /**
     * This function should be used for generating good CHECKSUMS for all types of files.
     *
     * @param string $filepath: The path to the file
     * @return mixed: Binary representation as hex number
     */
    public static function ripemd320File(string $filepath) {
        $file = fopen($filepath, 'rb');
        $ctx = hash_init('ripemd320');
        hash_update_stream($ctx, $file);
        $final = hash_final($ctx);
        fclose($file);
        return $final;
    }

}