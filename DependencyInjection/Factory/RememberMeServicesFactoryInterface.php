<?php

namespace TwoFAS\TwoFactorBundle\DependencyInjection\Factory;

use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

/**
 * Contract for remember me factories.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\DependencyInjection\Factory
 */
interface RememberMeServicesFactoryInterface
{
    /**
     * @return RememberMeServicesInterface
     */
    public function createInstance();
}