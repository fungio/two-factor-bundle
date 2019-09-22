<?php

namespace Fungio\TwoFactorBundle\Model\Persister;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * InMemoryRepository for tests only.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Model\Persister
 */
class InMemoryRepository implements InMemoryRepositoryInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $primaryKey;

    /**
     * @var ArrayCollection
     */
    private $items;

    /**
     * InMemoryRepository constructor.
     *
     * @param string $class
     * @param string $primaryKey
     */
    public function __construct($class, $primaryKey)
    {
        $this->items      = new ArrayCollection();
        $this->class      = $class;
        $this->primaryKey = $primaryKey;
    }

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        return $this->findOneBy([$this->primaryKey => $id]);
    }

    /**
     * @inheritDoc
     */
    public function findOneBy(array $criteria)
    {
        $result = $this->findBy($criteria, null, 1);

        if (!is_null($result)) {
            return array_pop($result);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteriaBuilder = Criteria::create();
        $expr            = Criteria::expr();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $criteriaBuilder = $criteriaBuilder->andWhere($expr->in($field, $value));
            } else {
                $criteriaBuilder = $criteriaBuilder->andWhere($expr->eq($field, $value));
            }
        }

        if (is_null($offset)) {
            $offset = 0;
        }

        $result = $this->items->matching($criteriaBuilder)->slice($offset, $limit);

        if (count($result)) {
            return $result;
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function findAll()
    {
        return $this->items->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return $this->class;
    }

    /**
     * @inheritDoc
     */
    public function add($entity)
    {
        $this->items->add($entity);
    }

    /**
     * @inheritDoc
     */
    public function remove($entity)
    {
        $this->removeRelated($entity);

        return $this->items->removeElement($entity);
    }

    /**
     * @param object      $entity
     * @param object|null $related
     */
    private function removeRelated($entity, $related = null)
    {
        $object = !is_null($related) ? $related : $entity;

        $class      = new \ReflectionClass($object);
        $properties = $class->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $field = $property->getValue($object);

            if ($field instanceof ArrayCollection) {
                $field->removeElement($entity);
            }

            if (is_object($field)) {
                $this->removeRelated($entity, $field);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function contains($entity)
    {
        return $this->items->contains($entity);
    }

    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return array|object The found entity/entities.
     */
    public function __call($method, $arguments)
    {
        switch (true) {
            case (0 === strpos($method, 'findBy')):
                $by     = substr($method, 6);
                $method = 'findBy';
                break;

            case (0 === strpos($method, 'findOneBy')):
                $by     = substr($method, 9);
                $method = 'findOneBy';
                break;

            default:
                throw new \BadMethodCallException(
                    "Undefined method '$method'. The method name must start with " .
                    "either findBy or findOneBy!"
                );
        }

        if (empty($arguments)) {
            throw new \InvalidArgumentException('Empty argument list');
        }

        $fieldName = lcfirst($by);

        switch (count($arguments)) {
            case 1:
                return $this->$method([$fieldName => $arguments[0]]);

            case 3:
                return $this->$method([$fieldName => $arguments[0]], $arguments[1], $arguments[2]);

            case 4:
                return $this->$method([$fieldName => $arguments[0]], $arguments[1], $arguments[2], $arguments[3]);
        }

        throw new \BadMethodCallException('Invalid call ' . $method . $by);
    }
}