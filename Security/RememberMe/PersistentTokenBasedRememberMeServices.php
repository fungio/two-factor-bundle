<?php

namespace Fungio\TwoFactorBundle\Security\RememberMe;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentToken;
use Symfony\Component\Security\Core\Authentication\RememberMe\TokenProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\CookieTheftException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Fungio\TwoFactorBundle\Storage\UserStorageInterface;

/**
 * Overwritten class to handle multiple fungio_rememberme cookies (for multiple users).
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Security\RememberMe
 */
class PersistentTokenBasedRememberMeServices implements RememberMeServicesInterface, LogoutHandlerInterface
{
    const COOKIE_DELIMITER = ':';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $options = [
        'secure'   => false,
        'httponly' => true,
    ];

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var array
     */
    private $userProviders;

    /**
     * @var TokenProviderInterface
     */
    private $tokenProvider;

    /**
     * @var UserStorageInterface
     */
    private $userStorage;

    /**
     * @param array                  $userProviders
     * @param string                 $secret
     * @param string                 $providerKey
     * @param array                  $options
     * @param TokenProviderInterface $tokenProvider
     * @param UserStorageInterface   $userStorage
     * @param LoggerInterface        $logger
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        array $userProviders,
        $secret,
        $providerKey,
        array $options = [],
        TokenProviderInterface $tokenProvider,
        UserStorageInterface $userStorage,
        LoggerInterface $logger = null
    ) {
        if (empty($secret)) {
            throw new \InvalidArgumentException('$secret must not be empty.');
        }
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }
        if (0 === count($userProviders)) {
            throw new \InvalidArgumentException('You must provide at least one user provider.');
        }

        $this->userProviders = $userProviders;
        $this->secret        = $secret;
        $this->providerKey   = $providerKey;
        $this->options       = array_merge($this->options, $options);
        $this->logger        = $logger;
        $this->tokenProvider = $tokenProvider;
        $this->userStorage   = $userStorage;
    }

    /**
     * @inheritdoc
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        // Do nothing
    }

    /**
     * @inheritdoc
     */
    public function autoLogin(Request $request)
    {
        if (null === $cookie = $this->getCookie($request)) {
            return;
        }

        if (null !== $this->logger) {
            $this->logger->debug('2FAS Remember-me cookie detected.');
        }
        $cookieParts = $this->decodeCookie($cookie);

        try {
            $user = $this->processAutoLoginCookie($cookieParts, $request);

            if (!$user instanceof UserInterface) {
                throw new \RuntimeException('processAutoLoginCookie() must return a UserInterface implementation.');
            }

            if (null !== $this->logger) {
                $this->logger->info('2FAS Remember-me cookie accepted.');
            }

            return new RememberMeToken($user, $this->providerKey, $this->secret);
        } catch (CookieTheftException $e) {
            $this->cancelCookie($request);

            throw $e;
        } catch (UsernameNotFoundException $e) {
            if (null !== $this->logger) {
                $this->logger->info('User for 2FAS remember-me cookie not found.');
            }
        } catch (UnsupportedUserException $e) {
            if (null !== $this->logger) {
                $this->logger->warning('User class for 2FAS remember-me cookie not supported.');
            }
        } catch (TokenNotFoundException $e) {
            if (null !== $this->logger) {
                $this->logger->debug('2FAS Remember-Me token not found.');
            }
        } catch (AuthenticationException $e) {
            if (null !== $this->logger) {
                $this->logger->debug('2FAS Remember-Me authentication failed.', ['exception' => $e]);
            }
        }

        $this->cancelCookie($request);
    }

    /**
     * @inheritDoc
     */
    public function loginFail(Request $request, Exception $exception = null)
    {
        $this->cancelCookie($request);
    }

    /**
     * @inheritDoc
     */
    public function loginSuccess(Request $request, Response $response, TokenInterface $token)
    {
        // Make sure any old remember-me cookies are cancelled
        $this->cancelCookie($request);

        if (!$token->getUser() instanceof UserInterface) {
            if (null !== $this->logger) {
                $this->logger->debug('Remember-me ignores token since it does not contain a UserInterface implementation.');
            }

            return;
        }

        if (!$this->isRememberMeRequested($request)) {
            if (null !== $this->logger) {
                $this->logger->debug('Remember-me was not requested.');
            }

            return;
        }

        if (null !== $this->logger) {
            $this->logger->debug('Remember-me was requested; setting cookie.');
        }

        // Remove attribute from request that sets a NULL cookie.
        // It was set by $this->cancelCookie()
        // (cancelCookie does other things too for some RememberMeServices
        // so we should still call it at the start of this method)
        $request->attributes->remove(self::COOKIE_ATTR_NAME);

        $this->onLoginSuccess($response, $token);
    }

