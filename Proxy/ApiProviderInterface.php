<?php

namespace Fungio\TwoFactorBundle\Proxy;

use Doctrine\Common\Collections\ArrayCollection;
use TwoFAS\Api\Code\Code;
use TwoFAS\Api\IntegrationUser;
use Fungio\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface;

/**
 * Contract for methods for make authentication, check code etc.
 * Can use for local implementations without remote API.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Proxy
 */
interface ApiProviderInterface
{
    /**
     * @param UserInterface $user
     * @param string        $totpSecret
     *
     * @return AuthenticationInterface
     */
    public function requestAuthViaTotp(UserInterface $user, $totpSecret);

    /**
     * @param ArrayCollection $authentications
     * @param string          $code
     *
     * @return Code
     */
    public function checkCode(ArrayCollection $authentications, $code);

    /**
     * @param int $id
     *
     * @return null|IntegrationUser
     */
    public function getIntegrationUserByExternalId($id);

    /**
     * @param IntegrationUser $integrationUser
     *
     * @return IntegrationUser
     */
    public function addIntegrationUser(IntegrationUser $integrationUser);

    /**
     * @param IntegrationUser $integrationUser
     *
     * @return IntegrationUser
     */
    public function updateIntegrationUser(IntegrationUser $integrationUser);
}