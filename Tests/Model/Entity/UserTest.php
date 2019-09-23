<?php

namespace Fungio\TwoFactorBundle\Tests\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use TwoFAS\Api\Methods;
use Fungio\TwoFactorBundle\Entity\Authentication;
use Fungio\TwoFactorBundle\Entity\RememberMeToken;
use Fungio\TwoFactorBundle\Model\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $user = $this->getUser();

        $this->assertNull($user->getId());
        $this->assertNull($user->getUsername());
        $this->assertInstanceOf(ArrayCollection::class, $user->getAuthentications());
        $this->assertInstanceOf(ArrayCollection::class, $user->getTokens());
        $this->assertInternalType('array', $user->getChannels());
    }

    public function testGetUserName()
    {
        $user = $this->getUser();
        $user->setUsername('tom');

        $this->assertEquals('tom', $user->getUsername());
    }

    public function testGetAuthentication()
    {
        $user = $this->getUser();
        $user->addAuthentication(new Authentication());

        $this->assertInstanceOf(Authentication::class, $user->getAuthentications()->first());
    }

    public function testGetToken()
    {
        $user = $this->getUser();
        $user->addToken(new RememberMeToken());

        $this->assertInstanceOf(RememberMeToken::class, $user->getTokens()->first());
    }

    public function testSetChannels()
    {
        $user = $this->getUser();

        $user->setChannels([
            'totp' => true,
            'sms' => true,
            'call' => false,
            'email' => false
        ]);

        $this->assertEquals([
            'totp' => true,
            'sms' => true,
            'call' => false,
            'email' => false
        ], $user->getChannels());
    }

    public function testAllChannelsDisabled()
    {
        $user = $this->getUser();

        $this->assertFalse($user->isAnyChannelEnabled());
    }

    public function testChannelDisabled()
    {
        $user = $this->getUser();

        $this->assertFalse($user->isChannelEnabled(Methods::TOTP));
    }

    public function testAnyChannelEnabled()
    {
        $user = $this->getUser();
        $user->enableChannel(Methods::TOTP);

        $this->assertTrue($user->isAnyChannelEnabled());
    }

    public function testChannelDisabledWhenNotExists()
    {
        $user = $this->getUser();

        $this->assertFalse($user->isChannelEnabled('foo'));
    }

    public function testEnableChannel()
    {
        $user = $this->getUser();
        $user->enableChannel(Methods::TOTP);
        $this->assertTrue($user->isChannelEnabled(Methods::TOTP));
    }

    public function testDisableChannel()
    {
        $user = $this->getUser();
        $user->enableChannel(Methods::TOTP);
        $this->assertTrue($user->isChannelEnabled(Methods::TOTP));

        $user->disableChannel(Methods::TOTP);
        $this->assertFalse($user->isChannelEnabled(Methods::TOTP));
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return new User();
    }
}
