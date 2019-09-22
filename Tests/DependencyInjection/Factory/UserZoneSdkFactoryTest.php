<?php

namespace Fungio\TwoFactorBundle\Tests\DependencyInjection\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use Fungio\Encryption\Cryptographer;
use Fungio\Encryption\DummyKeyStorage;
use Fungio\TwoFactorBundle\DependencyInjection\Factory\AccountSdkFactory;
use Fungio\TwoFactorBundle\Model\Entity\Option;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepository;
use Fungio\TwoFactorBundle\Storage\OAuthTokenStorage;
use Fungio\TwoFactorBundle\FungioTwoFactorBundle;
use Fungio\Account\OAuth\Interfaces\TokenStorage;
use Fungio\Account\OAuth\TokenType;
use Fungio\Account\Fungio;

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

        $this->assertEquals($this->getInstance(), $this->factory->createInstance());
    }

    /**
     * @return Fungio
     */
    private function getInstance()
    {
        $account = new Fungio($this->tokenStorage, TokenType::symfony(), $this->getHeaders());
        $account->setBaseUrl('http://localhost');

        return $account;
    }

    /**
     * @return array
     */
    private function getHeaders()
    {
        $headers = [
            'Plugin-Version' => FungioTwoFactorBundle::VERSION,
            'Php-Version'    => phpversion(),
            'App-Version'    => Kernel::VERSION,
            'App-Name'       => 'Symfony-FooBar',
            'App-Url'        => 'http://symfony.app'
        ];

        return $headers;
    }
}
