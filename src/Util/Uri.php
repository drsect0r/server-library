<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Util;

use Assert\Assertion;

final class Uri
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * @param string$uri    The URI
     * @param array $params Parameters added to the URI
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function buildURI($uri, $params = [])
    {
        $parse_url = parse_url($uri);
        Assertion::isArray($parse_url, 'The argument is not a valid URI.');

        foreach ($params as $k => $v) {
            if (isset($parse_url[$k])) {
                $parse_url[$k] .= '&'.http_build_query($v);
            } else {
                $parse_url[$k] = http_build_query($v);
            }
        }

        return
            self::buildScheme($parse_url)
            .self::buildUserAuthentication($parse_url)
            .self::buildHost($parse_url)
            .self::buildPort($parse_url)
            .self::buildPath($parse_url)
            .self::buildQuery($parse_url)
            .self::buildFragment($parse_url);
    }

    /**
     * @param array $parse_url The parsed URI
     *
     * @return string
     */
    private static function buildScheme(array $parse_url)
    {
        return isset($parse_url['scheme']) ? $parse_url['scheme'].'://' : '';
    }

    /**
     * @param array $parse_url The parsed URI
     *
     * @return string
     */
    private static function buildUserAuthentication(array $parse_url)
    {
        return (isset($parse_url['user'])) ? $parse_url['user'].((isset($parse_url['pass'])) ? ':'.$parse_url['pass'] : '').'@' : '';
    }

    /**
     * @param array $parse_url The parsed URI
     *
     * @return string
     */
    private static function buildHost(array $parse_url)
    {
        return isset($parse_url['host']) ? $parse_url['host'] : '';
    }

    /**
     * @param array $parse_url The parsed URI
     *
     * @return string
     */
    private static function buildPort(array $parse_url)
    {
        return isset($parse_url['port']) ? ':'.$parse_url['port'] : '';
    }

    /**
     * @param array $parse_url The parsed URI
     *
     * @return string
     */
    private static function buildPath(array $parse_url)
    {
        return isset($parse_url['path']) ? $parse_url['path'] : '/';
    }

    /**
     * @param array $parse_url The parsed URI
     *
     * @return string
     */
    private static function buildQuery(array $parse_url)
    {
        return isset($parse_url['query']) ? '?'.$parse_url['query'] : '';
    }

    /**
     * @param array $parse_url The parsed URI
     *
     * @return string
     */
    private static function buildFragment(array $parse_url)
    {
        return isset($parse_url['fragment']) ? '#'.$parse_url['fragment'] : '';
    }

    /**
     * Checks if the URI matches one of stored URIs.
     *
     * @param string $uri                    The URI to check
     * @param array  $storedUris             A list of stored URIs
     * @param bool   $path_traversal_allowed
     *
     * @return bool
     */
    public static function isRedirectUriAllowed($uri, array $storedUris, $path_traversal_allowed = false)
    {
        // If storedUris is empty, assume invalid
        if (count($storedUris) === 0) {
            return false;
        }

        if ('urn:' === mb_substr($uri,0, 4, '8bit')) {
            if (false === self::checkUrn($uri)) {
                return false;
            }
        } else {
            if (false === self::checkUrl($uri, $path_traversal_allowed)) {
                return false;
            }
        }

        foreach ($storedUris as $storedUri) {
            if (strcasecmp(mb_substr($uri, 0, mb_strlen($storedUri, '8bit'), '8bit'), $storedUri) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $url
     * @param bool   $path_traversal_allowed
     *
     * @return bool
     */
    private static function checkUrl($url, $path_traversal_allowed)
    {
        // If URI is not a valid URI, return false
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);

        // Checks for path traversal (e.g. http://foo.bar/redirect/../bad/url)
        if (isset($parsed['path']) && !$path_traversal_allowed) {
            $path = urldecode($parsed['path']);
            // check for 'path traversal'
            if (preg_match('#/\.\.?(/|$)#', $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $urn
     *
     * @return bool
     */
    private static function checkUrn($urn)
    {
        return 1 === preg_match('/^urn:[a-z0-9][a-z0-9-]{0,31}:[a-z0-9()+,\-.:=@;$_!*\'%\/?#]+$/', $urn);
    }
}
