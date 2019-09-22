<?php

namespace Fungio\TwoFactorBundle\Tests\Controller;

use Fungio\Api\Methods;

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

        $this->removeButton = $this->translator->trans('dashboard.trusted_devices.remove_button', [], 'FungioTwoFactorBundle');

        $this->loginWithFungio();

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

    public function testFungioEnabled()
    {
        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.two_factor.enabled', [], 'FungioTwoFactorBundle') . '")')->count()
        );
    }

    public function testFungioDisabled()
    {
        $this->fungioStatus->setValue(false);

        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.two_factor.disabled', [], 'FungioTwoFactorBundle') . '")')->count()
        );
    }

    public function testChannelTotpActive()
    {
        $this->fungioUser->enableChannel(Methods::TOTP);

        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(
            $this->translator->trans('dashboard.two_factor.enabled', [], 'FungioTwoFactorBundle'),
            $crawler->filter('span.fungio-channel-active')->text()
        );
    }

    public function testEmptyTrustedDevicesList()
    {
        $crawler = $this->client->request('GET', $this->uri);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.trusted_devices.empty', [], 'FungioTwoFactorBundle') . '")')->count()
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
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.trusted_devices.empty', [], 'FungioTwoFactorBundle') . '")')->count()
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
            $crawler->filter('html:contains("' . $this->translator->trans('dashboard.trusted_devices.empty', [], 'FungioTwoFactorBundle') . '")')->count()
        );
    }
}
