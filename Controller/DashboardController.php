<?php

namespace TwoFAS\TwoFactorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use TwoFAS\Api\Exception\Exception as ApiException;

/**
 * Dashboard for Two FAS (2FA status, enabled channels, list of trusted devices).
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Controller
 */
class DashboardController extends Controller
{
    /**
     * @return Response
     *
     * @throws ApiException
     */
    public function indexAction()
    {
        $configuration   = $this->get('two_fas_two_factor.util.configuration_checker');
        $user            = $this->getTwoFASUser();
        $integrationUser = $this->getIntegrationUser();

        return $this->render('TwoFASTwoFactorBundle:Dashboard:index.html.twig', [
            'integration_user' => $integrationUser,
            'status'           => $configuration->isTwoFASEnabled(),
            'channels'         => $user->getChannels(),
            'trusted_devices'  => $this->getTrustedDevices()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function removeDeviceAction(Request $request)
    {
        $response   = $this->forward('TwoFASTwoFactorBundle:TrustedDevice:remove', [], [
            'id'     => $request->get('id'),
            '_token' => $request->get('_token')
        ]);

        if ($response->isSuccessful()) {
            $this->addFlash('success', $this->trans('trusted_devices.remove.success'));
        } else {
            $this->addFlash('danger', $this->trans('trusted_devices.remove.error'));
        }

        return $this->redirectToRoute('twofas_index');
    }
}
