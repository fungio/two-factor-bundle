<?php

namespace TwoFAS\TwoFactorBundle\Util;

use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TwoFAS\Api\Code\Code;
use InvalidArgumentException;
use TwoFAS\Api\Exception\Exception as ApiException;
use TwoFAS\Api\Exception\IntegrationUserHasNoActiveMethodException;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Methods;
use TwoFAS\TwoFactorBundle\Event\CodeCheckEvent;
use TwoFAS\TwoFactorBundle\Event\TwoFASEvents;
use TwoFAS\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use TwoFAS\TwoFactorBundle\Model\Entity\UserInterface;
use TwoFAS\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use TwoFAS\TwoFactorBundle\Proxy\ApiProviderInterface;

/**
 * Facade class between application and 2FAS api - manages Authentications
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Util
 */
class AuthenticationManager
{
    /**
     * @var ApiProviderInterface
     */
    private $provider;

    /**
     * @var ObjectPersisterInterface
     */
    private $authenticationPersister;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var int
     */
    private $blockUserInMinutes;

    /**
     * AuthenticationManager constructor.
     *
     * @param ApiProviderInterface     $provider
     * @param ObjectPersisterInterface $authenticationPersister
     * @param ObjectManager            $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param int                      $blockUserInMinutes
     */
    public function __construct(
        ApiProviderInterface $provider,
        ObjectPersisterInterface $authenticationPersister,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $blockUserInMinutes
    ) {
        $this->provider                = $provider;
        $this->authenticationPersister = $authenticationPersister;
        $this->objectManager           = $objectManager;
        $this->eventDispatcher         = $eventDispatcher;
        $this->blockUserInMinutes      = $blockUserInMinutes;
    }

    /**
     * @param UserInterface $user
     * @param string        $channel
     *
     * @return ArrayCollection
     */
    public function getOpenAuthentications(UserInterface $user, $channel)
    {
        /** @var UserInterface $user */
        $user = $this->objectManager->merge($user);

        $criteria = Criteria::create();
        $expr     = Criteria::expr();
        $criteria
            ->where($expr->eq('user', $user))
            ->andWhere($expr->eq('type', $channel))
            ->andWhere($expr->gte('validTo', new DateTime()))
            ->andWhere($expr->eq('verified', false));

        return $user->getAuthentications()->matching($criteria);
    }

    /**
     * @param UserInterface   $user
     * @param IntegrationUser $integrationUser
     * @param string          $channel
     *
     * @return AuthenticationInterface
     *
     * @throws ApiException
     * @throws IntegrationUserHasNoActiveMethodException
     */
    public function openAuthentication(UserInterface $user, IntegrationUser $integrationUser, $channel)
    {
        switch ($channel) {
            case Methods::TOTP:
                return $this->openTotpAuthentication($user, $integrationUser->getTotpSecret());
            default:
                throw new InvalidArgumentException('Channel is not supported.');
        }
    }

    /**
     * @param UserInterface $user
     * @param string        $totpSecret
     *
     * @return AuthenticationInterface
     *
     * @throws ApiException
     */
    public function openTotpAuthentication(UserInterface $user, $totpSecret)
    {
        return $this->provider->requestAuthViaTotp($user, $totpSecret);
    }

    /**
     * @param ArrayCollection $authentications
     * @param string          $code
     *
     * @return Code
     *
     * @throws ApiException
     */
    public function checkCode(ArrayCollection $authentications, $code)
    {
        $response = $this->provider->checkCode($authentications, $code);

        $this->eventDispatcher->dispatch($this->getEventType($response), new CodeCheckEvent($response));

        return $response;
    }

    /**
     * @param array $authenticationIds
     */
    public function closeAuthentications(array $authenticationIds)
    {
        $authentications = $this->authenticationPersister->getRepository()->findBy(['id' => $authenticationIds]);

        array_map(function(AuthenticationInterface $authentication) {
            $authentication->setVerified(true);
            $this->authenticationPersister->saveEntity($authentication);
        }, $authentications);
    }

    /**
     * @param array $authenticationIds
     */
    public function blockAuthentications(array $authenticationIds)
    {
        $authentications = $this->authenticationPersister->getRepository()->findBy(['id' => $authenticationIds]);

        array_map(function(AuthenticationInterface $authentication) {

            if ($authentication->isBlocked()) {
                return;
            }

            $blockedTo = (new DateTime())->add(new DateInterval('PT' . $this->blockUserInMinutes . 'M'));

            if ($authentication->getValidTo() > $blockedTo) {
                $authentication->setValidTo($blockedTo);
            }

            $authentication->setBlocked(true);
            $this->authenticationPersister->saveEntity($authentication);

        }, $authentications);
    }

    /**
     * @param Code $code
     *
     * @return string
     */
    private function getEventType(Code $code)
    {
        if ($code->accepted()) {
            return TwoFASEvents::CODE_ACCEPTED;
        }

        if ($code->canRetry()) {
            return TwoFASEvents::CODE_REJECTED_CAN_RETRY;
        }

        return TwoFASEvents::CODE_REJECTED_CANNOT_RETRY;
    }
}