<?php

namespace TwoFAS\TwoFactorBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use TwoFAS\Api\Code\Code;

/**
 * Event fires when 2FAS Code is used (check code).
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Event
 */
class CodeCheckEvent extends Event
{
    /**
     * @var Code
     */
    private $code;

    /**
     * @param Code $code
     */
    public function __construct(Code $code)
    {
        $this->code = $code;
    }

    /**
     * @return Code
     */
    public function getCode()
    {
        return $this->code;
    }
}