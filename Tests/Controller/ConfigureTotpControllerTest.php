<?php

namespace TwoFAS\TwoFactorBundle\Tests\Controller;

use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Methods;
use TwoFAS\ValidationRules\ValidationRules;
use TwoFAS\TwoFactorBundle\EventListener\TrustedDeviceListener;

class ConfigureTotpControllerTest extends ControllerTestCase
{
    /**
     * @var string
     */
    private $submitButton;

    /**
     * @var string
     */
    private $uri = '/2fas/configure/totp';

    public function setUp()
    {
        parent::setUp();

        $this->submitButton = $this->translator->trans('form.code.enable_button', [], 'TwoFASTwoFactorBundle');
        $this->twoFASStatus->setValue(false);

        $this->mockTrustedDeviceListener();
    }

    public function testNoLogged()
    {
        $this->client->followRedirects(false);
        $this->client->request('GET', $this->uri);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    public function testIndex()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("2FAS - Two Factor Authentication Service")')->count()
        );
    }

    public function testCreateUserWhenNotExists()
    {
        $this->login();
        $this->userRepository->remove($this->twoFASUser);
        $this->integrationUserManager->method('findByExternalId')->willReturn(null);
        $this->integrationUserManager->method('createUser')->willReturn(new IntegrationUser());

        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("2FAS - Two Factor Authentication Service")')->count()
        );

        $this->assertCount(1, $this->userRepository->findAll());
    }

    public function testCreateIntegrationUserWhenNotExists()
    {
        $this->login();
        $this->userRepository->remove($this->twoFASUser);
        $this->integrationUserManager->method('findByExternalId')->willReturn(null);
        $this->integrationUserManager->method('createUser')->willReturn(new IntegrationUser());
        $this->integrationUserManager->expects($this->once())->method('createUser');

        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("2FAS - Two Factor Authentication Service")')->count()
        );
    }

    public function testSubmitEmptyForm()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $crawler = $this->client->submit($form);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("code.valid", [], "validators") . '")')->count()
        );
    }

    public function testSubmitInvalidCode()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $crawler = $this->client->submit($form, ['code' => 'abc']);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("code.valid", [], "validators") . '")')->count()
        );
    }

    public function testApiAuthorizationError()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $this->authenticationManager->method('openTotpAuthentication')->willThrowException(new AuthorizationException());

        $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testApiValidationTotpSecretRequiredError()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $this->authenticationManager->method('openTotpAuthentication')->willThrowException(new ValidationException(['totp_secret' => [ValidationRules::REQUIRED]]));

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("authentication.totp_secret.required", [], "messages") . '")')->count()
        );
    }

    public function testApiValidationCodeRequiredError()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $this->openTotpAuthentication();
        $this->api->method('checkCode')->willThrowException(new ValidationException(['code' => [ValidationRules::REQUIRED]]));

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("authentication.code.required", [], "messages") . '")')->count()
        );
    }

    public function testApiValidationCodeDigitsError()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $this->openTotpAuthentication();
        $this->api->method('checkCode')->willThrowException(new ValidationException(['code' => [ValidationRules::DIGITS]]));

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("authentication.code.digits", [], "messages") . '")')->count()
        );
    }

    public function testApiUnsupportedValidationError()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $this->authenticationManager->method('openTotpAuthentication')->willThrowException(new ValidationException(['not_exist' => ['validation.dummy']]));

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("general.unknown_error", [], "messages") . '")')->count()
        );
    }

    public function testRejectedCodeCanRetry()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $authentication = $this->getAuthentication(Methods::TOTP);
        $authentication->setUser($this->twoFASUser);

        $this->openTotpAuthentication();
        $this->checkCodeRejectedCanRetry();

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("authentication.code.can_retry", [], "messages") . '")')->count()
        );
    }

    public function testRejectedCodeCannotRetry()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $this->openTotpAuthentication();
        $this->checkCodeRejectedCannotRetry();

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("authentication.code.cannot_retry", [], "messages") . '")')->count()
        );
    }

    public function testAcceptedCodeAndSuccessfulConfiguration()
    {
        $this->login();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();

        $this->integrationUser->setExternalId(1);
        $this->openTotpAuthentication();
        $this->checkCodeAccepted();

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.two_factor.status', [], 'TwoFASTwoFactorBundle') . '")')->count()
        );
    }

    public function testReloadQrCode()
    {
        $this->login();

        $this->client->request(
            'GET',
            '/2fas/reload/totp',
            [],
            [],
            [
                'CONTENT_TYPE'          => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]);

        $response = $this->client->getResponse();
        $json     = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('qr_code', $json);
        $this->assertArrayHasKey('totp_secret', $json);
        $this->assertNotEmpty($json['qr_code']);
        $this->assertNotEmpty($json['totp_secret']);
    }

    public function testRemoveTrustedDevicesAfterConfigureTotp()
    {
        $this->login();

        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->integrationUser->setExternalId(1);
        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $authentication = $this->getAuthentication(Methods::TOTP);
        $authentication->setUser($this->twoFASUser);

        $crawler = $this->client->request('GET', '/2fas/index');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("an unknown browser")')->count()
        );

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->submitButton)->form();


        $this->openTotpAuthentication();
        $this->checkCodeAccepted();

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.trusted_devices.empty', [], 'TwoFASTwoFactorBundle') . '")')->count()
        );
    }

    protected function mockTrustedDeviceListener()
    {
        $trustedDeviceListener = new TrustedDeviceListener(
            $this->container->get('two_fas_two_factor.storage.user_session_storage'),
            $this->container->get('two_fas_two_factor.object_manager'),
            $this->container->get('two_fas_two_factor.remember_me_persister')
        );

        $this->container->set('two_fas_two_factor.event_listener.trusted_device', $trustedDeviceListener);
    }
}
