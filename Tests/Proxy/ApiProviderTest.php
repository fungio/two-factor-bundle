<?php

namespace Fungio\TwoFactorBundle\Tests\Proxy;

use Doctrine\Common\Collections\ArrayCollection;
use Fungio\Api\Code\AcceptedCode;
use Fungio\Api\IntegrationUser;
use Fungio\Api\Fungio;
use Fungio\Encryption\DummyKeyStorage;
use Fungio\TwoFactorBundle\Model\Entity\Authentication;
use Fungio\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use Fungio\TwoFactorBundle\Model\Entity\User;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepository;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use Fungio\TwoFactorBundle\Proxy\ApiProvider;

class ApiProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fungio|\PHPUnit_Framework_MockObject_MockObject
     */
    private $api;

    /**
     * @var InMemoryRepositoryInterface
     */
    private $authenticationRepository;

    /**
     * @var ApiProvider
     */
    private $proxy;

    public function setUp()
    {
        $this->api = $this->getMockBuilder(Fungio::class)->disableOriginalConstructor()->getMock();

        $this->authenticationRepository = new InMemoryRepository(Authentication::class, 'id');
        $authenticationPersister        = new InMemoryObjectPersister($this->authenticationRepository);

        $this->proxy = new ApiProvider($this->api, $authenticationPersister, new DummyKeyStorage());
    }

    public function testRequestAuthViaTotp()
    {
        $fungioAuthentication = new \Fungio\Api\Authentication('13', new \DateTime(), (new \DateTime())->add(new \DateInterval('PT15M')));
        $user                 = new User();
        $this->api->method('requestAuthViaTotp')->willReturn($fungioAuthentication);

        $authentication = $this->proxy->requestAuthViaTotp($user, 1);

        $this->assertInstanceOf(AuthenticationInterface::class, $authentication);
        $this->assertEquals($fungioAuthentication->id(), $authentication->getId());
        $this->assertEquals($fungioAuthentication->createdAt(), $authentication->getCreatedAt());
        $this->assertEquals($fungioAuthentication->validTo(), $authentication->getValidTo());
        $this->assertEquals($user, $authentication->getUser());
    }

    public function testCheckCode()
    {
        $authentications = new ArrayCollection();
        $authentication  = new Authentication();
        $authentication
            ->setId('13')
            ->setCreatedAt(new \DateTime())
            ->setValidTo((new \DateTime())->add(new \DateInterval('PT15M')));

        $authentications->add($authentication);

        $this->api->method('checkCode')->willReturn(new AcceptedCode(['13']));

        $code = $this->proxy->checkCode($authentications, '123456');

        $this->assertInstanceOf(AcceptedCode::class, $code);
    }

    public function testGetIntegrationUserByExternalId()
    {
        $this->api->expects($this->once())->method('getIntegrationUserByExternalId');

        $this->proxy->getIntegrationUserByExternalId(12);
    }

    public function testAddIntegrationUser()
    {
        $this->api->expects($this->once())->method('addIntegrationUser');

        $this->proxy->addIntegrationUser(new IntegrationUser());
    }

    public function testUpdateIntegrationUser()
    {
        $this->api->expects($this->once())->method('updateIntegrationUser');

        $this->proxy->updateIntegrationUser(new IntegrationUser());
    }
}
