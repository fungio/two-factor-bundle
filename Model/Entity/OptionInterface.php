<?php

namespace Fungio\TwoFactorBundle\Model\Entity;

/**
 * Contract for Option Model class.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Model
 */
interface OptionInterface
{
    const LOGIN       = 'login';
    const TOKEN       = 'token';
    const INTEGRATION = 'integration';
    const STATUS      = 'status';
    const OAUTH_SCOPE = 'oauth_scope';

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set name
     *
     * @param string $name
     *
     * @return OptionInterface
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set value
     *
     * @param string $value
     *
     * @return OptionInterface
     */
    public function setValue($value);

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();
}