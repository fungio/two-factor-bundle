<?php

namespace TwoFAS\TwoFactorBundle\Tests\Event;

use TwoFAS\TwoFactorBundle\Event\ChannelStatusChangedEvent;
use TwoFAS\TwoFactorBundle\Tests\UserEntity;

class ChannelStatusChangedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $user    = new UserEntity();
        $channel = 'totp';
        $event   = new ChannelStatusChangedEvent($user, $channel);

        $this->assertEquals($user, $event->getUser());
        $this->assertEquals($channel, $event->getChannel());
    }
}
