<?php

namespace Fungio\TwoFactorBundle\Tests\Storage;

use Fungio\Encryption\AESKey;
use Fungio\TwoFactorBundle\Storage\EncryptionStorage;

class EncryptionStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testStorage()
    {
        $storage = new EncryptionStorage(base64_encode('test_key'));

        $this->assertInstanceOf(AESKey::class, $storage->retrieve());
    }
}
