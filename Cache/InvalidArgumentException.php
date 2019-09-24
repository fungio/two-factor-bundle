<?php

namespace Fungio\TwoFactorBundle\Cache;

use InvalidArgumentException as BaseInvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

/**
 * Exception thrown if key or value sends to cache is not valid.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Cache
 */
class InvalidArgumentException extends BaseInvalidArgumentException implements PsrInvalidArgumentException
{

}