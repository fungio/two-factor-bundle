<?php

namespace Fungio\TwoFactorBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;

/**
 * Token used after login on trusted device.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Security\Token
 */
class TwoFactorRememberMeToken extends RememberMeToken
{

}