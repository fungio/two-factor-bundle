<?php

namespace TwoFAS\TwoFactorBundle\Storage;

use DomainException;

/**
 * Throws if token in storage is not valid.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Storage
 */
class InvalidTokenException extends DomainException
{

}