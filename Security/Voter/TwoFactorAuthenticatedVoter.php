<?php

namespace TwoFAS\TwoFactorBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use TwoFAS\TwoFactorBundle\Security\Token\TwoFactorToken;

/**
 * Voter check that user has IS_AUTHENTICATED_TWO_FACTOR_FULLY role.
 * If user has "remembered" role, he has IS_AUTHENTICATED_TWO_FACTOR_FULLY role too.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Security\Voter
 */
class TwoFactorAuthenticatedVoter extends Voter
{
    /**
     * Is Authenticated with Two-Factor authentication role
     */
    const IS_AUTHENTICATED_TWO_FACTOR_FULLY = 'IS_AUTHENTICATED_TWO_FACTOR_FULLY';

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
        return self::IS_AUTHENTICATED_TWO_FACTOR_FULLY === $attribute;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->isFirstFactorGranted($token) &&  $this->isSecondFactorToken($token);
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
    private function isSecondFactorToken(TokenInterface $token)
    {
        return $token instanceof TwoFactorToken;
    }
}