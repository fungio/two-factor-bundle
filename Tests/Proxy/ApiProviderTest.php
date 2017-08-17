<?php

namespace TwoFAS\TwoFactorBundle\Tests\Proxy;

use Doctrine\Common\Collections\ArrayCollection;
use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Encryption\DummyKeyStorage;
use TwoFAS\TwoFactorBundle\Model\Entity\Authentication;
use TwoFAS\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\User;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Proxy\ApiProvider;

class ApiProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwoFAS|\PHPUnit_Framework_MockObject_MockObject
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
        $this->api = $this->getMockBuilder(TwoFAS::class)->disableOriginalConstructor()->getMock();

        $this->authenticationRepository = new InMemoryRepository(Authentication::class, 'id');
        $authenticationPersister        = new InMemoryObjectPersister($this->authenticationRepository);

        $this->proxy = new ApiProvider($this->api, $authenticationPersister, new DummyKeyStorage());
    }

    public function testRequestAuthViaTotp()
    {
        $twoFASAuthentication = new \TwoFAS\Api\Authentication('13', new \DateTime(), (new \DateTime())->add(new \DateInterval('PT15M')));
        $user                 = new User();
        $this->api->method('requestAuthViaTotp')->willReturn($twoFASAuthentication);

        $authentication = $this->proxy->requestAuthViaTotp($user, 1);

        $this->assertInstanceOf(AuthenticationInterface::class, $authentication);
        $this->assertEquals($twoFASAuthentication->id(), $authentication->getId());
        $this->assertEquals($twoFASAuthentication->createdAt(), $authentication->getCreatedAt());
        $this->assertEquals($twoFASAuthentication->validTo(), $authentication->getValidTo());
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
