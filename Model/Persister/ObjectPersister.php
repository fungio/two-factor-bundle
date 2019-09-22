<?php

namespace Fungio\TwoFactorBundle\Model\Persister;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * ObjectManager for Fungio Entities.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Model
 */
class ObjectPersister implements ObjectPersisterInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param ObjectManager $objectManager
     * @param string        $class
     */
    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->objectManager = $objectManager;
        $this->repository    = $objectManager->getRepository($class);
        $metadata            = $objectManager->getClassMetadata($class);
        $this->className     = $metadata->getName();
    }

    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return new $this->className;
    }

    /**
     * @inheritdoc
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @inheritdoc
     */
    public function saveEntity($entity)
    {
        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    /**
     * @inheritDoc
     */
    public function removeEntity($entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
    }
}