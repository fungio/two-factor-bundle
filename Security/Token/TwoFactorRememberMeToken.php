<?php

namespace TwoFAS\TwoFactorBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;

/**
 * Token used after login on trusted device.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Security\Token
 */
class TwoFactorRememberMeToken extends RememberMeToken
{

}