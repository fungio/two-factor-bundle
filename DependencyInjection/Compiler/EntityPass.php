<?php

namespace Fungio\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Fungio\TwoFactorBundle\Entity\Authentication as OrmAuthentication;
use Fungio\TwoFactorBundle\Entity\Option as OrmOption;
use Fungio\TwoFactorBundle\Entity\RememberMeToken as OrmRememberMeToken;
use Fungio\TwoFactorBundle\Entity\User as OrmUser;
use Fungio\TwoFactorBundle\Model\Entity\Authentication as ModelAuthentication;
use Fungio\TwoFactorBundle\Model\Entity\Option as ModelOption;
use Fungio\TwoFactorBundle\Model\Entity\RememberMeToken as ModelRememberMeToken;
use Fungio\TwoFactorBundle\Model\Entity\User as ModelUser;

/**
 * Sets entity classes for multiple db drivers.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\DependencyInjection\Compiler
 */
class EntityPass implements CompilerPassInterface
{
    const OPTION_CLASS = 'fungio_two_factor.entities.option_class';
    const USER_CLASS = 'fungio_two_factor.entities.user_class';
    const AUTHENTICATION_CLASS = 'fungio_two_factor.entities.authentication_class';
    const REMEMBER_ME_CLASS = 'fungio_two_factor.entities.remember_me_class';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $dbDriver = $container->getParameter('fungio_two_factor.db_driver');

        switch ($dbDriver) {
            case 'orm':
                $option          = OrmOption::class;
                $user            = OrmUser::class;
                $authentication  = OrmAuthentication::class;
                $rememberMeToken = OrmRememberMeToken::class;
                break;
            case 'custom':
                $option          = ModelOption::class;
                $user            = ModelUser::class;
                $authentication  = ModelAuthentication::class;
                $rememberMeToken = ModelRememberMeToken::class;
                break;

            default:
                throw new \InvalidArgumentException('Invalid db driver');
        }


        $this->setEntityClass($container, self::OPTION_CLASS, $option);
        $this->setEntityClass($container, self::USER_CLASS, $user);
        $this->setEntityClass($container, self::AUTHENTICATION_CLASS, $authentication);
        $this->setEntityClass($container, self::REMEMBER_ME_CLASS, $rememberMeToken);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $parameter
     * @param string           $entity
     */
    protected function setEntityClass(ContainerBuilder $container, $parameter, $entity)
    {
        if (!$container->hasParameter($parameter) || is_null($container->getParameter($parameter))) {
            $container->setParameter($parameter, $entity);
        }
    }
}