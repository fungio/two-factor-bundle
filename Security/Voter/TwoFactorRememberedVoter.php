<?php

namespace Fungio\TwoFactorBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Fungio\TwoFactorBundle\Security\Token\TwoFactorRememberMeToken;

/**
 * Voter check that user has IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED role.
 * Usr has this role after login on trusted device.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Security\Voter
 */
class TwoFactorRememberedVoter extends Voter
{
    /**
     * Is Authenticated with Two-Factor Remember me authentication role
     */
    const IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED = 'IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        return self::IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED === $attribute;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->isSecondFactorGranted($token)) {
            return true;
        }

        return $this->isFirstFactorGranted($token) && $this->isRememberedTwoFactor($token);
    }

    /**
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function isFirstFactorGranted(TokenInterface $token)
    {
        return $this->decisionManager->decide($token, ['IS_AUTHENTICATED_FULLY', 'IS_AUTHENTICATED_REMEMBERED']);
    }

    /**
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function isSecondFactorGranted(TokenInterface $token)
    {
        return $this->decisionManager->decide($token, ['IS_AUTHENTICATED_TWO_FACTOR_FULLY']);
    }

    /**
     * @param TokenInterface $token
     *
     * @return bool
     */
    private function isRememberedTwoFactor(TokenInterface $token)
    {
        return $token instanceof TwoFactorRememberMeToken;
    }
}