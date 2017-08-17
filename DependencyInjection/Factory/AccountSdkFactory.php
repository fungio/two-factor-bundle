<?php

namespace TwoFAS\TwoFactorBundle\DependencyInjection\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use TwoFAS\TwoFactorBundle\TwoFASTwoFactorBundle;
use TwoFAS\Account\OAuth\Interfaces\TokenStorage;
use TwoFAS\Account\OAuth\TokenType;
use TwoFAS\Account\TwoFAS;

/**
 * Factory for Account SDK.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\DependencyInjection\Factory
 */
class AccountSdkFactory
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var TokenType
     */
    private $tokenType;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var null|string
     */
    private $appName;

    /**
     * @var null|string
     */
    private $baseUrl;

    /**
     * @param TokenStorage $tokenStorage
     * @param TokenType    $tokenType
     * @param RequestStack $requestStack
     * @param null|string  $appName
     * @param null|string  $baseUrl
     */
    public function __construct(TokenStorage $tokenStorage, TokenType $tokenType, RequestStack $requestStack, $appName = null, $baseUrl = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->tokenType    = $tokenType;
        $this->requestStack = $requestStack;
        $this->appName      = $appName;
        $this->baseUrl      = $baseUrl;
    }

    /**
     * @return TwoFAS
     */
    public function createInstance()
    {
        $headers = [
            'Plugin-Version' => TwoFASTwoFactorBundle::VERSION,
            'Php-Version'    => phpversion(),
            'App-Version'    => Kernel::VERSION,
            'App-Name'       => $this->appName,
            'App-Url'        => $this->getUrl()
        ];

        $account = new TwoFAS($this->tokenStorage, $this->tokenType, $headers);

        if (!is_null($this->baseUrl)) {
            $account->setBaseUrl($this->baseUrl);
        }

        return $account;
    }

    /**
     * @return null|string
     */
    private function getUrl()
    {
        $request = $this->requestStack->getMasterRequest();

        if ($request instanceof Request) {
            return $request->getHttpHost();
        }

        return null;
    }
}