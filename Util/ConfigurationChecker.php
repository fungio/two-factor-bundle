<?php

namespace TwoFAS\TwoFactorBundle\Util;

use TwoFAS\Api\Methods;
use Psr\SimpleCache\CacheInterface;
use TwoFAS\TwoFactorBundle\Cache\CacheKeys;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use TwoFAS\TwoFactorBundle\Storage\UserStorageInterface;

/**
 * Checks 2FAS configuration (is enabled, is configured etc.)
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Util
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
    public function isTwoFASConfigured()
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
    public function isTwoFASEnabled()
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
        $twoFASUser = $this->userStorage->getUser();

        if (null === $twoFASUser) {
            return false;
        }

        return $twoFASUser->isChannelEnabled(Methods::TOTP);
    }
}