<?php

namespace TwoFAS\TwoFactorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TwoFAS\TwoFactorBundle\DependencyInjection\Compiler\CachePass;
use TwoFAS\TwoFactorBundle\DependencyInjection\Compiler\EntityPass;
use TwoFAS\TwoFactorBundle\DependencyInjection\Compiler\RememberMePass;

/**
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle
 */
class TwoFASTwoFactorBundle extends Bundle
{
    const VERSION = '1.0.0';

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
