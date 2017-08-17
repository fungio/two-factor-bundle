<?php

namespace TwoFAS\TwoFactorBundle\Tests\Storage;

use TwoFAS\Encryption\AESGeneratedKey;
use TwoFAS\Encryption\AESKey;
use TwoFAS\TwoFactorBundle\Storage\EncryptionStorage;

class EncryptionStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testStorage()
    {
        $storage = new EncryptionStorage(base64_encode('test_key'));

        $this->assertInstanceOf(AESKey::class, $storage->retrieveKey());
    }

    public function testCannotStore()
    {
        $this->setExpectedException('BadMethodCallException', 'This method should not be used.');

        $storage = new EncryptionStorage(base64_encode('test_key'));

        $storage->storeKey(new AESGeneratedKey());
    }
}
