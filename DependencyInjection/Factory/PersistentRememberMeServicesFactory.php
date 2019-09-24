<?php

namespace Fungio\TwoFactorBundle\DependencyInjection\Factory;

use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Fungio\TwoFactorBundle\Security\RememberMe\PersistentTokenBasedRememberMeServices;
use Fungio\TwoFactorBundle\Storage\TokenStorage;
use Fungio\TwoFactorBundle\Storage\UserStorageInterface;

/**
 * Factory for Two FAS Remember Me Services.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\DependencyInjection\Factory
 */
class PersistentRememberMeServicesFactory implements RememberMeServicesFactoryInterface
{
    /**
     * @var array
     */
    private $userProviders;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var TokenProviderInterface
     */
    private $tokenProvider;

    /**
     * @var UserStorageInterface
     */
    private $userStorage;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var array
     */
    private $options = [
        'name'                  => 'FUNGIO_REMEMBERME',
        'path'                  => '/',
        'domain'                => null,
        'secure'                => false,
        'httponly'              => true,
        'always_remember_me'    => false,
        'remember_me_parameter' => 'remember_two_factor'
    ];

    /**
     * PersistentTokenBasedRememberMeServicesFactory constructor.
     *
     * @param array                  $userProviders
     * @param string                 $secret
     * @param TokenStorage           $tokenStorage
     * @param TokenProviderInterface $tokenProvider
     * @param UserStorageInterface   $userStorage ,
     * @param LoggerInterface|null   $logger
     * @param array                  $options
     */
    public function __construct(
        array $userProviders,
        $secret,
        TokenStorage $tokenStorage,
        TokenProviderInterface $tokenProvider,
        UserStorageInterface $userStorage,
        LoggerInterface $logger = null,
        array $options = []
    ) {
        $this->userProviders = $userProviders;
        $this->secret        = $secret;
        $this->tokenStorage  = $tokenStorage;
        $this->tokenProvider = $tokenProvider;
        $this->userStorage   = $userStorage;
        $this->logger        = $logger;
        $this->options       = array_merge($this->options, $options);
    }

    /**
     * @inheritdoc
     */
    public function createInstance()
    {
        /** @var UsernamePasswordToken|RememberMeToken $token */
        $token = $this->tokenStorage->getToken();

        if (!$this->tokenStorage->isValid($token)) {
            throw new LogicException('Token for Two FAS Remember Me Service is not valid');
        }

        return new PersistentTokenBasedRememberMeServices(
            $this->userProviders,
            $this->secret,
            $token->getProviderKey(),
            $this->options,
            $this->tokenProvider,
            $this->userStorage,
            $this->logger
        );
    }
}
