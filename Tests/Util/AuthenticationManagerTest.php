<?php

namespace TwoFAS\TwoFactorBundle\Tests\Util;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TwoFAS\Api\Authentication as TwoFASAuthentication;
use TwoFAS\Api\Methods;
use TwoFAS\Api\TotpSecretGenerator;
use TwoFAS\Api\TwoFAS;
use TwoFAS\TwoFactorBundle\Model\Entity\Authentication;
use TwoFAS\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\User;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Proxy\ApiProvider;
use TwoFAS\TwoFactorBundle\Storage\EncryptionStorage;
use TwoFAS\TwoFactorBundle\Util\AuthenticationManager;

class AuthenticationManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthenticationManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticationManager;

    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var TwoFAS|\PHPUnit_Framework_MockObject_MockObject
     */
    private $api;

    /**
     * @var InMemoryObjectPersister
     */
    private $authenticationPersister;

    /**
     * @var InMemoryRepositoryInterface
     */
    private $authenticationRepository;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->api = $this
            ->getMockBuilder(TwoFAS::class)
            ->disableOriginalConstructor()
            ->setMethods(['requestAuth', 'requestAuthViaTotp'])
            ->getMock();

        $this->authenticationRepository = new InMemoryRepository(Authentication::class, 'id');
        $this->authenticationPersister  = new InMemoryObjectPersister($this->authenticationRepository);

        $this->objectManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->getMockForAbstractClass();

        $apiProvider = new ApiProvider(
            $this->api,
            $this->authenticationPersister,
            $this->getMockBuilder(EncryptionStorage::class)->disableOriginalConstructor()->getMock()
        );

        $this->authenticationManager = new AuthenticationManager(
            $apiProvider,
            $this->authenticationPersister,
            $this->objectManager,
            $this->getMockForAbstractClass(EventDispatcherInterface::class),
            5
        );

    }

    public function testUserHasNotAnyAuthentications()
    {
        $user = $this->getUser();
        $this->objectManager->method('merge')->willReturn($user);

        $authentication = $this->authenticationManager->getOpenAuthentications($user, Methods::TOTP);

        $this->assertEquals(0, $authentication->count());
    }

    public function testGetOpenAuthentication()
    {
        $authentication = $this->getAuthentication();
        $authentication
            ->setId(1)
            ->setType(Methods::TOTP)
            ->setCreatedAt(new \DateTime())
            ->setValidTo((new \DateTime())->add(new \DateInterval('PT15M')))
            ->setVerified(false);

        $user = $this->getUser();
        $user->addAuthentication($authentication);
        $authentication->setUser($user);

        $this->objectManager->method('merge')->willReturn($user);

        $actualAuthentications = $this->authenticationManager->getOpenAuthentications($user, Methods::TOTP);

        $this->assertContains($authentication, $actualAuthentications->toArray());
    }

    public function testNoOpenAuthenticationWhenExpired()
    {
        $authentication = $this->getAuthentication();
        $authentication
            ->setId(1)
            ->setCreatedAt((new \DateTime())->sub(new \DateInterval('PT30M')))
            ->setValidTo((new \DateTime())->sub(new \DateInterval('PT15M')))
            ->setVerified(false);

        $user = $this->getUser();
        $user->addAuthentication($authentication);
        $authentication->setUser($user);

        $this->objectManager->method('merge')->willReturn($user);

        $actualAuthentications = $this->authenticationManager->getOpenAuthentications($user, Methods::TOTP);

        $this->assertEquals(0, $actualAuthentications->count());
    }

    public function testNoOpenAuthenticationWhenVerified()
    {
        $authentication = $this->getAuthentication();
        $authentication
            ->setId(1)
            ->setCreatedAt((new \DateTime())->sub(new \DateInterval('PT30M')))
            ->setValidTo((new \DateTime())->add(new \DateInterval('PT15M')))
            ->setVerified(true);

        $user = $this->getUser();
        $user->addAuthentication($authentication);
        $authentication->setUser($user);

        $this->objectManager->method('merge')->willReturn($user);

        $actualAuthentications = $this->authenticationManager->getOpenAuthentications($user, Methods::TOTP);

        $this->assertEquals(0, $actualAuthentications->count());
    }

    public function testGetFirstOpenAuthentication()
    {
        $authentication = $this->getAuthentication();
        $authentication
            ->setId(1)
            ->setType(Methods::TOTP)
            ->setCreatedAt((new \DateTime())->sub(new \DateInterval('PT30M')))
            ->setValidTo((new \DateTime())->sub(new \DateInterval('PT15M')));

        $authentication2 = $this->getAuthentication();
        $authentication2
            ->setId(2)
            ->setType(Methods::TOTP)
            ->setCreatedAt(new \DateTime())
            ->setValidTo((new \DateTime())->add(new \DateInterval('PT15M')))
            ->setVerified(false);

        $authentication3 = $this->getAuthentication();
        $authentication3
            ->setId(3)
            ->setType(Methods::TOTP)
            ->setCreatedAt(new \DateTime())
            ->setValidTo((new \DateTime())->add(new \DateInterval('PT15M')))
            ->setVerified(false);

        $user = $this->getUser();
        $user->addAuthentication($authentication);
        $user->addAuthentication($authentication2);
        $user->addAuthentication($authentication3);
        $authentication->setUser($user);
        $authentication2->setUser($user);
        $authentication3->setUser($user);

        $this->objectManager->method('merge')->willReturn($user);

        $actualAuthentications = $this->authenticationManager->getOpenAuthentications($user, Methods::TOTP);

        $this->assertContains($authentication2, $actualAuthentications->toArray());
    }

    public function testOpenTotpAuthentication()
    {
        $authentication = $this->getAuthentication();
        $authentication->setId(1);
        $this->api->method('requestAuthViaTotp')->willReturn($this->getTwoFASAuthentication());
        $this->authenticationRepository->add($authentication);
        $authentication = $this->authenticationManager->openTotpAuthentication($this->getUser(), TotpSecretGenerator::generate());

        $this->assertInstanceOf(AuthenticationInterface::class, $authentication);
        $this->assertEquals(1, $authentication->getId());
        $this->assertInstanceOf(UserInterface::class, $authentication->getUser());
        $this->assertInstanceOf(\DateTime::class, $authentication->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $authentication->getValidTo());
    }

    public function testCloseAuthentications()
    {
        $authentication = $this->getAuthentication();
        $authentication->setId('1');
        $this->authenticationRepository->add($authentication);

        $this->authenticationManager->closeAuthentications([$authentication->getId()]);

        $this->assertTrue($authentication->isVerified());
    }

    public function testBlockAuthentications()
    {
        $validTo        = (new \DateTime())->add(new \DateInterval('PT15M'));
        $authentication = $this->getAuthentication();
        $authentication
            ->setId('1')
            ->setValidTo($validTo);

        $this->authenticationRepository->add($authentication);
        $this->authenticationManager->blockAuthentications([$authentication->getId()]);

        $this->assertTrue($authentication->isBlocked());
        $this->assertNotEquals($validTo, $authentication->getValidTo());
    }

    public function testNotUpdateBlockedAuthentications()
    {
        $validTo        = (new \DateTime())->add(new \DateInterval('PT15M'));
        $authentication = $this->getAuthentication();
        $authentication
            ->setId('1')
            ->setBlocked(true)
            ->setValidTo($validTo);

        $this->authenticationRepository->add($authentication);
        $this->authenticationManager->blockAuthentications([$authentication->getId()]);

        $this->assertTrue($authentication->isBlocked());
        $this->assertEquals($validTo, $authentication->getValidTo());
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return new User();
    }

    /**
     * @return Authentication
     */
    protected function getAuthentication()
    {
        return new Authentication();
    }

    /**
     * @return TwoFASAuthentication
     */
    protected function getTwoFASAuthentication()
    {
        return new TwoFASAuthentication(1, new \DateTime(), (new \DateTime())->add(new \DateInterval('PT15M')));
    }
}
