<?php

namespace Fungio\TwoFactorBundle\Tests\Util;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Fungio\TwoFactorBundle\Model\Entity\User;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use Fungio\TwoFactorBundle\Util\UserManager;

class UserManagerTest extends KernelTestCase
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var ObjectPersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userPersister;

    /**
     * @var ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectRepository;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectRepository = $this
            ->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneByUserName'])
            ->getMockForAbstractClass();

        $this->userPersister = $this
            ->getMockBuilder(ObjectPersisterInterface::class)
            ->setMethods(['getEntity', 'getRepository'])
            ->getMockForAbstractClass();

        $this->userPersister->method('getRepository')->willReturn($this->objectRepository);

        $this->userManager = new UserManager($this->userPersister);
    }

    public function testFindByUserName()
    {
        $user = new User();
        $user->setUsername('tom');

        $this->objectRepository->method('findOneBy')->willReturn($user);

        $actualUser = $this->userManager->findByUserName('tom');

        $this->assertEquals($user, $actualUser);
    }

    public function testCreateUser()
    {
        $user = new User();

        $loggedUser = $this
            ->getMockBuilder(UserInterface::class)
            ->setMethods(['getUsername'])
            ->getMockForAbstractClass();

        $loggedUser->method('getUsername')->willReturn('tom');

        $this->userPersister->method('getEntity')->willReturn($user);

        $actualUser = $this->userManager->createUser($loggedUser);

        $this->assertEquals($user, $actualUser);
    }
}
