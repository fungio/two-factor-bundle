<?php

namespace TwoFAS\TwoFactorBundle\Model\Persister;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Interface to be implemented by object persisters.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Model\Persister
 */
interface ObjectPersisterInterface
{
    /**
     * @return object
     */
    public function getEntity();

    /**
     * @return ObjectRepository
     */
    public function getRepository();

    /**
     * @param object $entity
     */
    public function saveEntity($entity);

    /**
     * @param object $entity
     */
    public function removeEntity($entity);
}