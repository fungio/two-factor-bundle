<?php

namespace TwoFAS\TwoFactorBundle\Tests\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Translation\TranslatorInterface;
use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Code\RejectedCodeCannotRetry;
use TwoFAS\Api\Code\RejectedCodeCanRetry;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Methods;
use TwoFAS\Api\TwoFAS;
use TwoFAS\TwoFactorBundle\Cache\EmptyCacheStorage;
use TwoFAS\TwoFactorBundle\Model\Entity\Authentication;
use TwoFAS\TwoFactorBundle\Model\Entity\Option;
use TwoFAS\TwoFactorBundle\Model\Entity\OptionInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\RememberMeToken as TwoFASRememberMeToken;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Security\Token\TwoFactorToken;
use TwoFAS\TwoFactorBundle\Security\Voter\TrustedDeviceVoter;
use TwoFAS\TwoFactorBundle\Storage\UserSessionStorage;
use TwoFAS\TwoFactorBundle\Tests\UserEntity;
use TwoFAS\TwoFactorBundle\Util\AuthenticationManager;
use TwoFAS\TwoFactorBundle\Util\ConfigurationChecker;
use TwoFAS\TwoFactorBundle\Util\IntegrationUserManager;

abstract class ControllerTestCase extends WebTestCase
{
    /**
     * @var TwoFAS|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $api;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var IntegrationUserManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrationUserManager;

    /**
     * @var AuthenticationManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationManager;

    /**
     * @var IntegrationUser
     */
    protected $integrationUser;

    /**
     * @var UserEntity
     */
    protected $twoFASUser;

    /**
     * @var InMemoryRepositoryInterface
     */
    protected $tokenRepository;

    /**
     * @var InMemoryRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var InMemoryRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var OptionInterface
     */
    protected $twoFASStatus;

    /**
     * @var string
     */
    protected $csrfToken;

    /**
     * @var TrustedDeviceVoter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trustedDeviceVoter;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->client->followRedirects();
        $this->container       = $this->client->getContainer();
        $this->translator      = $this->container->get('translator');
        $this->csrfToken       = $this->container->get('security.csrf.token_manager')->getToken('twofas_csrf_token');
        $this->integrationUser = new IntegrationUser();
        $this->twoFASUser      = new UserEntity();

        $this->mockApi();
        $this->mockObjectManager();
        $this->mockIntegrationUserManager();
        $this->mockAuthenticationManager();
        $this->mockOptionRepository();
        $this->mockTokenRepository();
        $this->mockUserRepository();
        $this->mockUserStorage();
        $this->mockConfigurationChecker();
    }

    protected function login()
    {
        $firewall = '2fas';
        $user     = new User('admin', 'adminpass', ['ROLE_ADMIN']);
        $token    = new UsernamePasswordToken($user, null, $firewall, ['ROLE_ADMIN', 'IS_AUTHENTICATED_FULLY']);

        $this->setAuthenticated($token, $firewall);
    }

    protected function loginRemembered()
    {
        $firewall = '2fas';
        $user     = new User('admin', 'adminpass', ['ROLE_ADMIN']);
        $token    = new RememberMeToken($user, $firewall, 'rememberme');

        $this->setAuthenticated($token, $firewall);
    }

    protected function loginWithTwoFAS()
    {
        $firewall = '2fas';
        $user     = new User('admin', 'adminpass', ['ROLE_ADMIN']);
        $token    = new TwoFactorToken($user, null, $firewall, ['ROLE_ADMIN', 'IS_AUTHENTICATED_FULLY']);

        $this->setAuthenticated($token, $firewall);
    }

    protected function setAuthenticated(AbstractToken $token, $firewall)
    {
        $session      = $this->container->get('session');
        $tokenStorage = $this->container->get('two_fas_two_factor.storage.token_storage');

        $tokenStorage->setToken($token);

        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        $this->twoFASUser
            ->setId('1')
            ->setUsername($token->getUser()->getUsername());

        $this->userRepository->add($this->twoFASUser);
    }

    /**
     * @param string    $series
     * @param string    $tokenValue
     * @param \DateTime $lastUsed
     */
    protected function generateRememberMeToken($series, $tokenValue, \DateTime $lastUsed)
    {
        $token = $this->getRememberMeToken($this->twoFASUser, $series, $tokenValue, $lastUsed);
        $this->twoFASUser->addToken($token);
        $this->tokenRepository->add($token);
    }

    /**
     * @param string $series
     * @param string $tokenValue
     */
    protected function generateCookie($series, $tokenValue)
    {
        $cookie = new Cookie(
            'TWOFAS_REMEMBERME['.$this->twoFASUser->getId().']',
            base64_encode(implode(':', [$series, $tokenValue])),
            time(),
            '/',
            '',
            false,
            true
        );

        $this->client->getCookieJar()->set($cookie);
    }

    protected function mockApi()
    {
        $this->api = $this
            ->getMockBuilder(TwoFAS::class)
            ->disableOriginalConstructor()
            ->setMethods(['requestAuth', 'requestAuthViaTotp', 'checkCode'])
            ->getMock();

        $this->api->setBaseUrl('http://localhost');

        $this->container->set('two_fas_two_factor.sdk.api', $this->api);
    }

    protected function mockIntegrationUserManager()
    {
        $this->integrationUserManager = $this
            ->getMockBuilder(IntegrationUserManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['findByExternalId', 'createUser', 'updateUser'])
            ->getMock();

        $this->integrationUserManager->method('findByExternalId')->willReturn($this->integrationUser);
        $this->container->set('two_fas_two_factor.util.integration_user_manager', $this->integrationUserManager);
    }

