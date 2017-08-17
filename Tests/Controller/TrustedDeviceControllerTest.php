<?php

namespace TwoFAS\TwoFactorBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use TwoFAS\TwoFactorBundle\Controller\TrustedDeviceController;
use TwoFAS\TwoFactorBundle\Model\Entity\RememberMeToken;
use TwoFAS\TwoFactorBundle\Model\Entity\User as TwoFASUser;

class TrustedDeviceControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockTrustedDeviceVoter();
    }

    public function testRemoveTrustedDevice()
    {
        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, $tokenValue);
        $this->login();

        $request = new Request();
        $request->request->add([
            'id'     => $series,
            '_token' => $this->csrfToken
        ]);

        $controller = $this->getController();
        $response   = $controller->removeAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidCsrfToken()
    {
        $this->setExpectedException(AccessDeniedHttpException::class, $this->translator->trans('general.denied_action'));
        $this->login();

        $request = new Request();
        $request->request->add([
            'id'     => 'no-exists',
            '_token' => 'foobar'
        ]);

        $controller = $this->getController();
        $controller->removeAction($request);
    }

    public function testRemoveNotExistentDevice()
    {
        $this->setExpectedException(NotFoundHttpException::class, $this->translator->trans('trusted_devices.remove.not_found'));

        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, $tokenValue);
        $this->login();

        $request = new Request();
        $request->request->add([
            'id'     => 'foo',
            '_token' => $this->csrfToken
        ]);

        $controller = $this->getController();
        $controller->removeAction($request);
    }

    public function testCannotRemoveDeviceFromAnotherUser()
    {
        $this->setExpectedException(AccessDeniedHttpException::class, $this->translator->trans('general.denied_action'));

        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, $tokenValue);
        $this->login();

        $twoFASUser = new TwoFASUser();
        $twoFASUser->setUsername('tom');

        $token = new RememberMeToken();
        $token
            ->setSeries('123')
            ->setValue('321')
            ->setClass(User::class)
            ->setUser($twoFASUser)
            ->setBrowser('')
            ->setLastUsedAt($lastUsed);

        $twoFASUser->addToken($token);
        $this->tokenRepository->add($token);

        $request = new Request();
        $request->request->add([
            'id'     => '123',
            '_token' => $this->csrfToken
        ]);

        $controller = $this->getController();
        $controller->removeAction($request);
    }

    /**
     * @return TrustedDeviceController
     */
    private function getController()
    {
        $controller = new TrustedDeviceController();
        $controller->setContainer($this->container);

        return $controller;
    }
}
