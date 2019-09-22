<?php

namespace Fungio\TwoFactorBundle\Util;

use Fungio\Api\Methods;
use Psr\SimpleCache\CacheInterface;
use Fungio\TwoFactorBundle\Cache\CacheKeys;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use Fungio\TwoFactorBundle\Storage\UserStorageInterface;

/**
 * Checks 2FAS configuration (is enabled, is configured etc.)
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Util
 */
class ConfigurationChecker
{
    /**
     * @var ObjectPersisterInterface
     */
    private $optionPersister;

    /**
     * @var UserStorageInterface
     */
    private $userStorage;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * ConfigurationChecker constructor.
     *
     * @param ObjectPersisterInterface $optionPersister
     * @param UserStorageInterface     $userStorage
     * @param CacheInterface           $cache
     */
    public function __construct(ObjectPersisterInterface $optionPersister, UserStorageInterface $userStorage, CacheInterface $cache)
    {
        $this->optionPersister = $optionPersister;
        $this->userStorage     = $userStorage;
        $this->cache           = $cache;
    }

    /**
     * @return bool
     */
    public function isFungioConfigured()
    {
        if ($this->cache->has(CacheKeys::CONFIGURED)) {
            return $this->cache->get(CacheKeys::CONFIGURED);
        }

        $configured = count($this->optionPersister->getRepository()->findAll()) > 0;

        $this->cache->set(CacheKeys::CONFIGURED, $configured);

        return $configured;
    }

    /**
     * @return bool
     */
    public function isFungioEnabled()
    {
        if ($this->cache->has(CacheKeys::ENABLED)) {
            return $this->cache->get(CacheKeys::ENABLED);
        }

        /** @var OptionInterface|null $option */
        $option = $this->optionPersister->getRepository()->findOneBy(['name' => OptionInterface::STATUS]);

        $enabled = ($option instanceof OptionInterface) && (true === (bool) $option->getValue());

        $this->cache->set(CacheKeys::ENABLED, $enabled);

        return $enabled;
    }

    /**
     * @return bool
     */
    public function isSecondFactorEnabledForUser()
    {
        $fungioUser = $this->userStorage->getUser();

        if (null === $fungioUser) {
            return false;
        }

        return $fungioUser->isChannelEnabled(Methods::TOTP);
    }
}