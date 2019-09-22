<?php

namespace Fungio\TwoFactorBundle\Tests\DependencyInjection\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Fungio\Api\Fungio;
use Fungio\Encryption\Cryptographer;
use Fungio\TwoFactorBundle\Cache\EmptyCacheStorage;
use Fungio\TwoFactorBundle\DependencyInjection\Factory\ApiSdkFactory;
use Fungio\TwoFactorBundle\Model\Entity\Option;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepository;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use Fungio\TwoFactorBundle\Storage\EncryptionStorage;

class ApiSdkFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Cryptographer
     */
    private $cryptographer;

    /**
     * @var ApiSdkFactory
     */
    private $factory;

    public function setUp()
    {
        $this->optionRepository = new InMemoryRepository(Option::class, 'id');
        $optionPersister        = new InMemoryObjectPersister($this->optionRepository);
        $this->requestStack     = new RequestStack();
        $this->cryptographer    = Cryptographer::getInstance(new EncryptionStorage(base64_encode('foobar')));

        $this->factory = new ApiSdkFactory($optionPersister, $this->cryptographer, $this->requestStack, new EmptyCacheStorage(), 'Symfony-FooBar', 'http://localhost');
    }

    public function testCreateInstance()
    {
        $login = new Option();
        $login->setName(OptionInterface::LOGIN)->setValue($this->cryptographer->encrypt('foo'));

        $key = new Option();
        $key->setName(OptionInterface::TOKEN)->setValue($this->cryptographer->encrypt('bar'));

        $this->optionRepository->add($login);
        $this->optionRepository->add($key);

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->method('getHttpHost')->willReturn('http://symfony.app');

        $this->requestStack->push($request);

        $this->assertInstanceOf(Fungio::class, $this->factory->createInstance());
    }

    public function testCreateEmptyInstanceIfCannotGetOptions()
    {
        $login = new Option();
        $login->setName(OptionInterface::LOGIN)->setValue('not encrypted');

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->method('getHttpHost')->willReturn('http://symfony.app');

        $this->requestStack->push($request);

        $this->assertInstanceOf(Fungio::class, $this->factory->createInstance());
    }
}
