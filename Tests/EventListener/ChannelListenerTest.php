<?php

namespace TwoFAS\TwoFactorBundle\Tests\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use TwoFAS\TwoFactorBundle\Event\ChannelStatusChangedEvent;
use TwoFAS\TwoFactorBundle\EventListener\ChannelListener;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as BaseTokenStorage;
use TwoFAS\TwoFactorBundle\Security\Token\TwoFactorToken;
use TwoFAS\TwoFactorBundle\Storage\TokenStorage;
use TwoFAS\TwoFactorBundle\Tests\UserEntity;

class ChannelListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var ChannelListener
     */
    private $listener;

    public function setUp()
    {
        parent::setUp();

        $this->tokenStorage = new TokenStorage(new BaseTokenStorage());
        $this->listener     = new ChannelListener($this->tokenStorage);
    }

    public function testChannelEnable()
    {
        $user = new UserEntity();
        $user->enableChannel('totp');

        $event = new ChannelStatusChangedEvent($user, 'totp');
        $token = new UsernamePasswordToken('admin', 'adminpass', 'main', ['ROLE_ADMIN']);
        $this->tokenStorage->setToken($token);

        $this->listener->onChannelEnabled($event);

        /** @var TwoFactorToken $actualToken */
        $actualToken = $this->tokenStorage->getToken();
        $this->assertInstanceOf(TwoFactorToken::class, $actualToken);
        $this->assertEquals('admin', $actualToken->getUser());
        $this->assertEquals('adminpass', $actualToken->getCredentials());
        $this->assertEquals('main', $actualToken->getProviderKey());
        $this->assertEquals([new Role('ROLE_ADMIN')], $actualToken->getRoles());
    }

    public function testChannelStatusNotChangedAfterEnableAction()
    {
        $this->setExpectedException(\LogicException::class, 'Call channel enabled event on disabled channel.');
        $user = new UserEntity();
        $event = new ChannelStatusChangedEvent($user, 'totp');
        $this->listener->onChannelEnabled($event);
    }

    public function testNotSupportedToken()
    {
        $this->setExpectedException(\LogicException::class, 'Can\'t login with 2FAS with current token.');
        $user = new UserEntity();
        $user->enableChannel('totp');
        $event = new ChannelStatusChangedEvent($user, 'totp');

        $user  = $this->getMockForAbstractClass(UserInterface::class);
        $user->method('getRoles')->willReturn([]);
        $token = new AnonymousToken('foo', $user);
        $this->tokenStorage->setToken($token);
        $this->listener->onChannelEnabled($event);
    }
}
