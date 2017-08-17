<?php

namespace TwoFAS\TwoFactorBundle\Cache;

use \ReflectionClass;

/**
 * Keys used to cache some of TwoFactorBundle data
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Event
 */
final class CacheKeys
{
    const LOGIN      = 'two_fas_two_factor.cache.login';
    const TOKEN      = 'two_fas_two_factor.cache.token';
    const STATUS     = 'two_fas_two_factor.cache.status';
    const CONFIGURED = 'two_fas_two_factor.cache.configured';
    const ENABLED    = 'two_fas_two_factor.cache.enabled';

    /**
     * @return array
     */
    public static function getAvailableKeys()
    {
        $reflection = new ReflectionClass(CacheKeys::class);

        return $reflection->getConstants();
    }
}