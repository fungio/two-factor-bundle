<?php

namespace Fungio\TwoFactorBundle\Util;

use TwoFAS\Api\QrCodeGenerator;

/**
 * Generates Qr Code includes TOTP(Time-based One-time Password Algorithm) secret.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Util
 */
class TotpQrCodeGenerator implements TotpQrCodeGeneratorInterface
{
    /**
     * @var QrCodeGenerator
     */
    private $qrClient;

    /**
     * @param QrCodeGenerator $qrClient
     */
    public function __construct(QrCodeGenerator $qrClient)
    {
        $this->qrClient = $qrClient;
    }

    /**
     * @inheritdoc
     */
    public function generate($totpSecret, $mobileSecret, $description)
    {
        $message = "otpauth://totp/" . urlencode($description) . "?secret={$totpSecret}&mobile_secret={$mobileSecret}";

        return $this->qrClient->generateBase64($message);
    }
}