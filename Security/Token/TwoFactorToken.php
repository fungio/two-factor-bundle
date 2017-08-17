<?php

namespace TwoFAS\TwoFactorBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Token used after successful authentication with 2FAS.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Security\Token
 */
class TwoFactorToken extends UsernamePasswordToken
{

}