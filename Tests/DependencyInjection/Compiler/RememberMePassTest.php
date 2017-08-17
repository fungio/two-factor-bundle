<?php

namespace TwoFAS\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\LdapUserProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TwoFAS\TwoFactorBundle\DependencyInjection\Compiler\RememberMePass;
use TwoFAS\TwoFactorBundle\DependencyInjection\Factory\PersistentRememberMeServicesFactory;

class RememberMePassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RememberMePass
     */
    private $pass;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    public function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->pass             = new RememberMePass($this->containerBuilder);

        $this->containerBuilder->setParameter('two_fas_two_factor.encryption_key', 'foo');
    }

    public function testNoFirewallsConfigured()
    {
        $this->setExpectedException(\RuntimeException::class, 'You must configure at least one firewall.');

        $this->containerBuilder->setParameter('two_fas_two_factor.firewalls', []);

        $this->pass->process($this->containerBuilder);
    }

    public function testNoProvidersConfigured()
    {
        $this->setExpectedException(\RuntimeException::class, 'You must configure at least one user provider.');

        $this->setFirewalls();

        $this->pass->process($this->containerBuilder);
    }

    public function testDefinitions()
    {
        $this->prepareConfig();

        $this->pass->process($this->containerBuilder);

        $authenticationManager    = $this->containerBuilder->getDefinition('two_fas_two_factor.authentication.manager');
        $rememberMeServiceFactory = $this->containerBuilder->getDefinition('two_fas_two_factor.dependency_injection_factory.persistent_remember_me_services_factory');

        $authenticationProviders = $authenticationManager->getArgument(0);
        $userProviders           = $rememberMeServiceFactory->getArgument(0);

        $this->assertCount(2, $authenticationProviders);
        $this->assertCount(5, $userProviders);

        /** @var Definition $authenticationProvider */
        foreach ($authenticationProviders as $authenticationProvider) {
            /** @var Reference $userChecker */
            $userChecker = $authenticationProvider->getArgument(0);
            $this->assertContains($userChecker->__toString(), ['security.user_checker.main', 'security.user_checker.2fas']);
            $this->assertEquals('foo', $authenticationProvider->getArgument(1));
            $this->assertContains($authenticationProvider->getArgument(2), ['2fas', 'main']);
        }

        $this->assertContains($this->containerBuilder->findDefinition('security.user.provider.concrete.in_memory'), $userProviders);
        $this->assertContains($this->containerBuilder->findDefinition('security.user.provider.concrete.my_chain_provider'), $userProviders);
        $this->assertContains($this->containerBuilder->findDefinition('security.user.provider.concrete.my_ldap_provider'), $userProviders);
        $this->assertContains($this->containerBuilder->findDefinition('security.user.provider.concrete.my_db_provider'), $userProviders);
        $this->assertContains($this->containerBuilder->findDefinition('security.user.provider.concrete.my_some_custom_provider'), $userProviders);
    }

    private function prepareConfig()
    {
        $this->setFirewalls();
        $this->containerBuilder->prependExtensionConfig('security', $this->prepareSecurityConfig());

        $tokenStorageReference  = new Reference('two_fas_two_factor.storage.token_storage');
        $tokenProviderReference = new Reference('two_fas_two_factor.security.persistent_remember_me_token_provider');
        $rememberMeService      = new Reference('two_fas_two_factor.storage.user_session_storage');
        $loggerReference        = new Reference('monolog.logger');

        $managerDefinition            = new Definition(AuthenticationProviderManager::class, [[], true]);
        $rememberMeProviderDefinition = new Definition(RememberMeAuthenticationProvider::class, [null, '', '']);
        $rememberMeFactoryDefinition  = new Definition(PersistentRememberMeServicesFactory::class, [
            [],
            '',
            $tokenStorageReference,
            $tokenProviderReference,
            $rememberMeService,
            $loggerReference,
            []
        ]);
        $inMemoryUserProvider         = new Definition(InMemoryUserProvider::class, [[]]);
        $chainUserProvider            = new Definition(ChainUserProvider::class, [[]]);
        $ldapUserProvider             = new Definition(LdapUserProvider::class, [$this->getMockForAbstractClass(LdapClientInterface::class), 'foo']);
        $entityUserProvider           = new Definition(EntityUserProvider::class, [$this->getMockForAbstractClass(ManagerRegistry::class), 'foo']);
        $customMockProvider           = $this->getMockForAbstractClass(UserProviderInterface::class);
        $customProvider               = new Definition(get_class($customMockProvider));

        $this->containerBuilder->setDefinition('two_fas_two_factor.authentication.manager', $managerDefinition);
        $this->containerBuilder->setDefinition('two_fas_two_factor.authentication.provider.remember_me', $rememberMeProviderDefinition);
        $this->containerBuilder->setDefinition('two_fas_two_factor.dependency_injection_factory.persistent_remember_me_services_factory', $rememberMeFactoryDefinition);
        $this->containerBuilder->setDefinition('security.user.provider.concrete.in_memory', $inMemoryUserProvider);
        $this->containerBuilder->setDefinition('security.user.provider.concrete.my_chain_provider', $chainUserProvider);
        $this->containerBuilder->setDefinition('security.user.provider.concrete.my_ldap_provider', $ldapUserProvider);
        $this->containerBuilder->setDefinition('security.user.provider.concrete.my_db_provider', $entityUserProvider);
        $this->containerBuilder->setDefinition('security.user.provider.concrete.my_some_custom_provider', $customProvider);
        $this->containerBuilder->setParameter('two_fas_two_factor.remember_me.lifetime', 600);
    }

    private function setFirewalls()
    {
        $this->containerBuilder->setParameter('two_fas_two_factor.firewalls', ['2fas', 'main']);
    }

    private function prepareSecurityConfig()
    {
        return [
            'providers' => [
                'in_memory'               => ['memory'],
                'my_db_provider'          => ['entity'],
                'my_ldap_provider'        => ['ldap'],
                'my_chain_provider'       => ['chain'],
                'my_some_custom_provider' => ['id' => 'foobar']
            ]
        ];
    }
}
