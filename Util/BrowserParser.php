<?php

namespace Fungio\TwoFactorBundle\Util;

use WhichBrowser\Parser;

/**
 * Extends with get client IP.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Util
 */
class BrowserParser extends Parser
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }
}