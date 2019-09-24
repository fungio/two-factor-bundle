<?php

namespace Fungio\TwoFactorBundle\Util;

use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Exception\Exception as ApiException;
use TwoFAS\Api\MobileSecretGenerator;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface;
use Fungio\TwoFactorBundle\Proxy\ApiProviderInterface;

/**
 * Facade class between application and 2FAS api - manages IntegrationUser (find, save, update etc.)
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Util
 */
class IntegrationUserManager
{
    /**
     * @var ApiProviderInterface
     */
    private $provider;

    /**
     * @param ApiProviderInterface $provider
     */
    public function __construct(ApiProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param int $id
     *
     * @return null|IntegrationUser
     *
     * @throws ApiException
     */
    public function findByExternalId($id)
    {
        try {
            return $this->provider->getIntegrationUserByExternalId($id);
        } catch (IntegrationUserNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param UserInterface $user
     *
     * @return IntegrationUser
     *
     * @throws ApiException
     */
    public function createUser(UserInterface $user)
    {
        $integrationUser = new IntegrationUser();
        $integrationUser
            ->setExternalId($user->getId())
            ->setMobileSecret(MobileSecretGenerator::generate());

        return $this->provider->addIntegrationUser($integrationUser);
    }

    /**
     * @param IntegrationUser $user
     *
     * @return IntegrationUser
     *
     * @throws ApiException
     */
    public function updateUser(IntegrationUser $user)
    {
        return $this->provider->updateIntegrationUser($user);
    }
}