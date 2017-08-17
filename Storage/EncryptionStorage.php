<?php

namespace TwoFAS\TwoFactorBundle\Storage;

use BadMethodCallException;
use TwoFAS\Encryption\AESKey;
use TwoFAS\Encryption\Interfaces\Key;
use TwoFAS\Encryption\Interfaces\KeyStorage;

/**
 * Store encryption key for TwoFAS Data.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Storage\Encryption
 */
class EncryptionStorage implements KeyStorage
{
    /**
     * @var string
     */
    private $base64Key;

    /**
     * @param string $base64Key
     */
    public function __construct($base64Key)
    {
        $this->base64Key = $base64Key;
    }

    /**
     * @inheritDoc
     */
    public function storeKey(Key $key)
    {
        throw new BadMethodCallException('This method should not be used.');
    }

    /**
     * @inheritDoc
     */
    public function retrieveKey()
    {
        return new AESKey(base64_decode($this->base64Key));
    }
}