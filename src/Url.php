<?php

namespace CisTools;

/**
 * Class Url: For URL processing and remote requests
 * @package CisTools
 */
class Url {

    /**
     * This function checks if a given URL is within the same domain or set of domains given.
     *
     * The root domains must be in a form like example.com, foo.org, what-ever.nz etc.
     *
     * @param $url string: The URL to be checked.
     * @param $domains string/array: The domain or domains to be compared (e.g. example.com or an array ["example.com","example.org"]
     * @return bool: True, if the domain to check is within the same domain.
     */
    public static function checkInRootDomain(string $url, $domains): bool {
        if (!is_array($domains)) $domains = [$domains];

        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        $domain = implode('.', array_slice(explode('.', parse_url($url, PHP_URL_HOST)), -2));
        if (in_array($domain, $domains)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * @return The full request URL including protocol, host, port and query string
     */
    public static function getCurrent(): string {
        return "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Similar to parse_str. Does not need a second parameter - the result array is generated.
     *
     * Proper parsing of query strings allowing duplicate values (http://php.net/manual/en/function.parse-str.php#76792)
     *
     * @param $str
     * @return array
     */
    public static function parseStr(string $str): array {
        # result array
        $arr = [];

        # split on outer delimiter
        $pairs = explode('&', $str);

        # loop through each pair
        foreach ($pairs as $i) {
            # split into name and value (array_pad to prevent notice)
            list($name, $value) = array_pad(explode('=', $i, 2), 2, null);

            # if name already exists
            if (isset($arr[$name])) {
                # stick multiple values into an array
                if (is_array($arr[$name])) {
                    $arr[$name][] = $value;
                } else {
                    $arr[$name] = [$arr[$name], $value];
                }
            } # otherwise, simply stick it in a scalar
            else {
                $arr[$name] = $value;
            }
        }

        # return result array
        return $arr;
    }


    /**
     * Better URL parsing
     *
     * @param string $url
     * @return array|false
     */
    public static function parseDeep(string $url) {
        $parsed = parse_url($url);

        if (isset($parsed["path"])) {
            $parsed["path"] = array_filter(explode("/", rtrim(ltrim($parsed["path"], "/"), "/")));
        } else {
            $parsed["path"] = [];
        }

        if (isset($parsed["query"])) {
            $parsed["query"] = self::parseStr($parsed["query"]);
        } else {
            $parsed["query"] = [];
        }
        return $parsed;
    }


    /**
     * Add a GET-Parameter (key value pair) to an already prepared URL (with or without existing query string).
     *
     * @param $url : The URL to append the key value pair
     * @param $key : The key
     * @param $value : The value
     * @return string: The URL with the extended query string
     */
    public static function addParam(string $url, string $key, $value): string {
        return $url . ((strpos($url, '?') !== false) ? "&" : "?") . urlencode($key) . "=" . urlencode($value);
    }

    /**
     * Fast way to get the last fragment of an URL (the part between the latest two slashes).
     *
     * @param $url : The full URL to analyze (with http(s)://).
     * @return string: The last fragment of the URL if it exists, empty string otherwise.
     */
    public static function lastFragment(string $url): string {
        $noget = @rtrim(reset(explode("?", $url)), '/');
        if (substr_count($noget, "/") > 2) {
            return @end(explode('/', $noget));
        }
        return "";
    }

    /**
     * Get the client IP address. Good for general purposes but not for tracking single users (no hardening against spoofing).
     *
     * @return string: The client IP address
     */
    public static function getSimpleIp(): string {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return "";
    }


    /*************** CURL helpers ******************/

    /**
     * Performs a curl_exec with debug output to a specified file.
     *
     * @param &$curlhandle : The curlhandle resource which was created by curl_init(); to be passed by reference
     * @param null $log_location : Log location folder. If empty the wp-content directory will be used.
     * @param string $log_file_name : The name of the logfile to be written.
     *
     * @return mixed: The result of the curl request.
     */
    public static function curlExecDebug(&$curlhandle, string $log_folder_path, string $log_file_name = "cis-curl-errorlog.txt") {
        $fp = self::curlAddDebug($curlhandle, $log_folder_path, $log_file_name);
        $result = curl_exec($curlhandle);
        fclose($fp);
        return $result;
    }

    /**
     * Adds debug output to a curl handle before it is executed.
     *
     * NOTE: You have to close the file handle which is returned. The use of curlExecDebug is recommended for most situations.
     *
     * @param &$curlhandle : The curlhandle resource which was created by curl_init(); to be passed by reference
     * @param null $log_location : Log location folder. If empty the wp-content directory will be used.
     * @param string $log_file_name : The name of the logfile to be written.
     *
     * @return mixed: The log file resource.
     */
    public static function curlAddDebug(&$curlhandle, string $log_folder_path, string $log_file_name = "cis-curl-errorlog.txt") {

        if (!is_resource($curlhandle)) {
            trigger_error("Incorrect call to the curlAddDebug function: Expected curl handle.", E_USER_WARNING);
            return;
        }

        $fp = fopen($log_folder_path . DIRECTORY_SEPARATOR . $log_file_name, 'w');
        curl_setopt($curlhandle, CURLOPT_VERBOSE, 1);
        curl_setopt($curlhandle, CURLOPT_STDERR, $fp);

        return $fp;
    }

}