<?php

namespace Fungio\TwoFactorBundle\Tests\DependencyInjection\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use TwoFAS\Encryption\Cryptographer;
use TwoFAS\Encryption\DummyKeyStorage;
use Fungio\TwoFactorBundle\DependencyInjection\Factory\AccountSdkFactory;
use Fungio\TwoFactorBundle\Model\Entity\Option;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepository;
use Fungio\TwoFactorBundle\Storage\OAuthTokenStorage;
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
