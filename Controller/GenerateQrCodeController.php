<?php

namespace Fungio\TwoFactorBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Fungio\TwoFactorBundle\Util\TotpQrCodeGeneratorInterface;

/**
 * Generate QR Code includes TOTP and Mobile Secret.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Controller
 */
class GenerateQrCodeController extends Controller
{
    /**
     * @param Request $request
     * @param string  $totpSecret
     * @param string  $mobileSecret
     *
     * @return Response
     */
    public function generateAction(Request $request, $totpSecret, $mobileSecret)
    {
        $qrCode = $this->getQrCode($request, $totpSecret, $mobileSecret);

        return $this->render('@FungioTwoFactor/GenerateQrCode/generate.html.twig', [
            'qr_code'     => $qrCode,
            'totp_secret' => $totpSecret
        ]);
    }

    /**
     * @param Request $request
     * @param string  $totpSecret
     * @param string  $mobileSecret
     *
     * @return JsonResponse
     */
    public function generateJsonAction(Request $request, $totpSecret, $mobileSecret)
    {
        $qrCode = $this->getQrCode($request, $totpSecret, $mobileSecret);

        return new JsonResponse([
            'qr_code'     => $qrCode,
            'totp_secret' => $totpSecret
        ]);
    }

    /**
     * @param Request $request
     * @param string  $totpSecret
     * @param string  $mobileSecret
     *
     * @return string
     */
    protected function getQrCode(Request $request, $totpSecret, $mobileSecret)
    {
        /** @var TotpQrCodeGeneratorInterface $qrCodeGenerator */
        $qrCodeGenerator = $this->get('fungio_two_factor.util.totp_qr_code_generator');
        $description     = $this->getParameter('fungio_two_factor.account_name');

        if (is_null($description)) {
            $description = 'Symfony ' . Kernel::VERSION . '@' . $request->getHttpHost();
        }

        return $qrCodeGenerator->generate($totpSecret, $mobileSecret, $description);
    }
}
