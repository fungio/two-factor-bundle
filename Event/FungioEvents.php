<?php

namespace Fungio\TwoFactorBundle\Event;

/**
 * All events dispatched by TwoFactorBundle
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Event
 */
final class FungioEvents
{
    const CODE_ACCEPTED              = 'fungio_two_factor.code.accepted';
    const CODE_REJECTED_CAN_RETRY    = 'fungio_two_factor.code.rejected_can_retry';
    const CODE_REJECTED_CANNOT_RETRY = 'fungio_two_factor.code.rejected_cannot_retry';
    const CODE_CHECK_SUCCESSFUL      = 'fungio_two_factor.code.check_successful';

    const CHANNEL_ENABLED = 'fungio_two_factor.channel.enabled';

    const INTEGRATION_USER_CONFIGURATION_COMPLETE_TOTP = 'fungio_two_factor.integration_user.configuration.complete.totp';
}