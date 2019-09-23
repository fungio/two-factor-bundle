<?php

namespace Fungio\TwoFactorBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Fungio\TwoFactorBundle\DependencyInjection\FungioTwoFactorExtension;

class FungioTwoFactorExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $alias = 'fungio_two_factor';

    public function testDefaultConfiguration()
    {
        $container = $this->load($this->getDefaultConfiguration());

        $this->assertEquals('Symfony Example', $container->getParameter($this->alias . '.account_name'));
        $this->assertEquals('orm', $container->getParameter($this->alias . '.db_driver'));
        $this->assertEquals('foo', $container->getParameter($this->alias . '.encryption_key'));
        $this->assertEquals(['2fas'], $container->getParameter($this->alias . '.firewalls'));
        $this->assertNull($container->getParameter($this->alias . '.api_url'));
        $this->assertNull($container->getParameter($this->alias . '.account_url'));
        $this->assertNull($container->getParameter($this->alias . '.entities.option_class'));
        $this->assertNull($container->getParameter($this->alias . '.entities.user_class'));
        $this->assertNull($container->getParameter($this->alias . '.entities.authentication_class'));
        $this->assertNull($container->getParameter($this->alias . '.entities.remember_me_class'));
        $this->assertEquals($this->alias . '.option_persister.default', $container->getAlias($this->alias . '.option_persister'));
        $this->assertEquals($this->alias . '.user_persister.default', $container->getAlias($this->alias . '.user_persister'));
        $this->assertEquals($this->alias . '.authentication_persister.default', $container->getAlias($this->alias . '.authentication_persister'));
        $this->assertEquals($this->alias . '.remember_me_persister.default', $container->getAlias($this->alias . '.remember_me_persister'));
        $this->assertEquals(5, $container->getParameter($this->alias . '.block_user_login_in_minutes'));
        $this->assertEquals(31536000, $container->getParameter($this->alias . '.remember_me.lifetime'));
    }

    public function testCustomConfiguration()
    {
        $container = $this->load($this->getCustomConfiguration());

        $this->assertEquals('Symfony Example', $container->getParameter($this->alias . '.account_name'));
        $this->assertEquals('custom', $container->getParameter($this->alias . '.db_driver'));
        $this->assertEquals('foo', $container->getParameter($this->alias . '.encryption_key'));
        $this->assertEquals(['2fas'], $container->getParameter($this->alias . '.firewalls'));
        $this->assertEquals('http://localhost', $container->getParameter($this->alias . '.api_url'));
        $this->assertEquals('http://localhost', $container->getParameter($this->alias . '.account_url'));
        $this->assertEquals($this->alias . '.dummy_option_class', $container->getParameter($this->alias . '.entities.option_class'));
        $this->assertEquals($this->alias . '.dummy_user_class', $container->getParameter($this->alias . '.entities.user_class'));
        $this->assertEquals($this->alias . '.dummy_authentication_class', $container->getParameter($this->alias . '.entities.authentication_class'));
        $this->assertEquals($this->alias . '.dummy_remember_me_class', $container->getParameter($this->alias . '.entities.remember_me_class'));
        $this->assertEquals($this->alias . '.dummy_option_persister', $container->getAlias($this->alias . '.option_persister'));
        $this->assertEquals($this->alias . '.dummy_user_persister', $container->getAlias($this->alias . '.user_persister'));
        $this->assertEquals($this->alias . '.dummy_authentication_persister', $container->getAlias($this->alias . '.authentication_persister'));
        $this->assertEquals($this->alias . '.dummy_remember_me_persister', $container->getAlias($this->alias . '.remember_me_persister'));
        $this->assertEquals(10, $container->getParameter($this->alias . '.block_user_login_in_minutes'));
        $this->assertEquals(600, $container->getParameter($this->alias . '.remember_me.lifetime'));
    }

    public function testValidateOptionPersister()
    {
        $this->setExpectedExceptionRegExp(InvalidConfigurationException::class, '/You need to specify your own option persister when using the "custom" driver\.$/');

        $config = $this->getCustomConfiguration();
        unset($config[$this->alias]['persisters']['option_persister']);

        $this->load($config);
    }

    public function testValidateUserPersister()
    {
        $this->setExpectedExceptionRegExp(InvalidConfigurationException::class, '/You need to specify your own user persister when using the "custom" driver\.$/');

        $config = $this->getCustomConfiguration();
        unset($config[$this->alias]['persisters']['user_persister']);

        $this->load($config);
    }

    public function testValidateAuthenticationPersister()
    {
        $this->setExpectedExceptionRegExp(InvalidConfigurationException::class, '/You need to specify your own authentication persister when using the "custom" driver\.$/');

        $config = $this->getCustomConfiguration();
        unset($config[$this->alias]['persisters']['authentication_persister']);

        $this->load($config);
    }

    public function testValidateRememberMePersister()
    {
        $this->setExpectedExceptionRegExp(InvalidConfigurationException::class, '/You need to specify your own remember_me persister when using the "custom" driver\.$/');

        $config = $this->getCustomConfiguration();
        unset($config[$this->alias]['persisters']['remember_me_persister']);

        $this->load($config);
    }

    public function testValidateBlockUserWhenValueIsLessThanZero()
    {
        $this->setExpectedExceptionRegExp(InvalidConfigurationException::class, '/Should be greater than or equal to 0$/');

        $config                                              = $this->getCustomConfiguration();
        $config[$this->alias]['block_user_login_in_minutes'] = -1;

        $this->load($config);
    }

    public function testValidateBlockUserWhenValueIsGreaterThanAuthenticationDuration()
    {
        $this->setExpectedExceptionRegExp(InvalidConfigurationException::class, '/Should be less than or equal to 15$/');

        $config                                              = $this->getCustomConfiguration();
        $config[$this->alias]['block_user_login_in_minutes'] = 20;

        $this->load($config);
    }

    /**
     * @param array $configs
     *
     * @return ContainerBuilder
     */
    private function load(array $configs)
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $extension = new FungioTwoFactorExtension();
        $extension->load($configs, $container);

        return $container;
    }

    /**
     * @return array
     */
    private function getDefaultConfiguration()
    {
        return [
            $this->alias => [
                'account_name'   => 'Symfony Example',
                'db_driver'      => 'orm',
                'encryption_key' => 'foo',
                'firewalls'      => ['2fas']
            ]
        ];
    }

    private function getCustomConfiguration()
    {
        return [
            $this->alias => [
                'account_name'                => 'Symfony Example',
                'db_driver'                   => 'custom',
                'encryption_key'              => 'foo',
                'firewalls'                   => ['2fas'],
                'api_url'                     => 'http://localhost',
                'account_url'                 => 'http://localhost',
                'block_user_login_in_minutes' => 10,
                'remember_me'                 => ['lifetime' => 600],
                'entities'                    => [
                    'option_class'         => $this->alias . '.dummy_option_class',
                    'user_class'           => $this->alias . '.dummy_user_class',
                    'authentication_class' => $this->alias . '.dummy_authentication_class',
                    'remember_me_class'    => $this->alias . '.dummy_remember_me_class',
                ],
                'persisters'                  => [
                    'option_persister'         => $this->alias . '.dummy_option_persister',
                    'user_persister'           => $this->alias . '.dummy_user_persister',
                    'authentication_persister' => $this->alias . '.dummy_authentication_persister',
                    'remember_me_persister'    => $this->alias . '.dummy_remember_me_persister'
                ]
            ]
        ];
    }
}
