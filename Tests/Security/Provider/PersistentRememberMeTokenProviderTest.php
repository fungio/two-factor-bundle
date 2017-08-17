<?php

namespace TwoFAS\TwoFactorBundle\Tests\Security\Provider;

use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentToken;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use TwoFAS\TwoFactorBundle\Model\Entity\RememberMeToken;
use TwoFAS\TwoFactorBundle\Model\Entity\RememberMeTokenInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\User;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepository;
use TwoFAS\TwoFactorBundle\Model\Persister\InMemoryRepositoryInterface;
use TwoFAS\TwoFactorBundle\Security\Provider\PersistentRememberMeTokenProvider;
use TwoFAS\TwoFactorBundle\Util\BrowserParser;

class PersistentRememberMeTokenProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PersistentRememberMeTokenProvider
     */
    private $persistentProvider;

    /**
     * @var InMemoryRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var InMemoryRepositoryInterface
     */
    private $userRepository;

    /**
     * @var string
     */
    private $browserVersion = 'Chrome Dev 55.0.2883.95 on OS X El Capitan 10.11';

    public function setUp()
    {
        parent::setUp();

        $this->userRepository = new InMemoryRepository(User::class, 'id');
        $userPersister        = new InMemoryObjectPersister($this->userRepository);

        $this->tokenRepository = new InMemoryRepository(RememberMeToken::class, 'series');
        $tokenPersister        = new InMemoryObjectPersister($this->tokenRepository);

        $this->persistentProvider = new PersistentRememberMeTokenProvider($userPersister, $tokenPersister, new BrowserParser($this->browserVersion));
    }

    public function testLoadToken()
    {
        $rememberMeToken = $this->getRememberMeToken();
        $expectedToken   = $this->getPersistentToken($rememberMeToken);
        $actualToken     = $this->persistentProvider->loadTokenBySeries($rememberMeToken->getSeries());

        $this->assertEquals($expectedToken, $actualToken);
    }

    public function testCannotLoadTokenWhenNotExists()
    {
        $this->setExpectedException(TokenNotFoundException::class);
        $this->persistentProvider->loadTokenBySeries('FAD*&^==');
    }

    public function testCreateNewToken()
    {
        $rememberMeToken = $this->getRememberMeToken();
        $expectedToken   = $this->getPersistentToken($rememberMeToken);
        $this->persistentProvider->createNewToken($expectedToken);
        $actualToken = $this->persistentProvider->loadTokenBySeries($rememberMeToken->getSeries());

        $this->assertEquals($expectedToken, $actualToken);
    }

    public function testUpdateToken()
    {
        $rememberMeToken = $this->getRememberMeToken();
        $this->tokenRepository->add($rememberMeToken);

        $value = 'BHk455&^432JKLF=';
        $date  = new \DateTime('2016-12-31 23:59:59');

        $this->persistentProvider->updateToken($rememberMeToken->getSeries(), $value, $date);
        $actualToken = $this->persistentProvider->loadTokenBySeries($rememberMeToken->getSeries());

        $this->assertInstanceOf(PersistentToken::class, $actualToken);
        $this->assertEquals($rememberMeToken->getSeries(), $actualToken->getSeries());
        $this->assertEquals($value, $actualToken->getTokenValue());
        $this->assertEquals($date, $actualToken->getLastUsed());
    }

    public function testUpdateWhenTokenNotExists()
    {
        $this->setExpectedException(TokenNotFoundException::class, 'No token found.');
        $this->persistentProvider->updateToken('foo', 'bar', new \DateTime());
    }

    public function testDeleteToken()
    {
        $rememberMeToken = $this->getRememberMeToken();

        $this->persistentProvider->deleteTokenBySeries($rememberMeToken->getSeries());

        $this->assertFalse($this->tokenRepository->contains($rememberMeToken));
    }

    /**
     * @return RememberMeToken
     */
    protected function getRememberMeToken()
    {
        $user = new User();
        $user->setUsername('tom');

        $rememberMeToken = new RememberMeToken();
        $rememberMeToken
            ->setSeries('SSycpnsmX+v+gZ2xaWDM9N7lV1bmQBc1oLNqeWchH5gb/2E1OtpUajZg9bwZJZQlBWroHtcoDiEVnTddxlqfFw==')
            ->setValue('lwB62gghcopQMyso1WyLHj0X4+xok/vk9k8MSTzV9wBS9ujGX5vcpDIiUEDH6mp5n+WO3hPg0CPGMgaoeNDCUw==')
            ->setClass('AppBundle\Entity\User')
            ->setUser($user)
            ->setLastUsedAt(new \DateTime('2017-01-01 12:00:00'))
            ->setBrowser($this->browserVersion);

        $user->addToken($rememberMeToken);

        $this->tokenRepository->add($rememberMeToken);
        $this->userRepository->add($user);

        return $rememberMeToken;
    }

    /**
     * @param RememberMeTokenInterface $token
     *
     * @return PersistentToken
     */
    protected function getPersistentToken(RememberMeTokenInterface $token)
    {
        return new PersistentToken(
            $token->getClass(),
            $token->getUser()->getUsername(),
            $token->getSeries(),
            $token->getValue(),
            $token->getLastUsedAt()
        );
    }
}
