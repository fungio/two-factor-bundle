<?php

namespace TwoFAS\TwoFactorBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\ChannelNotActiveException;
use TwoFAS\Api\Exception\Exception as ApiException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\TwoFactorBundle\Form\CodeForm;
use TwoFAS\TwoFactorBundle\Model\Entity\AuthenticationInterface;
use TwoFAS\TwoFactorBundle\Security\Token\TwoFactorToken;
use TwoFAS\TwoFactorBundle\Storage\InvalidTokenException;

/**
 * Second factor authentication form and check authentication in 2FAS.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Controller
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

        $user = $this->getTwoFASUser();

        if (!$user->isChannelEnabled($channel)) {
            throw new ChannelNotActiveException('No channel is enabled.');
        }

        $authentications = $this->getUsersAuthentications($channel);

        $form = $this->createForm(CodeForm::class,
            ['auth_id' => $this->getAuthenticationIds($authentications)],
            ['validation_groups' => ['check_code']]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->getData()['code'];

            $authenticationManager = $this->get('two_fas_two_factor.util.authentication_manager');
            $response              = $authenticationManager->checkCode($authentications, $code);

            if ($response->accepted()) {
                return $this->checkSuccessful($request);
            }
        }

        return $this->render('TwoFASTwoFactorBundle:CheckCode:check.html.twig', [
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
    protected function getUsersAuthentications($channel)
    {
        try {
            $userStorage           = $this->get('two_fas_two_factor.storage.user_session_storage');
            $authenticationManager = $this->get('two_fas_two_factor.util.authentication_manager');
            $user                  = $this->getTwoFASUser();
            $authentications       = $authenticationManager->getOpenAuthentications($user, $channel);

            if (0 === $authentications->count()) {
                $integrationUser = $this->getIntegrationUser();
                $authentication  = $authenticationManager->openAuthentication($user, $integrationUser, $channel);
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
        $targetPath        = 'two_fas_two_factor.redirect_after_login.path';
        $rememberMeFactory = $this->get('two_fas_two_factor.dependency_injection_factory.persistent_remember_me_services_factory');
        $token             = $this->get('two_fas_two_factor.storage.token_storage')->getToken();
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
