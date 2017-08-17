<?php

namespace TwoFAS\TwoFactorBundle\Tests\Event;

use TwoFAS\Api\IntegrationUser;
use TwoFAS\TwoFactorBundle\Event\IntegrationUserConfigurationCompleteEvent;

class IntegrationUserConfigurationCompleteEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $integrationUser = new IntegrationUser();
        $event           = new IntegrationUserConfigurationCompleteEvent($integrationUser);

        $this->assertEquals($integrationUser, $event->getIntegrationUser());
    }
}