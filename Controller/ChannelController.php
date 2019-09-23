<?php

namespace Fungio\TwoFactorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Fungio\TwoFactorBundle\Event\ChannelStatusChangedEvent;
use Fungio\TwoFactorBundle\Event\FungioEvents;
use TwoFAS\Api\Methods;
use TwoFAS\Api\Exception\Exception as ApiException;

/**
 * Manage 2FAS channels.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Controller
 */
class ChannelController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ApiException
     */
    public function enableAction(Request $request)
    {
        $channel = $request->request->get('channel');

        if (!$this->isChannelValid($channel)) {
            $this->addFlash('danger', $this->trans('channel.not_valid'));
            return $this->redirectToRoute('fungio_index');
        }

        if (!$this->canEnable($channel)) {
            $this->addFlash('danger', $this->trans('channel.cannot_enable'));
            return $this->redirectToRoute('fungio_index');
        }

        $this->changeChannelStatus($channel, true);

        return $this->redirectToRoute('fungio_index');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function disableAction(Request $request)
    {
        $channel = $request->request->get('channel');

        if (!$this->isChannelValid($channel)) {
            $this->addFlash('danger', $this->trans('channel.not_valid'));
            return $this->redirectToRoute('fungio_index');
        }

        $this->changeChannelStatus($channel, false);

        return $this->redirectToRoute('fungio_index');
    }

    /**
     * @param string $channel
     * @param bool   $status
     */
    protected function changeChannelStatus($channel, $status)
    {
        $user            = $this->getFungioUser();
        $userStorage     = $this->get('fungio_two_factor.storage.user_session_storage');
        $eventDispatcher = $this->get('event_dispatcher');

        if (true === (bool) $status) {
            $user->enableChannel($channel);
            $userStorage->updateUser($user);
            $eventDispatcher->dispatch(FungioEvents::CHANNEL_ENABLED, new ChannelStatusChangedEvent($user, $channel));
            $this->addFlash('success', $this->trans('channel.success_enabled'));
        } else {
            $user->disableChannel($channel);
            $userStorage->updateUser($user);
            $this->addFlash('success', $this->trans('channel.success_disabled'));
        }
    }

    /**
     * @param string $channel
     *
     * @return bool
     *
     * @throws ApiException
     */
    protected function canEnable($channel)
    {
        $integrationUser = $this->getIntegrationUser();

        switch ($channel) {
            case Methods::TOTP:
                return null !== $integrationUser->getTotpSecret();
            default:
                return false;
        }
    }

    /**
     * @param string $channel
     *
     * @return bool
     */
    protected function isChannelValid($channel)
    {
        return in_array($channel, Methods::getAllowedMethods());
    }
}
