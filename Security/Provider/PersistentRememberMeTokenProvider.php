<?php

namespace Fungio\TwoFactorBundle\Security\Provider;

use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentToken;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Fungio\TwoFactorBundle\Model\Entity\RememberMeTokenInterface;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use Fungio\TwoFactorBundle\Util\BrowserParser;
use DateTime;

/**
 * Persistent provider for Fungio Remember Me Token which save token and browser information.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Security\Provider
 */
class PersistentRememberMeTokenProvider implements TokenProviderInterface
{
    /**
     * @var ObjectPersisterInterface
     */
    private $userPersister;

    /**
     * @var ObjectPersisterInterface
     */
    private $tokenPersister;

    /**
     * @var BrowserParser
     */
    private $browserParser;

    /**
     * @param ObjectPersisterInterface $userPersister
     * @param ObjectPersisterInterface $tokenPersister
     * @param BrowserParser            $browserParser
     */
    public function __construct(ObjectPersisterInterface $userPersister, ObjectPersisterInterface $tokenPersister, BrowserParser $browserParser)
    {
        $this->userPersister  = $userPersister;
        $this->tokenPersister = $tokenPersister;
        $this->browserParser  = $browserParser;
    }

    /**
     * @inheritDoc
     */
    public function loadTokenBySeries($series)
    {
        /** @var RememberMeTokenInterface|null $token */
        $token = $this->tokenPersister->getRepository()->find($series);

        if (is_null($token)) {
            throw new TokenNotFoundException('No token found.');
        }

        return new PersistentToken($token->getClass(), $token->getUser()->getUsername(), $series, $token->getValue(), $token->getLastUsedAt());
    }

    /**
     * @inheritDoc
     */
    public function deleteTokenBySeries($series)
    {
        $token = $this->tokenPersister->getRepository()->find($series);

        if (!is_null($token)) {
            $this->tokenPersister->removeEntity($token);
        }
    }

    /**
     * @inheritDoc
     */
    public function updateToken($series, $tokenValue, DateTime $lastUsed)
    {
        /** @var RememberMeTokenInterface|null $token */
        $token = $this->tokenPersister->getRepository()->find($series);

        if (is_null($token)) {
            throw new TokenNotFoundException('No token found.');
        }

        $token
            ->setSeries($series)
            ->setValue($tokenValue)
            ->setLastUsedAt($lastUsed)
            ->setBrowser($this->browserParser->toString());

        $this->tokenPersister->saveEntity($token);
    }

    /**
     * @inheritDoc
     */
    public function createNewToken(PersistentTokenInterface $token)
    {
        /** @var UserInterface|null $user */
        $user = $this->userPersister->getRepository()->findOneBy(['username' => $token->getUsername()]);

        /** @var RememberMeTokenInterface $rememberMeToken */
        $rememberMeToken = $this->tokenPersister->getEntity();

        $rememberMeToken
            ->setSeries($token->getSeries())
            ->setValue($token->getTokenValue())
            ->setClass($token->getClass())
            ->setUser($user)
            ->setBrowser($this->browserParser->toString())
            ->setIp($this->browserParser->getIp())
            ->setCreatedAt(new \DateTime())
            ->setLastUsedAt($token->getLastUsed());

        $user->addToken($rememberMeToken);

        $this->userPersister->saveEntity($user);
    }
}