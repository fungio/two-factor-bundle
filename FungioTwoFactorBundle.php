<?php

namespace Fungio\TwoFactorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Fungio\TwoFactorBundle\DependencyInjection\Compiler\CachePass;
use Fungio\TwoFactorBundle\DependencyInjection\Compiler\EntityPass;
use Fungio\TwoFactorBundle\DependencyInjection\Compiler\RememberMePass;

/**
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle
 */
class FungioTwoFactorBundle extends Bundle
{
    const VERSION = '1.0.1';

    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CachePass());
        $container->addCompilerPass(new EntityPass());
        $container->addCompilerPass(new RememberMePass());
    }
}
