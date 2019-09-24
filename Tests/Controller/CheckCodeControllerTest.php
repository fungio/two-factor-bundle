<?php

namespace Fungio\TwoFactorBundle\Tests\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\Methods;
use TwoFAS\ValidationRules\ValidationRules;

class CheckCodeControllerTest extends ControllerTestCase
{
    /**
     * @var string
     */
    private $loginButton;

    /**
     * @var string
     */
    private $uri = '/2fas/check';

    public function setUp()
    {
        parent::setUp();

        $this->loginButton = $this->translator->trans('form.code.login_button', [], 'FungioTwoFactorBundle');
        $this->fungioUser->enableChannel(Methods::TOTP);
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
        $this->openAuthentication(Methods::TOTP);

        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('form.code.remember_me', [], 'FungioTwoFactorBundle') . '")')->count()
        );
    }

    public function testSubmitEmptyForm()
    {
        $this->login();
        $this->openAuthentication(Methods::TOTP);

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

        $crawler = $this->client->submit($form);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("code.valid", [], "validators") . '")')->count()
        );
    }

    public function testSubmitInvalidCode()
    {
        $this->login();
        $this->openAuthentication(Methods::TOTP);

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

        $crawler = $this->client->submit($form, ['code' => 'abc']);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("code.valid", [], "validators") . '")')->count()
        );
    }

    public function testApiAuthorizationError()
    {
        $this->login();
        $this->authenticationManager->method('getOpenAuthentications')->willReturn(new ArrayCollection([]));
        $this->authenticationManager->method('openAuthentication')->willThrowException(new AuthorizationException());

        $this->client->request('GET', $this->uri);

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testApiValidationCodeRequiredError()
    {
        $this->login();
        $this->openAuthentication(Methods::TOTP);

        $this->api->method('checkCode')->willThrowException(new ValidationException(['code' => [ValidationRules::REQUIRED]]));

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

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
        $this->openAuthentication(Methods::TOTP);

        $this->api->method('checkCode')->willThrowException(new ValidationException(['code' => [ValidationRules::DIGITS]]));

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

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
        $this->openAuthentication(Methods::TOTP);

        $this->api->method('checkCode')->willThrowException(new ValidationException(['not_exist' => ['validation.dummy']]));

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

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

    public function testApiValidationExceptionOnAuthenticationOpen()
    {
        $this->login();

        $this->authenticationManager->method('getOpenAuthentications')->willReturn(new ArrayCollection([]));
        $this->authenticationManager->method('openAuthentication')->willThrowException(new ValidationException([]));

        $this->client->request('GET', $this->uri);

        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }

    public function testRejectedCodeCanRetry()
    {
        $this->login();
        $this->openAuthentication(Methods::TOTP);
        $this->checkCodeRejectedCanRetry();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

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
        $this->openAuthentication(Methods::TOTP);
        $this->checkCodeRejectedCannotRetry();

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

        $crawler = $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans("authentication.code.cannot_retry", [], "messages") . '")')->count()
        );
    }

    public function testAcceptedCodeWithTotpAuthentication()
    {
        $this->login();

        $this->openAuthentication(Methods::TOTP);
        $this->checkCodeAccepted();

        $this->client->request('GET', '/2fas/index');

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

        $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/2fas/index', $this->client->getRequest()->getRequestUri());
    }

    public function testOpenNewAuthenticationWhenUserHasNotAnyOpenedAuthentications()
    {
        $authentication = $this->getAuthentication(Methods::TOTP);
        $authentication->setUser($this->fungioUser);

        $this->login();
        $this->checkCodeAccepted();

        $this->authenticationManager->method('getOpenAuthentications')->willReturn(new ArrayCollection([]));
        $this->authenticationManager->method('openAuthentication')->willReturn($authentication);

        $this->client->request('GET', '/2fas/index');

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();

        $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/2fas/index', $this->client->getRequest()->getRequestUri());
    }

    public function testCannotCheckCodeTwice()
    {
        $this->loginWithFungio();
        $this->client->request('GET', $this->uri);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testLoginWithSecondFactorWhenUserIsRememberedOnFirstLoginForm()
    {
        $this->loginRemembered();
        $this->openAuthentication(Methods::TOTP);

        $crawler = $this->client->request('GET', '/2fas/index');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/2fas/check', $this->client->getRequest()->getRequestUri());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('form.code.remember_me', [], 'FungioTwoFactorBundle') . '")')->count()
        );
    }

    public function testLoginAndCheckFungioRememberMe()
    {
        $this->login();
        $this->openAuthentication(Methods::TOTP);
        $this->checkCodeAccepted();

        $this->client->request('GET', '/2fas/index');

        $crawler = $this->client->request('GET', $this->uri);
        $form    = $crawler->selectButton($this->loginButton)->form();
        $form['remember_two_factor']->tick();

        $this->client->submit($form, [
            'code'   => '123456',
            '_token' => $this->csrfToken
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/2fas/index', $this->client->getRequest()->getRequestUri());

        $cookieNames = $this->client->getRequest()->cookies->keys();
        $this->assertCount(1, preg_grep('/^FUNGIO_REMEMBERME/', $cookieNames));
    }

    public function testLoginWithoutSecondFactorWhenUserIsRememberedOnFungioForm()
    {
        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->login();
        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, $tokenValue);
        $crawler = $this->client->request('GET', '/2fas/index');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("2FAS - Two Factor Authentication Service")')->count()
        );

        $this->assertTrue($this->client->getRequest()->cookies->has('FUNGIO_REMEMBERME'));
    }

    public function testLoginWithExpiredCookie()
    {
        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();
        $lastUsed->sub(new \DateInterval('PT60S'));

        $this->openAuthentication(Methods::TOTP);
        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, $tokenValue);
        $this->login();

        $crawler = $this->client->request('GET', '/2fas/index');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('form.code.remember_me', [], 'FungioTwoFactorBundle') . '")')->count()
        );
    }

    public function testLoginWithInvalidCookie()
    {
        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->openAuthentication(Methods::TOTP);
        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, base64_encode(random_bytes(64)));
        $this->login();

        $crawler = $this->client->request('GET', '/2fas/index');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('form.code.remember_me', [], 'FungioTwoFactorBundle') . '")')->count()
        );
    }
}
