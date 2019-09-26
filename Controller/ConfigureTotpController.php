<?php

namespace Fungio\TwoFactorBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TwoFAS\Api\Exception\Exception as ApiException;
use TwoFAS\Api\Methods;
use TwoFAS\Api\TotpSecretGenerator;
use Fungio\TwoFactorBundle\Event\IntegrationUserConfigurationCompleteEvent;
use Fungio\TwoFactorBundle\Event\FungioEvents;
use Fungio\TwoFactorBundle\Form\CodeForm;

/**
 * Configure 2FAS to use TOTP Authentication.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Controller
 */
class ConfigureTotpController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     *
     * @throws ApiException
     */
    public function configureAction(Request $request)
    {
        $integrationUser = $this->getIntegrationUser();
        $form            = $this->createForm(CodeForm::class, null, ['validation_groups' => ['configure']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $authenticationManager = $this->get('fungio_two_factor.util.authentication_manager');
            $userStorage           = $this->get('fungio_two_factor.storage.user_session_storage');
            $dispatcher            = $this->get('event_dispatcher');
            $user                  = $this->getFungioUser();
            $data                  = $form->getData();
            $totpSecret            = $data['totp_secret'];
            $code                  = $data['code'];

            $authentication = $authenticationManager->openTotpAuthentication($user, $totpSecret);
            $response       = $authenticationManager->checkCode(new ArrayCollection([$authentication]), $code);

            if ($response->accepted()) {

                $integrationUser->setTotpSecret($totpSecret);
                $user->enableChannel(Methods::TOTP);

                $userStorage->updateIntegrationUser($integrationUser);
                $userStorage->updateUser($user);

                $dispatcher->dispatch(FungioEvents::INTEGRATION_USER_CONFIGURATION_COMPLETE_TOTP, new IntegrationUserConfigurationCompleteEvent($integrationUser));

                return $this->redirectToRoute('fungio_index');
            }
        }

        return $this->render('@FungioTwoFactor/ConfigureTotp/configure.html.twig', [
            'totp_secret'   => (!empty($integrationUser->getTotpSecret()) ? $integrationUser->getTotpSecret() : TotpSecretGenerator::generate()),
            'mobile_secret' => $integrationUser->getMobileSecret(),
            'form'          => $form->createView()
        ]);
    }

    /**
     * @return Response
     *
     * @throws ApiException
     */
    public function reloadAction()
    {
        $integrationUser = $this->getIntegrationUser();

        return $this->forward('FungioTwoFactorBundle:GenerateQrCode:generateJson', [
            'totpSecret'   => TotpSecretGenerator::generate(),
            'mobileSecret' => $integrationUser->getMobileSecret()
        ]);
    }
}
