<?php

namespace Fungio\TwoFactorBundle\Tests\Model\Persister;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersister;
use Fungio\TwoFactorBundle\Tests\DummyEntity;

class ObjectPersisterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    public function setUp()
    {
        parent::setUp();

        $this->objectManager = $this->getMockBuilder(ObjectManager::class)->getMockForAbstractClass();
        $metaData            = $this->getMockBuilder(ClassMetadata::class)->getMockForAbstractClass();
        $metaData->method('getName')->willReturn(DummyEntity::class);
        $this->objectManager->method('getClassMetadata')->willReturn($metaData);
        $repository = $this->getMockForAbstractClass(ObjectRepository::class);
        $this->objectManager->method('getRepository')->willReturn($repository);
    }

    public function testGetEntity()
    {
        $objectPersister = new ObjectPersister($this->objectManager, 'DummyObject');
        $this->assertInstanceOf(DummyEntity::class, $objectPersister->getEntity());
    }

    public function testGetRepository()
    {
        $objectPersister = new ObjectPersister($this->objectManager, 'DummyObject');
        $this->assertInstanceOf(ObjectRepository::class, $objectPersister->getRepository());
    }

    public function testSaveEntity()
    {
        $this->objectManager->expects($this->once())->method('persist');
        $this->objectManager->expects($this->once())->method('flush');
        $objectPersister = new ObjectPersister($this->objectManager, 'DummyObject');
        $objectPersister->saveEntity(new DummyEntity());
    }

    public function testRemoveEntity()
    {
        $this->objectManager->expects($this->once())->method('remove');
        $this->objectManager->expects($this->once())->method('flush');
        $objectPersister = new ObjectPersister($this->objectManager, 'DummyObject');
        $objectPersister->removeEntity(new DummyEntity());
    }
}
