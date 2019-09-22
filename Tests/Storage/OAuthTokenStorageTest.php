<?php

namespace Fungio\TwoFactorBundle\Tests\Storage;

use Fungio\Encryption\Cryptographer;
use Fungio\TwoFactorBundle\Model\Entity\Option;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryObjectPersister;
use Fungio\TwoFactorBundle\Model\Persister\InMemoryRepository;
use Fungio\TwoFactorBundle\Storage\EncryptionStorage;
use Fungio\TwoFactorBundle\Storage\OAuthTokenStorage;
use Fungio\Account\OAuth\Token;
use Fungio\Account\OAuth\TokenNotFoundException;

class OAuthTokenStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OAuthTokenStorage
     */
    private $tokenStorage;

    /**
     * @var Cryptographer
     */
    private $cryptographer;

    /**
     * @var InMemoryRepository
     */
    private $optionRepository;

    public function setUp()
    {
        parent::setUp();

        $this->cryptographer    = Cryptographer::getInstance(new EncryptionStorage(base64_encode('foobar')));
        $this->optionRepository = new InMemoryRepository(Option::class, 'id');
        $optionPersister        = new InMemoryObjectPersister($this->optionRepository);
        $this->tokenStorage     = new OAuthTokenStorage($optionPersister, $this->cryptographer);
    }

    public function testStoreToken()
    {
        $token = new Token('setup', 'fhsdk5827g', 1);
        $this->tokenStorage->storeToken($token);

        /** @var OptionInterface $option */
        $option = $this->optionRepository->getItems()->first();

        $this->assertCount(1, $this->optionRepository->findAll());
        $this->assertInstanceOf(OptionInterface::class, $option);
        $this->assertEquals('oauth_scope_setup', $option->getName());
        $this->assertNotNull($option->getValue());
    }

    public function testUpdateToken()
    {
        $token = new Token('setup', 'fhsdk5827g', 1);
        $this->tokenStorage->storeToken($token);

        $token2 = new Token('setup', 'jkhfgds770', 2);
        $this->tokenStorage->storeToken($token2);

        /** @var OptionInterface $option */
        $option = $this->optionRepository->getItems()->first();

        $this->assertCount(1, $this->optionRepository->findAll());
        $this->assertInstanceOf(OptionInterface::class, $option);
        $this->assertEquals('oauth_scope_setup', $option->getName());
        $this->assertNotNull($option->getValue());

        $actualToken = $this->tokenStorage->retrieveToken('setup');
        $this->assertEquals($token2, $actualToken);
    }

    public function testStoreTwoTokens()
    {
        $token  = new Token('setup', 'fhsdk5827g', 1);
        $token2 = new Token('wordpress', 'fds09545', 2);
        $this->tokenStorage->storeToken($token);
        $this->tokenStorage->storeToken($token2);

        $this->assertCount(2, $this->optionRepository->findAll());
    }

    public function testRetrieveToken()
    {
        $token = new Token('setup', 'fhsdk5827g', 1);
        $this->tokenStorage->storeToken($token);
        $actualToken = $this->tokenStorage->retrieveToken('setup');

        $this->assertEquals($token, $actualToken);
    }

    public function testCannotRetrieveTokenIfNotExist()
    {
        $this->setExpectedException(TokenNotFoundException::class, 'Token: "setup" not found in storage.');
        $this->tokenStorage->retrieveToken('setup');
    }

    public function testCannotRetrieveTokenIfIsNotValid()
    {
        $this->setExpectedException(\RuntimeException::class, 'Invalid token in storage.');
        $option = new Option();
        $option
            ->setName(OptionInterface::OAUTH_SCOPE . '_setup')
            ->setValue($this->cryptographer->encrypt(serialize('foobar')));

        $this->optionRepository->add($option);
        $this->tokenStorage->retrieveToken('setup');
    }
}
