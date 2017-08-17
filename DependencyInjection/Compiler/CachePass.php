<?php

namespace TwoFAS\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TwoFAS\TwoFactorBundle\Cache\EmptyCacheStorage;

/**
 * Compile classes used for cache some of 2FAS values stored in db.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\DependencyInjection\Compiler
 */
class CachePass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $cacheEnabled = $container->getParameter('two_fas_two_factor.cache.enabled');

        if (!$cacheEnabled) {
            $container->register('two_fas_two_factor.cache.storage', EmptyCacheStorage::class);
            return;
        }

        $container->setAlias(
            'two_fas_two_factor.cache.storage',
            new Alias($container->getParameter('two_fas_two_factor.cache.service'))
        );
    }
}