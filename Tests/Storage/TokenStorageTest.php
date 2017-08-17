<?php

namespace TwoFAS\TwoFactorBundle\Tests\Storage;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use TwoFAS\TwoFactorBundle\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as BaseTokenStorage;

class TokenStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorage
     */
    private $storage;

    public function setUp()
    {
        parent::setUp();

        $this->storage = new TokenStorage(new BaseTokenStorage());
    }

    public function testUsernameToken()
    {
        $this->storage->setToken(new UsernamePasswordToken('admin', 'adminpass', 'foo'));

        $token = $this->storage->getToken();

        $this->assertTrue($this->storage->isValid($token));
    }

    public function testRememberMeToken()
    {
        $user = $this->getMockBuilder(UserInterface::class)->setMethods(['getRoles'])->getMockForAbstractClass();
        $user->method('getRoles')->willReturn([]);

        $this->storage->setToken(new RememberMeToken($user, 'foo', 'bar'));

        $token = $this->storage->getToken();

        $this->assertTrue($this->storage->isValid($token));
    }

    public function testNullAsToken()
    {
        $this->assertFalse($this->storage->isValid(null));
    }

    public function testInvalidToken()
    {
        $this->storage->setToken(new AnonymousToken('foo', 'admin'));

        $token = $this->storage->getToken();

        $this->assertFalse($this->storage->isValid($token));
    }
}
