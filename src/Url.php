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
     * @return The host URL
     */
    public static function getHostUrl(): string {
        return "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'];
    }

    /**
     * @return string: The canonical URL (full URL without query string)
     */
    public static function getCanonical(): string {
        return strtok(self::getCurrent(), '?');
    }

    /**
     * @return The full request URL including protocol, host, port and query string
     */
    public static function getCurrent(): string {
        return self::getHostUrl() . $_SERVER['REQUEST_URI'];
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
     * Better URL parsing: Splits an URL into all its parts
     *
     * @param string $url : The URL to parse
     * @return array|false: False if not parseable. Otherwise it returns an array containing:
     * scheme (string)
     * host (string)
     * port (if available)
     * path (array, if available)
     * query (array, associative, if available)
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
     * The exact opposite of parseDeep. Builds an URL from the single components passed as described below:
     *
     * @param $parsed : An array containging all parameters to build the URL from:
     * scheme (mandatory, string)
     * host (mandatory, string)
     * port (optional, integer)
     * path (optional, array)
     * query (optional, associative array)
     *
     * @param bool $trailingslashit : Shall the URL have a trailing slash?
     * @param bool $urlencode : Shall all parameters (path & query) be encoded? (recommended, otherwise you can build invalid URLs)
     * @return bool|string: The URL or false if invalid parameters were passed.
     */
    public static function buildDeep(array $parsed, $trailingslashit = false, $urlencode = true) {
        if (!isset($parsed["host"]) || !isset($parsed["scheme"])) {
            return false;
        }
        $url = $parsed["scheme"] . "://" . $parsed["host"];

        if (isset($parsed["port"]) && intval($parsed["port"]) > 0) {
            $url .= ":" . intval($parsed["port"]);
        }

        if (isset($parsed["path"]) && !empty($parsed["path"])) {
            if ($urlencode) {
                $parsed["path"] = array_map("urlencode", $parsed["path"]);
            }
            $url .= "/" . implode("/", $parsed["path"]);
        }

        if ($trailingslashit) {
            $url .= "/";
        }

        if (isset($parsed["query"]) && !empty($parsed["query"])) {
            $f = true;
            foreach ($parsed["query"] as $key => $value) {
                if ($f == true) {
                    $url .= "?";
                    $f = false;
                } else {
                    $url .= "&";
                }
                if ($urlencode) {
                    $url .= urlencode($key) . "=" . urlencode($value);
                } else {
                    $url .= $key . "=" . $value;
                }
            }
        }
        return $url;
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

    /**
     * Get a certain fragment of an URL which comes after another fragment.
     *
     * e.g. https://www.example.com/member/sample-guy/achievments/2017
     *
     * getPermalinkParam($url,"member") would return "sample-guy"
     * getPermalinkParam($url,"achievements") would return "2017"
     *
     * @param string $url : The URL to parse
     * @param string $key : The key before the value.
     * @return string: If the value was found the value, null otherwise.
     */
    public static function getPermalinkParam(string $url, string $key) {
        $parsed = self::parseDeep($url);
        if (!empty($parsed["path"])) {
            $path = $parsed["path"];
            reset($path);
            while ($current = current($path)) {
                $next = next($path);
                if ($current === $key && false !== $next) {
                    return $next;
                }
            }
        }
        return null;
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