<?php

namespace Fungio\TwoFactorBundle\Tests\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as BaseTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Fungio\Api\Code\AcceptedCode;
use Fungio\Api\Code\RejectedCodeCanRetry;
use Fungio\TwoFactorBundle\Event\CodeCheckEvent;
use Fungio\TwoFactorBundle\EventListener\TokenListener;
use Fungio\TwoFactorBundle\Model\Entity\Authentication;
use Fungio\TwoFactorBundle\Security\Token\TwoFactorToken;
use Fungio\TwoFactorBundle\Storage\TokenStorage;

class TokenListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var TokenListener
     */
    private $listener;

    public function setUp()
    {
        parent::setUp();

        $this->tokenStorage = new TokenStorage(new BaseTokenStorage());
        $this->listener     = new TokenListener($this->tokenStorage);
    }

    public function testSuccessfulAuthentication()
    {
        $event = new CodeCheckEvent(new AcceptedCode([]), new Authentication());
        $token = new UsernamePasswordToken('admin', 'adminpass', 'main', ['ROLE_ADMIN']);
        $this->tokenStorage->setToken($token);

        $this->listener->onAuthenticationSuccess($event);

        /** @var TwoFactorToken $actualToken */
        $actualToken = $this->tokenStorage->getToken();
        $this->assertInstanceOf(TwoFactorToken::class, $actualToken);
        $this->assertEquals('admin', $actualToken->getUser());
        $this->assertEquals('adminpass', $actualToken->getCredentials());
        $this->assertEquals('main', $actualToken->getProviderKey());
        $this->assertEquals([new Role('ROLE_ADMIN')], $actualToken->getRoles());
    }

    public function testNotAcceptedCode()
    {
        $this->setExpectedException(\LogicException::class, 'Call success code event on not accepted code.');
        $event = new CodeCheckEvent(new RejectedCodeCanRetry([]), new Authentication());
        $this->listener->onAuthenticationSuccess($event);
    }

    public function testNotSupportedToken()
    {
        $this->setExpectedException(\LogicException::class, 'Can\'t login with 2FAS with current token.');
        $event = new CodeCheckEvent(new AcceptedCode([]), new Authentication());
        $user  = $this->getMockForAbstractClass(UserInterface::class);
        $user->method('getRoles')->willReturn([]);
        $token = new AnonymousToken('foo', $user);
        $this->tokenStorage->setToken($token);
        $this->listener->onAuthenticationSuccess($event);
    }
}
