<?php

namespace Fungio\TwoFactorBundle\Storage;

use DomainException;

/**
 * Throws if token in storage is not valid.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Storage
 */
class InvalidTokenException extends DomainException
{

}