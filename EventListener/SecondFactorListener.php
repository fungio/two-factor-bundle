<?php

namespace TwoFAS\TwoFactorBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use TwoFAS\TwoFactorBundle\DependencyInjection\Factory\RememberMeServicesFactoryInterface;
use TwoFAS\TwoFactorBundle\Security\Token\TwoFactorRememberMeToken;
use TwoFAS\TwoFactorBundle\Storage\TokenStorage;
use TwoFAS\TwoFactorBundle\Util\ConfigurationChecker;

/**
 * Listen on every request that is two factor authentication is enabled
 * and redirect user to code check controller if not authenticated with 2FAS.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\EventListener
 */
class SecondFactorListener
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var RememberMeServicesFactoryInterface
     */
    private $rememberMeFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var ConfigurationChecker
     */
    private $configurationChecker;

    /**
     * @var array
     */
    private $firewalls;

    /**
     * TwoFactorListener constructor.
     *
     * @param TokenStorage                       $tokenStorage
     * @param AuthorizationCheckerInterface      $authorizationChecker
     * @param AuthenticationManagerInterface     $authenticationManager
     * @param RememberMeServicesFactoryInterface $rememberMeFactory
     * @param RouterInterface                    $router
     * @param SessionInterface                   $session
     * @param ConfigurationChecker               $configurationChecker
     * @param array                              $firewalls
     */
    public function __construct(
        TokenStorage $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        AuthenticationManagerInterface $authenticationManager,
        RememberMeServicesFactoryInterface $rememberMeFactory,
        RouterInterface $router,
        SessionInterface $session,
        ConfigurationChecker $configurationChecker,
        array $firewalls = []
    ) {
        $this->tokenStorage          = $tokenStorage;
        $this->authorizationChecker  = $authorizationChecker;
        $this->authenticationManager = $authenticationManager;
        $this->rememberMeFactory     = $rememberMeFactory;
        $this->router                = $router;
        $this->session               = $session;
        $this->configurationChecker  = $configurationChecker;
        $this->firewalls             = $firewalls;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (!$this->isTokenValid()) {
            return;
        }

        if ($this->isGranted()) {
            return;
        }

        if ($this->isRemembered($event->getRequest())) {
            return;
        }

        if (!$this->configurationChecker->isTwoFASEnabled()) {
            return;
        }

        if (!$this->isSecondFactorEnabledForUser()) {
            return;
        }

        if ($this->isTwoFASCheckRoute($event->getRequest()->getRequestUri())) {
            return;
        }

        $this->saveRedirectPath($event->getRequest());

        $event->setResponse(new RedirectResponse($this->router->generate('twofas_check')));
    }

    /**
     * @return bool
     */
    protected function isTokenValid()
    {
        /** @var UsernamePasswordToken|RememberMeToken $token */
        $token = $this->tokenStorage->getToken();

        if (!$this->tokenStorage->isValid($token)) {
            return false;
        }

        if (!in_array($token->getProviderKey(), $this->firewalls)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isGranted()
    {
        return $this->authorizationChecker->isGranted('IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isRemembered(Request $request)
    {
        $rememberMeService = $this->rememberMeFactory->createInstance();
        $token             = $rememberMeService->autoLogin($request);

        if (null === $token) {
            return false;
        }

        /** @var $token UsernamePasswordToken|RememberMeToken */
        $token = $this->authenticationManager->authenticate($token);
        $this->tokenStorage->setToken($this->rememberMe($token));

        return true;
    }

    /**
     * @param RememberMeToken $token
     *
     * @return TwoFactorRememberMeToken
     */
    protected function rememberMe(RememberMeToken $token)
    {
        return new TwoFactorRememberMeToken($token->getUser(), $token->getProviderKey(), $token->getSecret());
    }

    /**
     * @return bool
     */
    protected function isSecondFactorEnabledForUser()
    {
        return $this->configurationChecker->isSecondFactorEnabledForUser();
    }

    /**
     * @param string $uri
     *
     * @return bool
     */
    protected function isTwoFASCheckRoute($uri)
    {
        return false !== stripos($uri, $this->router->generate('twofas_check'));
    }

    /**
     * @param Request $request
     */
    protected function saveRedirectPath(Request $request)
    {
        $this->session->set('two_fas_two_factor.redirect_after_login.path', $request->getRequestUri());
    }
}