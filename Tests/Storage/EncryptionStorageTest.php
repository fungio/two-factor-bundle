<?php

namespace TwoFAS\TwoFactorBundle\Tests\Storage;

use TwoFAS\Encryption\AESKey;
use TwoFAS\TwoFactorBundle\Storage\EncryptionStorage;

class EncryptionStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testStorage()
    {
        $storage = new EncryptionStorage(base64_encode('test_key'));

        $this->assertInstanceOf(AESKey::class, $storage->retrieve());
    }
}