    /**
     * @param array   $cookieParts
     * @param Request $request
     *
     * @return UserInterface
     */
    protected function processAutoLoginCookie(array $cookieParts, Request $request)
    {
        if (count($cookieParts) !== 2) {
            throw new AuthenticationException('The cookie is invalid.');
        }

        list($series, $tokenValue) = $cookieParts;
        $persistentToken = $this->tokenProvider->loadTokenBySeries($series);
        $loggedUser      = $this->userStorage->getUser();

        if ($loggedUser->getUsername() != $persistentToken->getUsername()) {
            throw new AuthenticationException('The cookie comes from another user.');
        }

        if (!hash_equals($persistentToken->getTokenValue(), $tokenValue)) {
            throw new CookieTheftException('This token was already used. The account is possibly compromised.');
        }

        if ($persistentToken->getLastUsed()->getTimestamp() + $this->options['lifetime'] < time()) {
            throw new AuthenticationException('The cookie has expired.');
        }

        $tokenValue = base64_encode(random_bytes(64));
        $this->tokenProvider->updateToken($series, $tokenValue, new \DateTime());
        $request->attributes->set(self::COOKIE_ATTR_NAME,
            $this->createCookie(
                $this->encodeCookie([$series, $tokenValue]),
                time() + $this->options['lifetime'],
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            )
        );

        return $this->getUserProvider($persistentToken->getClass())->loadUserByUsername($persistentToken->getUsername());
    }

    /**
     * @param Response       $response
     * @param TokenInterface $token
     */
    protected function onLoginSuccess(Response $response, TokenInterface $token)
    {
        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));

        $this->tokenProvider->createNewToken(
            new PersistentToken(
                get_class($user = $token->getUser()),
                $user->getUsername(),
                $series,
                $tokenValue,
                new \DateTime()
            )
        );

        $response->headers->setCookie(
            $this->createCookie(
                $this->encodeCookie([$series, $tokenValue]),
                time() + $this->options['lifetime'],
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    protected function getCookie(Request $request)
    {
        $user = $this->userStorage->getUser();

        if (is_null($user)) {
            return null;
        }

        $id      = $user->getId();
        $cookies = $request->cookies->get($this->options['name']);

        if (!is_array($cookies) || !array_key_exists($id, $cookies)) {
            return null;
        }

        return $cookies[$id];
    }

    /**
     * @param string                                  $value
     * @param int|string|\DateTime|\DateTimeInterface $lifetime
     * @param string                                  $path
     * @param string                                  $domain
     * @param bool                                    $secure
     * @param bool                                    $httpOnly
     *
     * @return Cookie
     */
    protected function createCookie($value, $lifetime, $path, $domain, $secure, $httpOnly)
    {
        $id   = $this->userStorage->getUser()->getId();
        $name = $this->options['name'] . '[' . $id . ']';

        return new Cookie($name, $value, $lifetime, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Decodes the raw cookie value.
     *
     * @param string $rawCookie
     *
     * @return array
     */
    protected function decodeCookie($rawCookie)
    {
        return explode(self::COOKIE_DELIMITER, base64_decode($rawCookie));
    }

    /**
     * @param array $cookieParts
     *
     * @return string
     *
     * @throws \InvalidArgumentException When $cookieParts contain the cookie delimiter. Extending class should either remove or escape it.
     */
    protected function encodeCookie(array $cookieParts)
    {
        foreach ($cookieParts as $cookiePart) {
            if (false !== strpos($cookiePart, self::COOKIE_DELIMITER)) {
                throw new \InvalidArgumentException(sprintf('$cookieParts should not contain the cookie delimiter "%s"', self::COOKIE_DELIMITER));
            }
        }

        return base64_encode(implode(self::COOKIE_DELIMITER, $cookieParts));
    }

    /**
     * @param Request $request
     */
    protected function cancelCookie(Request $request)
    {
        // Delete cookie on the client
        if (null !== $this->logger) {
            $this->logger->debug('Clearing 2FAS remember-me cookie.', ['name' => $this->options['name']]);
        }

        $request->attributes->set(self::COOKIE_ATTR_NAME,
            $this->createCookie(
                null,
                1,
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            )
        );

        // Delete cookie from the tokenProvider
        if (null !== ($cookie = $this->getCookie($request))
            && count($parts = $this->decodeCookie($cookie)) === 2
        ) {
            list($series) = $parts;
            $this->tokenProvider->deleteTokenBySeries($series);
        }
    }

    /**
     * @param string $class
     *
     * @return UserProviderInterface
     */
    protected function getUserProvider($class)
    {
        foreach ($this->userProviders as $provider) {
            if ($provider->supportsClass($class)) {
                return $provider;
            }
        }

        throw new UnsupportedUserException(sprintf('There is no user provider that supports class "%s".', $class));
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isRememberMeRequested(Request $request)
    {
        if (true === $this->options['always_remember_me']) {
            return true;
        }

        $parameter = ParameterBagUtils::getRequestParameterValue($request, $this->options['remember_me_parameter']);

        if (null === $parameter && null !== $this->logger) {
            $this->logger->debug('Did not send remember-me cookie.', ['parameter' => $this->options['remember_me_parameter']]);
        }

        return $parameter === 'true' || $parameter === 'on' || $parameter === '1' || $parameter === 'yes' || $parameter === true;
    }
}
