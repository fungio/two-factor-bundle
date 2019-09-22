<?php

namespace Fungio\TwoFactorBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Fungio\Api\IntegrationUser;

/**
 * Event fires when configuration is completed.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Event
 */
class IntegrationUserConfigurationCompleteEvent extends Event
{
    /**
     * @var IntegrationUser
     */
    private $integrationUser;

    /**
     * @param IntegrationUser $integrationUser
     */
    public function __construct(IntegrationUser $integrationUser)
    {
        $this->integrationUser = $integrationUser;
    }

    /**
     * @return IntegrationUser
     */
    public function getIntegrationUser()
    {
        return $this->integrationUser;
    }
}