<?php

namespace CisTools;

use CisTools\Attribute\Author;
use CisTools\Attribute\ClassInfo;
use JetBrains\PhpStorm\Pure;

/**
 * Class Url
 * @package CisTools
 */
#[ClassInfo(summary: "For URL processing and remote requests")]
#[Author(name: "David Stöckl", url: "https://www.blackbam.at")]
class Url
{

    /**
     * This function checks if a given URL is within the same domain or set of domains given.
     *
     * The root domains must be in a form like example.com, foo.org, what-ever.nz etc.
     *
     * @param $url string: The URL to be checked.
     * @param $domains string|array: The domain or domains to be compared (e.g. example.com or an array ["example.com","example.org"]
     * @return bool: True, if the domain to check is within the same domain.
     */
    public static function checkInRootDomain(string $url, array|string $domains): bool
    {
        if (!is_array($domains)) {
            $domains = [$domains];
        }

        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        $domain = implode('.', array_slice(explode('.', parse_url($url, PHP_URL_HOST)), -2));
        if (in_array($domain, $domains, true)) {
            return true;
        }

        return false;
    }

    /**
     * @return string: The current canonical URL (full URL without query string)
     */
    public static function getCanonical(): string
    {
        return strtok(self::getCurrent(), '?');
    }

    /**
     * @return string: The full request URL including protocol, host, port and query string
     */
    #[Pure]
    public static function getCurrent(): string
    {
        return self::getHostUrl() . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string: The current host URL
     */
    #[Pure]
    public static function getHostUrl(): string
    {
        return "http" . (self::isSecure() ? "s" : "") . "://" . $_SERVER['HTTP_HOST'];
    }

    /**
     * Check if http is secure
     *
     * @return bool: True if https
     */
    public static function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443;
    }

    /**
     * Update or add a GET-Parameter (key value pair) to an already prepared URL (with or without existing query string).
     *
     * @param string $url
     * @param string $key
     * @param $value
     * @return string
     */
    public static function updateParam(string $url, string $key, $value): string
    {
        $parsed = self::parseDeep($url);
        $parsed["query"][$key] = $value;
        return self::buildDeep($parsed);
    }

    /**
     * Better URL parsing: Splits an URL into all its parts
     *
     * @param string $url : The URL to parse
     * @return array|bool: False if not parseable. Otherwise it returns an array containing:
     * scheme (string)
     * host (string)
     * port (if available)
     * path (array, if available)
     * query (array, associative, if available)
     */
    public static function parseDeep(string $url)
    {
        $parsed = parse_url($url);

        if (isset($parsed["path"])) {
            $parsed["path"] = array_filter(explode("/", rtrim(ltrim($parsed["path"], "/"), "/")));
        } else {
            $parsed["path"] = [];
        }

        if (isset($parsed["query"])) {
            parse_str($parsed["query"], $parsed['query']);
        } else {
            $parsed["query"] = [];
        }
        return $parsed;
    }

    /**
     * The exact opposite of parseDeep. Builds an URL from the single components passed as described below:
     *
     * @param $parsed : An array containing all parameters to build the URL from:
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
    public static function buildDeep(array $parsed, bool $trailingslashit = false, $urlencode = true): bool|string
    {
        if (!isset($parsed["host"], $parsed["scheme"])) {
            return false;
        }
        $url = $parsed["scheme"] . "://" . $parsed["host"];

        if (isset($parsed["port"]) && (int)$parsed["port"] > 0) {
            $url .= ":" . (int)$parsed["port"];
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
            $url .= "?" . http_build_query($parsed["query"], null, '&', PHP_QUERY_RFC3986);
        }
        return $url;
    }

    /**
     * Fast way to get the last fragment of an URL (the part between the latest two slashes).
     *
     * @param string $url : The full URL to analyze (with http(s)://).
     * @return string: The last fragment of the URL if it exists, empty string otherwise.
     */
    public static function lastFragment(string $url): string
    {
        $parts = explode("?", $url);
        $noget = rtrim(reset($parts), '/');
        if (substr_count($noget, "/") > 2) {
            $analyze = explode('/', $noget);
            return end($analyze);
        }
        return "";
    }

    /**
     * Get the client IP address. Good for general purposes but not for tracking single users (no hardening against spoofing).
     *
     * @return string: The client IP address
     */
    public static function getSimpleIp(): string
    {
        foreach (
            array(
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR'
            ) as $key
        ) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe

                    if (filter_var(
                            $ip,
                            FILTER_VALIDATE_IP,
                            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                        ) !== false) {
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
     * @return string: If the value was found the value, empty string otherwise
     */
    public static function getPermalinkParam(string $url, string $key): string
    {
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
        return "";
    }


    /*************** CURL helpers ******************/

    /**
     * Performs a curl_exec with debug output to a specified file.
     *
     * @param &$curlHandle : The curl handle resource which was created by curl_init(); to be passed by reference
     * @param string $log_folder_path : The path to log to
     * @param string $log_file_name : The name of the logfile to be written.
     *
     * @return bool|string: The result of the curl request.
     */
    public static function curlExecDebug(
        $curlHandle,
        string $log_folder_path,
        string $log_file_name = "cis-curl-errorlog.txt"
    ): bool|string {
        $fp = self::curlAddDebug($curlHandle, $log_folder_path, $log_file_name);
        $result = curl_exec($curlHandle);
        fclose($fp);
        return $result;
    }

    /**
     * Adds debug output to a curl handle before it is executed.
     *
     * NOTE: You have to close the file handle which is returned. The use of curlExecDebug is recommended for most situations.
     *
     * @param $curlHandle : The curlHandle resource which was created by curl_init(); to be passed by reference
     * @param string $log_folder_path : The path to log to
     * @param string $log_file_name : The name of the logfile to be written.
     *
     * @return mixed: The log file resource.
     */
    public static function curlAddDebug(
        $curlHandle,
        string $log_folder_path,
        string $log_file_name = "cis-curl-errorlog.txt"
    ) {
        if (!is_resource($curlHandle)) {
            trigger_error("Incorrect call to the curlAddDebug function: Expected curl handle.", E_USER_WARNING);
            return false;
        }

        $fp = fopen($log_folder_path . DIRECTORY_SEPARATOR . $log_file_name, 'wb');
        curl_setopt($curlHandle, CURLOPT_VERBOSE, 1);
        curl_setopt($curlHandle, CURLOPT_STDERR, $fp);
        return $fp;
    }

}