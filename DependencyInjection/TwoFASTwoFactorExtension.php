<?php

namespace TwoFAS\TwoFactorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TwoFASTwoFactorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        if ('custom' !== $config['db_driver']) {
            $loader->load(sprintf('%s.xml', $config['db_driver']));
        }

        $container->setParameter($this->getAlias() . '.db_driver', $config['db_driver']);
        $container->setParameter($this->getAlias() . '.account_name', $config['account_name']);
        $container->setParameter($this->getAlias() . '.encryption_key', $config['encryption_key']);
        $container->setParameter($this->getAlias() . '.firewalls', $config['firewalls']);
        $container->setParameter($this->getAlias() . '.api_url', $config['api_url']);
        $container->setParameter($this->getAlias() . '.account_url', $config['account_url']);
        $container->setParameter($this->getAlias() . '.block_user_login_in_minutes', $config['block_user_login_in_minutes']);
        $container->setParameter($this->getAlias() . '.remember_me.lifetime', $config['remember_me']['lifetime']);
        $container->setParameter($this->getAlias() . '.cache.enabled', $config['cache']['enabled']);
        $container->setParameter($this->getAlias() . '.cache.service', $config['cache']['service']);
        $container->setParameter($this->getAlias() . '.entities.option_class', $config['entities']['option_class']);
        $container->setParameter($this->getAlias() . '.entities.user_class', $config['entities']['user_class']);
        $container->setParameter($this->getAlias() . '.entities.authentication_class', $config['entities']['authentication_class']);
        $container->setParameter($this->getAlias() . '.entities.remember_me_class', $config['entities']['remember_me_class']);

        $container->setAlias($this->getAlias() . '.option_persister', $config['persisters']['option_persister']);
        $container->setAlias($this->getAlias() . '.user_persister', $config['persisters']['user_persister']);
        $container->setAlias($this->getAlias() . '.authentication_persister', $config['persisters']['authentication_persister']);
        $container->setAlias($this->getAlias() . '.remember_me_persister', $config['persisters']['remember_me_persister']);

        $loader->load('services.xml');
        $loader->load('listeners.xml');
        $loader->load('security.xml');
    }
}
