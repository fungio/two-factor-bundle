<?php

namespace Fungio\TwoFactorBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\ChannelNotActiveException;
use TwoFAS\Api\Exception\Exception as ApiException;
use TwoFAS\Api\Exception\ValidationException;
use Fungio\TwoFactorBundle\Form\CodeForm;
use Fungio\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use Fungio\TwoFactorBundle\Security\Token\TwoFactorToken;
use Fungio\TwoFactorBundle\Storage\InvalidTokenException;

/**
 * Second factor authentication form and check authentication in 2FAS.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Controller
 */
class CheckCodeController extends Controller
{
    /**
     * @param Request $request
     * @param string  $channel
     *
     * @return RedirectResponse|Response
     *
     * @throws AccessDeniedHttpException
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws ApiException
     */
    public function checkAction(Request $request, $channel)
    {
        if ($this->isGranted('IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED')) {
            throw new AccessDeniedHttpException('This path is only available through login process.');
        }

        $user = $this->getFungioUser();

        if (!$user->isChannelEnabled($channel)) {
            throw new ChannelNotActiveException('No channel is enabled.');
        }

        $authentications = $this->getUserAuthentications($channel);

        $form = $this->createForm(CodeForm::class,
            ['auth_id' => $this->getAuthenticationIds($authentications)],
            ['validation_groups' => ['check_code']]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->getData()['code'];

            $authenticationManager = $this->get('fungio_two_factor.util.authentication_manager');
            $response              = $authenticationManager->checkCode($authentications, $code);

            if ($response->accepted()) {
                return $this->checkSuccessful($request);
            }
        }

        return $this->render('@FungioTwoFactor/CheckCode/check.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param string $channel
     *
     * @return ArrayCollection
     *
     * @throws ApiException
     */
    protected function getUserAuthentications($channel)
    {
        try {
            $userStorage           = $this->get('fungio_two_factor.storage.user_session_storage');
            $authenticationManager = $this->get('fungio_two_factor.util.authentication_manager');
            $user                  = $this->getFungioUser();
            $authentications       = $authenticationManager->getOpenAuthentications($user, $channel);

            if (0 === $authentications->count()) {
                $authentication  = $authenticationManager->openAuthentication($user, $channel);
                $user->addAuthentication($authentication);
                $authentications->add($authentication);
                $userStorage->updateUser($user);
            }

            return $authentications;

        } catch (ValidationException $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * @param ArrayCollection $authentications
     *
     * @return array
     */
    protected function getAuthenticationIds(ArrayCollection $authentications)
    {
        return array_map(function(AuthenticationInterface $authentication) {
            return $authentication->getId();
        }, $authentications->toArray());
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws InvalidTokenException
     */
    protected function checkSuccessful(Request $request)
    {
        $targetPath        = 'fungio_two_factor.redirect_after_login.path';
        $rememberMeFactory = $this->get('fungio_two_factor.dependency_injection_factory.persistent_remember_me_services_factory');
        $token             = $this->get('fungio_two_factor.storage.token_storage')->getToken();
        $session           = $request->getSession();

        if (!$token instanceof TwoFactorToken) {
            throw new InvalidTokenException('Invalid token in storage after login.');
        }

        $url = $session->has($targetPath) ? $session->get($targetPath) : '/';
        $session->remove($targetPath);
        $response = new RedirectResponse($url);

        $rememberMeService = $rememberMeFactory->createInstance();
        $rememberMeService->loginSuccess($request, $response, $token);

        return $response;
    }
}
