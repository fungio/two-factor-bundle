<?php

namespace TwoFAS\TwoFactorBundle\Tests\BrowserKit;

use Symfony\Component\BrowserKit\CookieJar as BaseCookieJar;

/**
 * This class fix a bug with cookie with array notation in the cookie name:
 *
 * Result in http foundation request:
 * [
 *      TWOFAS_REMEMBERME => [1 => 'foo']
 * ]
 *
 * Result in browser kit request:
 *
 * [
 *      TWOFAS_REMEMBERME[1] => 'foo'
 * ]
 *
 * This is only workaround!
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package BrowserKit
 */
class CookieJar extends BaseCookieJar
{
    /**
     * @inheritdoc
     */
    public function allValues($uri, $returnsRawValue = false)
    {
        $cookies       = parent::allValues($uri, $returnsRawValue);
        $parsedCookies = [];

        foreach ($cookies as $name => $cookie) {
            if (false === strpos($name, '[')) {
                $parsedCookies[$name] = $cookie;
            } else {
                $start   = strpos($name, '[');
                $end     = strpos($name, ']');
                $key     = substr($name, ($start + 1), ($end - $start - 1));
                $newName = substr($name, 0, $start);

                if (!array_key_exists($newName, $parsedCookies)) {
                    $parsedCookies[$newName] = [];
                }

                $parsedCookies[$newName][$key] = $cookie;
            }
        }

        return $parsedCookies;
    }
}
