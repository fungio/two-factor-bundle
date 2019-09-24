<?php

namespace Fungio\TwoFactorBundle\DependencyInjection\Factory;

use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

/**
 * Contract for remember me factories.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\DependencyInjection\Factory
 */
interface RememberMeServicesFactoryInterface
{
    /**
     * @return RememberMeServicesInterface
     */
    public function createInstance();
}