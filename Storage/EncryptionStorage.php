<?php

namespace TwoFAS\TwoFactorBundle\Storage;

use TwoFAS\Encryption\AESKey;
use TwoFAS\Encryption\Interfaces\ReadKey;

/**
 * Store encryption key for TwoFAS Data.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Storage\Encryption
 */
class EncryptionStorage implements ReadKey
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
    public function retrieve()
    {
        return new AESKey(base64_decode($this->base64Key));
    }
}
