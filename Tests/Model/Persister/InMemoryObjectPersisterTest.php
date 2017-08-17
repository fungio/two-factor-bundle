<?php

namespace TwoFAS\TwoFactorBundle\Tests\Model\Persister;

use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Tests\DummyEntity;

class InMemoryObjectPersisterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryObjectPersister
     */
    private $persister;

    /**
     * @var InMemoryRepositoryInterface
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = new InMemoryRepository(DummyEntity::class, 'id');
        $this->persister  = new InMemoryObjectPersister($this->repository);
    }

    public function testGetEntity()
    {
        $this->assertInstanceOf(DummyEntity::class, $this->persister->getEntity());
    }

    public function testGetRepository()
    {
        $this->assertEquals($this->repository, $this->persister->getRepository());
    }

    public function testSaveEntity()
    {
        $entity = new DummyEntity();
        $entity
            ->setId(12)
            ->setValue('foo');

        $this->assertCount(0, $this->persister->getRepository()->findAll());
        $this->persister->saveEntity($entity);
        $this->assertCount(1, $this->persister->getRepository()->findAll());
    }

    public function testUpdateEntity()
    {
        $entity = new DummyEntity();
        $entity
            ->setId(12)
            ->setValue('foo');

        $this->persister->saveEntity($entity);

        $entity->setValue(15);
        $this->persister->saveEntity($entity);

        $this->assertCount(1, $this->persister->getRepository()->findAll());
    }

    public function testRemoveEntity()
    {
        $entity = new DummyEntity();
        $entity
            ->setId(12)
            ->setValue('foo');

        $this->persister->saveEntity($entity);
        $this->persister->removeEntity($entity);
        $this->assertCount(0, $this->persister->getRepository()->findAll());
    }
}
