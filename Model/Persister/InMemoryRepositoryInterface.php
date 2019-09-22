<?php

namespace Fungio\TwoFactorBundle\Model\Persister;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Contract for InMemory Repository classes.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Model\Persister
 */
interface InMemoryRepositoryInterface extends ObjectRepository
{
    /**
     * @param object $entity
     */
    public function add($entity);

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function remove($entity);

    /**
     * @param $entity
     *
     * @return bool
     */
    public function contains($entity);

    /**
     * @return ArrayCollection
     */
    public function getItems();
}