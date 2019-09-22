<?php

namespace Fungio\TwoFactorBundle\Tests\Event;

use Fungio\Api\IntegrationUser;
use Fungio\TwoFactorBundle\Event\IntegrationUserConfigurationCompleteEvent;

class IntegrationUserConfigurationCompleteEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $integrationUser = new IntegrationUser();
        $event           = new IntegrationUserConfigurationCompleteEvent($integrationUser);

        $this->assertEquals($integrationUser, $event->getIntegrationUser());
    }
}