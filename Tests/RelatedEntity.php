<?php

namespace TwoFAS\TwoFactorBundle\Tests;

/**
 * Related entity only for tests.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Tests
 */
class RelatedEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var DummyEntity
     */
    private $dummy;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return RelatedEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return DummyEntity
     */
    public function getDummy()
    {
        return $this->dummy;
    }

    /**
     * @param DummyEntity $dummy
     *
     * @return RelatedEntity
     */
    public function setDummy($dummy)
    {
        $this->dummy = $dummy;
        return $this;
    }
}