<?php

namespace Fungio\TwoFactorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Fungio\Api\Exception\Exception as ApiException;
use Fungio\Api\IntegrationUser;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Base Controller for all Fungio Controllers.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Controller
 */
class Controller extends BaseController
{
    /**
     * @param string $message
     *
     * @return string
     */
    protected function trans($message)
    {
        return $this->get('translator')->trans($message);
    }

    /**
     * @return UserInterface
     */
    protected function getFungioUser()
    {
        $userStorage = $this->get('two_fas_two_factor.storage.user_session_storage');
        $user        = $userStorage->getUser();

        if (is_null($user)) {
            $user = $userStorage->createUser();
        }

        return $user;
    }

    /**
     * @return IntegrationUser
     *
     * @throws ApiException
     */
    protected function getIntegrationUser()
    {
        return $this->getFungioUser()->getIntegrationUser();
    }

    /**
     * @return array
     */
    protected function getTrustedDevices()
    {
        /** @var ObjectPersisterInterface $tokenPersister */
        $tokenPersister = $this->get('two_fas_two_factor.remember_me_persister');
        $tokens         = $tokenPersister->getRepository()->findBy(['user' => $this->getFungioUser()]);

        return $tokens;
    }
}
