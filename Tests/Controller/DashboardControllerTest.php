<?php

namespace TwoFAS\TwoFactorBundle\Tests\Controller;

use TwoFAS\Api\Methods;

class DashboardControllerTest extends ControllerTestCase
{
    /**
     * @var string
     */
    private $removeButton;

    /**
     * @var string
     */
    private $uri = '/2fas/index';

    public function setUp()
    {
        parent::setUp();

        $this->removeButton = $this->translator->trans('dashboard.trusted_devices.remove_button', [], 'TwoFASTwoFactorBundle');

        $this->loginWithTwoFAS();

        $this->mockTrustedDeviceVoter();
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("2FAS - Two Factor Authentication Service")')->count()
        );
    }

    public function testTwoFASEnabled()
    {
        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.two_factor.enabled', [], 'TwoFASTwoFactorBundle') . '")')->count()
        );
    }

    public function testTwoFASDisabled()
    {
        $this->twoFASStatus->setValue(false);

        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.two_factor.disabled', [], 'TwoFASTwoFactorBundle') . '")')->count()
        );
    }

    public function testChannelTotpActive()
    {
        $this->twoFASUser->enableChannel(Methods::TOTP);

        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(
            $this->translator->trans('dashboard.two_factor.enabled', [], 'TwoFASTwoFactorBundle'),
            $crawler->filter('span.twofas-channel-active')->text()
        );
    }

    public function testEmptyTrustedDevicesList()
    {
        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.trusted_devices.empty', [], 'TwoFASTwoFactorBundle') . '")')->count()
        );
    }

    public function testTrustedDevicesList()
    {
        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, $tokenValue);
        $crawler = $this->client->request('GET', '/2fas/index');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("an unknown browser")')->count()
        );
    }

    public function testRemoveTrustedDevice()
    {
        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, $tokenValue);

        $crawler = $this->client->request('GET', '/2fas/index');
        $form    = $crawler->selectButton($this->removeButton)->form();
        $crawler = $this->client->submit($form);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('trusted_devices.remove.success', [], 'messages') . '")')->count()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.trusted_devices.empty', [], 'TwoFASTwoFactorBundle') . '")')->count()
        );
    }

    public function testRemoveTrustedDeviceThatHasAlreadyBeenRemoved()
    {
        $series     = base64_encode(random_bytes(64));
        $tokenValue = base64_encode(random_bytes(64));
        $lastUsed   = new \DateTime();

        $this->generateRememberMeToken($series, $tokenValue, $lastUsed);
        $this->generateCookie($series, $tokenValue);

        $crawler = $this->client->request('GET', '/2fas/index');
        $form    = $crawler->selectButton($this->removeButton)->form();

        $token = $this->tokenRepository->findOneBy(['series' => $series]);
        $this->tokenRepository->remove($token);

        $crawler = $this->client->submit($form);

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('trusted_devices.remove.error', [], 'messages') . '")')->count()
        );

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.trusted_devices.empty', [], 'TwoFASTwoFactorBundle') . '")')->count()
        );
    }
}
