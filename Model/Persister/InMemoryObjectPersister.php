<?php

namespace Fungio\TwoFactorBundle\Model\Persister;

/**
 * InMemoryObjectPersister for tests only.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Model\Persister
 */
class InMemoryObjectPersister implements ObjectPersisterInterface
{
    /**
     * @var InMemoryRepositoryInterface
     */
    protected $repository;

    /**
     * @var string
     */
    protected $className;

    /**
     * OptionManager constructor.
     *
     * @param InMemoryRepositoryInterface $repository
     */
    public function __construct(InMemoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->className  = $repository->getClassName();
    }

    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return new $this->className;
    }

    /**
     * @return InMemoryRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @inheritDoc
     */
    public function saveEntity($entity)
    {
        if ($this->repository->contains($entity)) {
            $collection = $this->repository->getItems();
            $key        = $collection->indexOf($entity);

            $collection->set($key, $entity);

        } else {
            $this->repository->add($entity);
        }
    }

    /**
     * @inheritDoc
     */
    public function removeEntity($entity)
    {
        $this->repository->remove($entity);
    }
}