<?php

namespace TwoFAS\TwoFactorBundle\Util;

/**
 * Contract for Totp Qr Code Generators.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Util
 */
interface TotpQrCodeGeneratorInterface
{
    /**
     * @param string $totpSecret
     * @param string $mobileSecret
     * @param string $description
     *
     * @return string
     */
    public function generate($totpSecret, $mobileSecret, $description);
}