<?php

namespace Fungio\TwoFactorBundle\Tests\Util;

use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\TwoFAS;
use Fungio\TwoFactorBundle\Model\Entity\User;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepository;
use Fungio\TwoFactorBundle\Proxy\ApiProvider;
use Fungio\TwoFactorBundle\Storage\EncryptionStorage;
use Fungio\TwoFactorBundle\Tests\DummyEntity;
use Fungio\TwoFactorBundle\Util\IntegrationUserManager;

class IntegrationUserManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fungio|\PHPUnit_Framework_MockObject_MockObject
     */
    private $api;

    /**
     * @var IntegrationUserManager
     */
    private $manager;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->api = $this
            ->getMockBuilder(TwoFAS::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIntegrationUserByExternalId', 'addIntegrationUser', 'updateIntegrationUser'])
            ->getMock();

        /** @var EncryptionStorage|\PHPUnit_Framework_MockObject_MockObject $storage */
        $storage = $this->getMockBuilder(EncryptionStorage::class)->disableOriginalConstructor()->getMock();

        $apiProvider = new ApiProvider($this->api, new InMemoryObjectPersister(new InMemoryRepository(new DummyEntity(), 'id')), $storage);

        $this->manager = new IntegrationUserManager($apiProvider);
    }

    public function testFindByExternalId()
    {
        $integrationUser = new IntegrationUser();
        $integrationUser
            ->setId('aaa111')
            ->setExternalId(123);

        $this->api->method('getIntegrationUserByExternalId')->willReturn($integrationUser);

        $actualUser = $this->manager->findByExternalId(123);
        $this->assertEquals($integrationUser, $actualUser);
    }

    public function testReturnNullWhenIntegrationUserNotFound()
    {
        $this->api->method('getIntegrationUserByExternalId')->willThrowException(new IntegrationUserNotFoundException());
        $this->assertNull($this->manager->findByExternalId(321));
    }

    public function testCreateUser()
    {
        /** @var User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMockBuilder(User::class)->setMethods(['getId'])->getMock();
        $user->method('getId')->willReturn(123);

        $integrationUser = new IntegrationUser();
        $integrationUser
            ->setId('aaa111')
            ->setExternalId(123)
            ->setMobileSecret('aaa');

        $this->api->method('addIntegrationUser')->willReturn($integrationUser);

        $integrationUser = $this->manager->createUser($user);

        $this->assertEquals(123, $integrationUser->getExternalId());
        $this->assertNotNull($integrationUser->getMobileSecret());
    }

    public function testUpdateUser()
    {
        $this->api->expects($this->once())->method('updateIntegrationUser');

        $user = new IntegrationUser();
        $user->setExternalId(123);

        $this->manager->updateUser($user);
    }
}
