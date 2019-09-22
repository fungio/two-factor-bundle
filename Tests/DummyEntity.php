<?php

namespace Fungio\TwoFactorBundle\Tests;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * DummyEntity for tests.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Tests
 */
class DummyEntity
{
    /**
     * @var mixed
     */
    private $id;

    /**
     * @var string
     */
    private $value;

    /**
     * @var ArrayCollection
     */
    private $related;

    /**
     * DummyEntity constructor.
     */
    public function __construct()
    {
        $this->related = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return DummyEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return DummyEntity
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * @param RelatedEntity $related
     *
     * @return DummyEntity
     */
    public function addRelated($related)
    {
        $this->related->add($related);
        return $this;
    }
}