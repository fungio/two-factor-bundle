<?php

namespace Fungio\TwoFactorBundle\Proxy;

use Doctrine\Common\Collections\ArrayCollection;
use Fungio\Api\Authentication as ApiAuthentication;
use Fungio\Api\AuthenticationCollection;
use Fungio\Api\IntegrationUser;
use Fungio\Api\Methods;
use Fungio\Api\Fungio;
use Fungio\Encryption\Interfaces\ReadKey;
use Fungio\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Class for send requests to real API through SDK.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Proxy
 */
class ApiProvider implements ApiProviderInterface
{
    /**
     * @var Fungio
     */
    private $api;

    /**
     * @var ReadKey
     */
    private $encryptionStorage;

    /**
     * @var ObjectPersisterInterface
     */
    private $authenticationPersister;

    /**
     * ApiProvider constructor.
     *
     * @param Fungio                   $api
     * @param ObjectPersisterInterface $authenticationPersister
     * @param ReadKey               $encryptionStorage
     */
    public function __construct(Fungio $api, ObjectPersisterInterface $authenticationPersister, ReadKey $encryptionStorage)
    {
        $this->api                     = $api;
        $this->authenticationPersister = $authenticationPersister;
        $this->encryptionStorage       = $encryptionStorage;
    }

    /**
     * @inheritDoc
     */
    public function requestAuthViaTotp(UserInterface $user, $totpSecret)
    {
        $fungioAuthentication = $this->api->requestAuthViaTotp($totpSecret);

        return $this->makeAuthentication($user, $fungioAuthentication, Methods::TOTP);
    }

    /**
     * @inheritDoc
     */
    public function checkCode(ArrayCollection $authentications, $code)
    {
        $collection = new AuthenticationCollection();

        $authentications->map(function(AuthenticationInterface $authentication) use ($collection) {
            $collection->add(new ApiAuthentication(
                    $authentication->getId(),
                    $authentication->getCreatedAt(),
                    $authentication->getValidTo())
            );
        });

        return $this->api->checkCode($collection, $code);
    }

    /**
     * @inheritDoc
     */
    public function getIntegrationUserByExternalId($id)
    {
        return $this->api->getIntegrationUserByExternalId($this->encryptionStorage, $id);
    }

    /**
     * @inheritDoc
     */
    public function addIntegrationUser(IntegrationUser $integrationUser)
    {
        return $this->api->addIntegrationUser($this->encryptionStorage, $integrationUser);
    }

    /**
     * @inheritDoc
     */
    public function updateIntegrationUser(IntegrationUser $integrationUser)
    {
        return $this->api->updateIntegrationUser($this->encryptionStorage, $integrationUser);
    }

    /**
     * @param UserInterface     $user
     * @param ApiAuthentication $apiAuthentication
     * @param string            $channel
     *
     * @return AuthenticationInterface
     */
    protected function makeAuthentication(UserInterface $user, ApiAuthentication $apiAuthentication, $channel)
    {
        /** @var AuthenticationInterface $authentication */
        $authentication = $this->authenticationPersister->getEntity();

        $authentication
            ->setId($apiAuthentication->id())
            ->setUser($user)
            ->setType($channel)
            ->setVerified(false)
            ->setCreatedAt($apiAuthentication->createdAt())
            ->setValidTo($apiAuthentication->validTo());

        return $authentication;
    }
}
