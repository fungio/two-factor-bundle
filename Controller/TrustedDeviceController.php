<?php

namespace TwoFAS\TwoFactorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Delete logged user trusted device from list.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Controller
 * @internal This controller is not public (action is forwarded from dashboard)
 */
class TrustedDeviceController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function removeAction(Request $request)
    {
        /** @var ObjectPersisterInterface $tokenPersister */
        $tokenPersister = $this->get('two_fas_two_factor.remember_me_persister');

        if (!$this->isCsrfTokenValid('twofas_csrf_token', $request->get('_token'))) {
            throw new AccessDeniedHttpException($this->trans('general.denied_action'));
        }

        $device = $tokenPersister->getRepository()->find($request->get('id'));

        if (is_null($device)) {
            throw $this->createNotFoundException($this->trans('trusted_devices.remove.not_found'));
        }

        if (!$this->isGranted('remove', $device)) {
            throw new AccessDeniedHttpException($this->trans('general.denied_action'));
        }

        $tokenPersister->removeEntity($device);

        return new Response();
    }
}