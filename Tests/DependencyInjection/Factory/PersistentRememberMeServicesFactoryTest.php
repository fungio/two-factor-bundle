<?php

namespace Fungio\TwoFactorBundle\Tests\DependencyInjection\Factory;

use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as BaseTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Fungio\TwoFactorBundle\DependencyInjection\Factory\PersistentRememberMeServicesFactory;
use Fungio\TwoFactorBundle\Storage\TokenStorage;
use Fungio\TwoFactorBundle\Storage\UserStorageInterface;

class PersistentRememberMeServicesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var PersistentRememberMeServicesFactory
     */
    private $factory;

    public function setUp()
    {
        $this->tokenStorage = new TokenStorage(new BaseTokenStorage());
        $this->factory      = new PersistentRememberMeServicesFactory(
            ['foo'],
            'foo',
            $this->tokenStorage,
            $this->getMockForAbstractClass(TokenProviderInterface::class),
            $this->getMockForAbstractClass(UserStorageInterface::class)
        );
    }

    public function testCreateInstance()
    {
        $token = new UsernamePasswordToken('admin', 'adminpass', '2fas', ['ROLE_ADMIN']);
        $this->tokenStorage->setToken($token);

        $this->assertInstanceOf(RememberMeServicesInterface::class, $this->factory->createInstance());
    }

    public function testCannotCreateIfTokenNotExists()
    {
        $this->setExpectedException(\LogicException::class, 'Token for Two FAS Remember Me Service is not valid');

        $this->factory->createInstance();
    }

    public function testCannotCreateIfTokenIsNotValid()
    {
        $this->setExpectedException(\LogicException::class, 'Token for Two FAS Remember Me Service is not valid');

        $token = new AnonymousToken('foo', 'anonymous');
        $this->tokenStorage->setToken($token);

        $this->factory->createInstance();
    }
}
