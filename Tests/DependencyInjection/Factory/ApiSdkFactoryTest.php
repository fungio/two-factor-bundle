<?php

namespace TwoFAS\TwoFactorBundle\Tests\DependencyInjection\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Encryption\Cryptographer;
use TwoFAS\TwoFactorBundle\Cache\EmptyCacheStorage;
use TwoFAS\TwoFactorBundle\DependencyInjection\Factory\ApiSdkFactory;
use TwoFAS\TwoFactorBundle\Model\Entity\Option;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Storage\EncryptionStorage;

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

        $this->assertInstanceOf(TwoFAS::class, $this->factory->createInstance());
    }

    public function testCreateEmptyInstanceIfCannotGetOptions()
    {
        $login = new Option();
        $login->setName(OptionInterface::LOGIN)->setValue('not encrypted');

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->method('getHttpHost')->willReturn('http://symfony.app');

        $this->requestStack->push($request);

        $this->assertInstanceOf(TwoFAS::class, $this->factory->createInstance());
    }
}
