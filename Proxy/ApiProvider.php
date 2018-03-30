<?php

namespace TwoFAS\TwoFactorBundle\Proxy;

use Doctrine\Common\Collections\ArrayCollection;
use TwoFAS\Api\Authentication as ApiAuthentication;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Methods;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Encryption\Interfaces\ReadKey;
use TwoFAS\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Class for send requests to real API through SDK.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Proxy
 */
class ApiProvider implements ApiProviderInterface
{
    /**
     * @var TwoFAS
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
     * @param TwoFAS                   $api
     * @param ObjectPersisterInterface $authenticationPersister
     * @param ReadKey               $encryptionStorage
     */
    public function __construct(TwoFAS $api, ObjectPersisterInterface $authenticationPersister, ReadKey $encryptionStorage)
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
        $twoFASAuthentication = $this->api->requestAuthViaTotp($totpSecret);

        return $this->makeAuthentication($user, $twoFASAuthentication, Methods::TOTP);
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
