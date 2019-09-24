<?php

namespace Fungio\TwoFactorBundle\Storage;

use TwoFAS\Encryption\Cryptographer;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use TwoFAS\Account\OAuth\Interfaces\TokenStorage as AccountTokenStorage;
use TwoFAS\Account\OAuth\Token;
use TwoFAS\Account\OAuth\TokenNotFoundException;

/**
 * Storage for OAuth tokens.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Storage
 */
class OAuthTokenStorage implements AccountTokenStorage
{
    /**
     * @var ObjectPersisterInterface
     */
    private $optionPersister;

    /**
     * @var Cryptographer
     */
    private $cryptographer;

    /**
     * @param ObjectPersisterInterface $optionPersister
     * @param Cryptographer            $cryptographer
     */
    public function __construct(ObjectPersisterInterface $optionPersister, Cryptographer $cryptographer)
    {
        $this->optionPersister = $optionPersister;
        $this->cryptographer   = $cryptographer;
    }

    /**
     * @inheritDoc
     */
    public function storeToken(Token $token)
    {
        $cryptographer = $this->cryptographer;
        $option        = $this->getOption($token->getType());

        if (is_null($option)) {
            /** @var OptionInterface $option */
            $option = $this->optionPersister->getEntity();
        }

        $option
            ->setName(OptionInterface::OAUTH_SCOPE . '_' . $token->getType())
            ->setValue($cryptographer->encrypt(serialize($token)));

        $this->optionPersister->saveEntity($option);
    }

    /**
     * @param string $type
     *
     * @return null|OptionInterface
     */
    protected function getOption($type)
    {
        return $this->optionPersister->getRepository()->findOneBy(['name' => OptionInterface::OAUTH_SCOPE . '_' . $type]);
    }

    /**
     * @inheritDoc
     */
    public function retrieveToken($type)
    {
        $cryptographer = $this->cryptographer;
        $option        = $this->getOption($type);

        if (is_null($option)) {
            throw new TokenNotFoundException('Token: "' . $type . '" not found in storage.');
        }

        $token = unserialize($cryptographer->decrypt($option->getValue()));

        if (!$token instanceof Token) {
            throw new \RuntimeException('Invalid token in storage.');
        }

        return $token;
    }
}