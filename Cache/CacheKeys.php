<?php

namespace Fungio\TwoFactorBundle\Cache;

use \ReflectionClass;

/**
 * Keys used to cache some of TwoFactorBundle data
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Event
 */
final class CacheKeys
{
    const LOGIN      = 'fungio_two_factor.cache.login';
    const TOKEN      = 'fungio_two_factor.cache.token';
    const STATUS     = 'fungio_two_factor.cache.status';
    const CONFIGURED = 'fungio_two_factor.cache.configured';
    const ENABLED    = 'fungio_two_factor.cache.enabled';

    /**
     * @return array
     */
    public static function getAvailableKeys()
    {
        $reflection = new ReflectionClass(CacheKeys::class);

        return $reflection->getConstants();
    }
}