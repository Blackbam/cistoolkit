<?php


namespace CisTools;

use CisTools\Enum\InternetAddressType;
use CisTools\Exception\InvalidInternetAddressException;
use JetBrains\PhpStorm\Pure;

/**
 * For operations on domains.
 */
class InternetAddress
{

    public const DOMAIN_ALLOWED_REGEX = '/^((?!-))(xn--)?[a-z0-9][a-z0-9-_]{0,61}[a-z0-9]{0,1}\.(xn--)?([a-z0-9\-]{1,61}|[a-z0-9-]{1,30}\.[a-z]{2,})$/';


    /**
     * Check the type of an internet address.
     *
     * @param string $address
     * @return InternetAddressType
     * @throws InvalidInternetAddressException
     */
    public static function getAddressType(string $address): InternetAddressType
    {
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return InternetAddressType::IPv4;
        }

        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return InternetAddressType::IPv6;
        }

        if (preg_match(self::DOMAIN_ALLOWED_REGEX, $address)) {
            return InternetAddressType::DOMAIN;
        }
        throw new InvalidInternetAddressException("The given domain or IP is invalid or not allowed.");
    }

    /**
     * @param string $domain
     * @param array $domains
     * @return array
     */
    private static function getDomainVariationsRecursive(string $domain, array $domains = []): array
    {
        if (str_contains($domain, '.')) {
            $domains[] = $domain;
            $parts = explode('.', $domain);
            unset($parts[0]);

            return self::getDomainVariationsRecursive(implode(".", $parts), $domains);
        }

        return $domains;
    }

    /**
     * Cast to lowercase, trim, strip and remove trailing dot from a domain name.
     *
     * @param string $domain
     * @return string
     */
    #[Pure] public static function sanitizeDomainName(string $domain): string
    {
        return strtolower(rtrim(trim($domain), "."));
    }

    /**
     * Simply removes the first level from an FQDN.
     *
     * @param string $subdomain
     * @return string
     */
    public static function removeFirstLevel(string $subdomain): string
    {
        $parts = explode(".", $subdomain);
        array_shift($parts);
        return implode(".", $parts);
    }

    /**
     * Simply removes http(s) from a domain.
     *
     * @param string $domain
     * @return string
     */
    public static function removeHttpsPrefix(string $domain): string
    {
        return preg_replace("(^https?://)", "", $domain);
    }
}