    protected function mockAuthenticationManager()
    {
        $this->authenticationManager = $this
            ->getMockBuilder(AuthenticationManager::class)
            ->setConstructorArgs([
                $this->container->get('two_fas_two_factor.proxy.api_provider'),
                $this->container->get('two_fas_two_factor.authentication_persister'),
                $this->container->get('two_fas_two_factor.object_manager'),
                $this->container->get('event_dispatcher'),
                $this->container->getParameter('two_fas_two_factor.block_user_login_in_minutes')
            ])
            ->setMethods(['getOpenAuthentications', 'openAuthentication', 'openTotpAuthentication', 'closeAuthentications', 'blockAuthentications'])
            ->getMock();
        $this->container->set('two_fas_two_factor.util.authentication_manager', $this->authenticationManager);
    }

    protected function mockOptionRepository()
    {
        $this->optionRepository = new InMemoryRepository(Option::class, 'id');

        $this->twoFASStatus = new Option();
        $this->twoFASStatus
            ->setName(OptionInterface::STATUS)
            ->setValue(true);

        $this->optionRepository->add($this->twoFASStatus);

        $optionPersister = new InMemoryObjectPersister($this->optionRepository);
        $this->container->set('two_fas_two_factor.option_persister', $optionPersister);
    }

    protected function mockTokenRepository()
    {
        $this->tokenRepository = new InMemoryRepository(TwoFASRememberMeToken::class, 'series');
        $tokenPersister        = new InMemoryObjectPersister($this->tokenRepository);
        $this->container->set('two_fas_two_factor.remember_me_persister', $tokenPersister);
    }

    protected function mockUserRepository()
    {
        $this->userRepository = new InMemoryRepository(UserEntity::class, 'id');
        $userPersister        = new InMemoryObjectPersister($this->userRepository);
        $this->container->set('two_fas_two_factor.user_persister', $userPersister);
    }

    protected function mockUserStorage()
    {
        $session = $this->getMockForAbstractClass(SessionInterface::class);
        $session->method('has')->willReturn(false);

        $userStorage = new UserSessionStorage(
            $session,
            $this->container->get('two_fas_two_factor.storage.token_storage'),
            $this->container->get('two_fas_two_factor.object_manager'),
            $this->container->get('two_fas_two_factor.util.user_manager'),
            $this->integrationUserManager
        );

        $this->container->set('two_fas_two_factor.storage.user_session_storage', $userStorage);
    }

    protected function mockObjectManager()
    {
        $objectManager = $this->getMockForAbstractClass(ObjectManager::class);
        $objectManager->method('merge')->willReturn($this->twoFASUser);

        $this->container->set('two_fas_two_factor.object_manager', $objectManager);
    }

    protected function mockTrustedDeviceVoter()
    {
        $this->trustedDeviceVoter = new TrustedDeviceVoter(
            $this->container->get('two_fas_two_factor.storage.user_session_storage'),
            $this->container->get('two_fas_two_factor.object_manager')
        );

        $this->container->set('two_fas_two_factor.security_voter.trusted_device_voter', $this->trustedDeviceVoter);
    }

    protected function mockConfigurationChecker()
    {
        $configurationChecker = new ConfigurationChecker(
            $this->container->get('two_fas_two_factor.option_persister'),
            $this->container->get('two_fas_two_factor.storage.user_session_storage'),
            new EmptyCacheStorage()
        );

        $this->container->set('two_fas_two_factor.util.configuration_checker', $configurationChecker);
    }

    /**
     * @param string $channel
     *
     * @return Authentication
     */
    protected function getAuthentication($channel)
    {
        $authentication = new Authentication();
        $authentication
            ->setId(uniqid())
            ->setType($channel)
            ->setCreatedAt(new \DateTime())
            ->setValidTo((new \DateTime())->add(new \DateInterval('PT15M')));
        return $authentication;
    }

    /**
     * @param UserInterface $user
     * @param string        $series
     * @param string        $tokenValue
     * @param \DateTime     $lastUsed
     *
     * @return TwoFASRememberMeToken
     */
    protected function getRememberMeToken(UserInterface $user, $series, $tokenValue, \DateTime $lastUsed)
    {
        $token = new TwoFASRememberMeToken();
        $token
            ->setSeries($series)
            ->setValue($tokenValue)
            ->setClass(User::class)
            ->setUser($user)
            ->setBrowser('an unknown browser')
            ->setLastUsedAt($lastUsed);

        return $token;
    }

    /**
     * @param string $channel
     */
    protected function openAuthentication($channel)
    {
        $authentication = $this->getAuthentication($channel);
        $authentication->setUser($this->twoFASUser);

        $this->authenticationManager->method('getOpenAuthentications')->willReturn(new ArrayCollection([$authentication]));
    }

    protected function openTotpAuthentication()
    {
        $authentication = $this->getAuthentication(Methods::TOTP);
        $authentication->setUser($this->twoFASUser);

        $this->authenticationManager->method('openTotpAuthentication')->willReturn($authentication);
    }

    protected function checkCodeRejectedCanRetry()
    {
        $this->api->method('checkCode')->willReturn(new RejectedCodeCanRetry([]));
    }

    protected function checkCodeRejectedCannotRetry()
    {
        $this->api->method('checkCode')->willReturn(new RejectedCodeCannotRetry([]));
    }

    protected function checkCodeAccepted()
    {
        $this->api->method('checkCode')->willReturn(new AcceptedCode([]));
    }
}
