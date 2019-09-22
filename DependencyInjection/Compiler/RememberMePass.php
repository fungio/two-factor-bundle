<?php

namespace Fungio\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compile services used for Two FAS Remember Me functionality based on original from Symfony.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\DependencyInjection\Compiler
 */
class RememberMePass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $secret        = $container->getParameter('two_fas_two_factor.encryption_key');
        $firewalls     = $this->getFirewalls($container);
        $userProviders = $this->getUserProviders($container);
        $authProviders = $this->getAuthProviders($container, $firewalls, $secret);

        // Remember me service factory
        $rememberMeServiceFactory = $container->getDefinition('two_fas_two_factor.dependency_injection_factory.persistent_remember_me_services_factory');
        $rememberMeServiceFactory->replaceArgument(0, $userProviders);
        $rememberMeServiceFactory->replaceArgument(1, $secret);
        $rememberMeServiceFactory->replaceArgument(6, ['lifetime' => $container->getParameter('two_fas_two_factor.remember_me.lifetime')]);

        // Authentication manager
        $authenticationManager = $container->getDefinition('two_fas_two_factor.authentication.manager');
        $authenticationManager->replaceArgument(0, $authProviders);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function getFirewalls(ContainerBuilder $container)
    {
        $firewalls = $container->getParameter('two_fas_two_factor.firewalls');

        if (0 === count($firewalls)) {
            throw new \RuntimeException('You must configure at least one firewall.');
        }

        return $firewalls;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function getUserProviders(ContainerBuilder $container)
    {
        $config        = $container->getExtensionConfig('security');
        $userProviders = [];

        for ($i = 0; $i < count($config); $i++) {

            if (!array_key_exists('providers', $config[$i])) {
                continue;
            }

            foreach ($config[$i]['providers'] as $providerName => $providerConfig) {
                $userProviders[] = $container->findDefinition('security.user.provider.concrete.' . $providerName);
            }
        }

        if (0 === count($userProviders)) {
            throw new \RuntimeException('You must configure at least one user provider.');
        }

        return $userProviders;
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $firewalls
     * @param string           $secret
     *
     * @return array
     */
    private function getAuthProviders(ContainerBuilder $container, array $firewalls, $secret)
    {
        $authProviders = [];

        foreach ($firewalls as $id) {
            $authProvider = $container->getDefinition('two_fas_two_factor.authentication.provider.remember_me');
            $authProvider
                ->replaceArgument(0, new Reference('security.user_checker.' . $id))
                ->replaceArgument(1, $secret)
                ->replaceArgument(2, $id);

            $authProviders[] = $authProvider;
        }

        return $authProviders;
    }
}