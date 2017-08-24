<?php

namespace TwoFAS\TwoFactorBundle\Tests\DependencyInjection\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use TwoFAS\Encryption\Cryptographer;
use TwoFAS\Encryption\DummyKeyStorage;
use TwoFAS\TwoFactorBundle\DependencyInjection\Factory\AccountSdkFactory;
use TwoFAS\TwoFactorBundle\Model\Entity\Option;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Storage\OAuthTokenStorage;
use TwoFAS\Account\OAuth\Interfaces\TokenStorage;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\Account\TwoFAS;

class AccountSdkFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var AccountSdkFactory
     */
    private $factory;

    public function setUp()
    {
        $this->requestStack = new RequestStack();
        $this->tokenStorage = new OAuthTokenStorage(new InMemoryObjectPersister(new InMemoryRepository(Option::class, 'id')), Cryptographer::getInstance(new DummyKeyStorage()));
        $this->factory      = new AccountSdkFactory($this->tokenStorage, TokenType::symfony(), $this->requestStack, 'Symfony-FooBar', 'http://localhost');
    }

    public function testCreateInstance()
    {
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->method('getHttpHost')->willReturn('http://symfony.app');

        $this->requestStack->push($request);

        $this->assertInstanceOf(TwoFAS::class, $this->factory->createInstance());
    }
}
