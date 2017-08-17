<?php

namespace TwoFAS\TwoFactorBundle\Tests\Model\Persister;

use Doctrine\Common\Collections\ArrayCollection;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Tests\DummyEntity;
use TwoFAS\TwoFactorBundle\Tests\RelatedEntity;

class InMemoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryRepositoryInterface
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = new InMemoryRepository(DummyEntity::class, 'id');
    }

    public function testGetClassName()
    {
        $this->assertEquals(DummyEntity::class, $this->repository->getClassName());
    }

    public function testGetItems()
    {
        $this->assertInstanceOf(ArrayCollection::class, $this->repository->getItems());
    }

    public function testAdd()
    {
        $this->repository->add(new DummyEntity());
        $this->assertEquals(1, $this->repository->getItems()->count());
    }

    public function testContains()
    {
        $entity = new DummyEntity();
        $entity
            ->setId(1)
            ->setValue('foo');

        $this->repository->add($entity);
        $this->assertTrue($this->repository->contains($entity));
    }

    public function testNotContains()
    {
        $entity = new DummyEntity();
        $entity
            ->setId(1)
            ->setValue('foo');

        $entity2 = new DummyEntity();
        $entity2
            ->setId(2)
            ->setValue('bar');

        $this->repository->add($entity);
        $this->assertFalse($this->repository->contains($entity2));
    }

    public function testRemove()
    {
        $entity = new DummyEntity();
        $entity->setId(1);

        $entity2 = new DummyEntity();
        $entity2->setId(2);

        $this->repository->add($entity);
        $this->repository->add($entity2);
        $this->repository->remove($entity);

        $this->assertEquals(1, $this->repository->getItems()->count());
        $this->assertTrue($this->repository->contains($entity2));
    }

    public function testRemoveRelated()
    {
        $entity = new DummyEntity();
        $entity->setId(1);

        $related = new RelatedEntity();
        $related->setId(3)->setDummy($entity);

        $entity->addRelated($related);
        $this->repository->add($related);

        $this->assertCount(1, $this->repository->findAll());
        $this->assertEquals(1, $entity->getRelated()->count());

        $this->repository->remove($related);

        $this->assertEquals(0, $entity->getRelated()->count());
    }

    public function testFind()
    {
        $entity = new DummyEntity();
        $entity->setId(1);

        $entity2 = new DummyEntity();
        $entity2->setId(2);

        $this->repository->add($entity);
        $this->repository->add($entity2);

        $this->assertEquals($entity, $this->repository->find(1));
    }

    public function testFindByAnotherPrimaryKey()
    {
        $repository = new InMemoryRepository(DummyEntity::class, 'value');

        $entity = new DummyEntity();
        $entity->setId(1)->setValue('foo');

        $entity2 = new DummyEntity();
        $entity2->setId(2)->setValue('bar');

        $repository->add($entity);
        $repository->add($entity2);

        $this->assertEquals($entity2, $repository->find('bar'));
    }

    public function testFindBy()
    {
        $entity = new DummyEntity();
        $entity->setId(1)->setValue('foo');

        $entity2 = new DummyEntity();
        $entity2->setId(2)->setValue('bar');

        $this->repository->add($entity);
        $this->repository->add($entity2);

        $result = $this->repository->findBy(['value' => 'bar']);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals($entity2, array_pop($result));
    }

    public function testFindOneBy()
    {
        $entity = new DummyEntity();
        $entity->setId(1)->setValue('foo');

        $entity2 = new DummyEntity();
        $entity2->setId(2)->setValue('bar');

        $this->repository->add($entity);
        $this->repository->add($entity2);

        $this->assertEquals($entity2, $this->repository->findOneBy(['value' => 'bar']));
    }

    public function testFindAll()
    {
        $entity = new DummyEntity();
        $entity->setId(1)->setValue('foo');

        $entity2 = new DummyEntity();
        $entity2->setId(2)->setValue('bar');

        $this->repository->add($entity);
        $this->repository->add($entity2);

        $this->assertCount(2, $this->repository->findAll());
    }

    public function testFindByFieldName()
    {
        $entity = new DummyEntity();
        $entity->setId(1)->setValue('foo');

        $entity2 = new DummyEntity();
        $entity2->setId(2)->setValue('bar');

        $this->repository->add($entity);
        $this->repository->add($entity2);

        $result = $this->repository->findByValue('bar');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals($entity2, array_pop($result));
    }

    public function testFindOneByFieldName()
    {
        $entity = new DummyEntity();
        $entity->setId(1)->setValue('foo');

        $entity2 = new DummyEntity();
        $entity2->setId(2)->setValue('bar');

        $this->repository->add($entity);
        $this->repository->add($entity2);

        $this->assertEquals($entity2, $this->repository->findOneBy(['value' => 'bar']));
    }

    public function testLimitedFindByFieldName()
    {
        $entity = new DummyEntity();
        $entity->setId(1)->setValue('bar');

        $entity2 = new DummyEntity();
        $entity2->setId(2)->setValue('bar');

        $entity3 = new DummyEntity();
        $entity3->setId(3)->setValue('bar');

        $this->repository->add($entity);
        $this->repository->add($entity2);
        $this->repository->add($entity3);

        $result = $this->repository->findByValue('bar', null, 2);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
    }

    public function testLimitedWithOffsetFindByFieldName()
    {
        $entity = new DummyEntity();
        $entity->setId(1)->setValue('bar');

        $entity2 = new DummyEntity();
        $entity2->setId(2)->setValue('bar');

        $entity3 = new DummyEntity();
        $entity3->setId(3)->setValue('bar');

        $this->repository->add($entity);
        $this->repository->add($entity2);
        $this->repository->add($entity3);

        $result = $this->repository->findByValue('bar', null, 1, 1);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals($entity2, array_pop($result));
    }

    public function testBadMethodCall()
    {
        $this->setExpectedException(\BadMethodCallException::class);
        $this->repository->notExistentMethod();
    }

    public function testFindOneByFiledNameWithoutArguments()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Empty argument list');
        $this->repository->findOneByValue();
    }

    public function testFindOneByWithTooManyArguments()
    {
        $this->setExpectedException(\BadMethodCallException::class, 'Invalid call findOneByValue');
        $this->repository->findOneByValue(0, 0, 0, 0, 0);
    }
}
