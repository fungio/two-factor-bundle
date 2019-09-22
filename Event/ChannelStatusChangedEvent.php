<?php

namespace Fungio\TwoFactorBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Fungio\TwoFactorBundle\Model\Entity\UserInterface;

/**
 * Event fires when channel status has been changed.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Event
 */
class ChannelStatusChangedEvent extends Event
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var string
     */
    private $channel;

    /**
     * @param UserInterface $user
     * @param string        $channel
     */
    public function __construct(UserInterface $user, $channel)
    {
        $this->user    = $user;
        $this->channel = $channel;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }
}