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
     * @return string: Lowercase hexits hash
     */
    public static function ripemd320File(string $filepath): string {
        $file = fopen($filepath, 'rb');
        $ctx = hash_init('ripemd320');
        hash_update_stream($ctx, $file);
        $final = hash_final($ctx);
        fclose($file);
        return $final;
    }


    /**
     * Filters a file name (or folder name) by removing invalid system characters and checking the length.
     *
     * For knowing if the original name is valid just compare the result.
     *
     * @param string $filename: The filename to filter
     * @param bool $strict: If false, all characters allowed in a file system are also allowed in the name (e.g. hyphens).
     * @param bool $beautify: If this is true a beautiful filename is created.
     * @return string: The filtered filename.
     */
    public static function filterFilename(string $filename,bool $strict = true, bool $beautify = false) : string {

        if(!$strict) {
            // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
            $filename = str_replace(array_merge(
                array_map('chr', range(0, 31)),
                array('<', '>', ':', '"', '/', '\\', '|', '?', '*')
            ), '', $filename);
            // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $filename= mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
            return $filename;
        } else {
            // sanitize filename
            $filename = preg_replace(
                '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
                '-', $filename);
            // avoids ".", ".." or ".hiddenFiles"
            $filename = ltrim($filename, '.-');
            // optional beautification
            if ($beautify) {
                // reduce consecutive characters
                $filename = preg_replace(array(
                    // "file   name.zip" becomes "file-name.zip"
                    '/ +/',
                    // "file___name.zip" becomes "file-name.zip"
                    '/_+/',
                    // "file---name.zip" becomes "file-name.zip"
                    '/-+/'
                ), '-', $filename);
                $filename = preg_replace(array(
                    // "file--.--.-.--name.zip" becomes "file.name.zip"
                    '/-*\.-*/',
                    // "file...name..zip" becomes "file.name.zip"
                    '/\.{2,}/'
                ), '.', $filename);
                // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
                $filename = mb_strtolower($filename, mb_detect_encoding($filename));
                // ".file-name.-" becomes "file-name"
                $filename = trim($filename, '.-');
            }
            // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
            return $filename;
        }
    }

}