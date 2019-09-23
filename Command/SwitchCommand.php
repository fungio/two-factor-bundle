<?php

namespace Fungio\TwoFactorBundle\Command;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Psr\SimpleCache\CacheInterface;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;

/**
 * Abstract class for enable/disable commands
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Command
 */
abstract class SwitchCommand extends ContainerAwareCommand
{
    /**
     * @throws LogicException
     */
    protected function checkOptions()
    {
        $optionPersister = $this->getOptionPersister();

        if (is_null($optionPersister->getRepository()->findOneBy(['name' => OptionInterface::LOGIN]))) {
            throw new LogicException('Fungio Login has not been set.');
        }

        if (is_null($optionPersister->getRepository()->findOneBy(['name' => OptionInterface::TOKEN]))) {
            throw new LogicException('Fungio Login has not been set.');
        }
    }

    /**
     * @param bool $working
     */
    protected function switchStatus($working)
    {
        $optionPersister = $this->getOptionPersister();
        /** @var OptionInterface|null $status */
        $status = $optionPersister->getRepository()->findOneBy(['name' => OptionInterface::STATUS]);

        if (is_null($status)) {
            $status = $optionPersister->getEntity();
            $status->setName(OptionInterface::STATUS);
        }

        $status->setValue((int) $working);
        $optionPersister->saveEntity($status);

        $this->getCache()->clear();
    }

    /**
     * @return ObjectPersisterInterface
     */
    private function getOptionPersister()
    {
        return $this->getContainer()->get('fungio_two_factor.option_persister');
    }

    /**
     * @return CacheInterface
     */
    protected function getCache()
    {
        return $this->getContainer()->get('fungio_two_factor.cache.storage');
    }
}
