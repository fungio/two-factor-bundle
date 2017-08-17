<?php

namespace TwoFAS\TwoFactorBundle\Tests\Controller;

use TwoFAS\Api\Methods;
use TwoFAS\Api\TotpSecretGenerator;

class ChannelControllerTest extends ControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loginWithTwoFAS();
    }

    public function testEnableChannel()
    {
        $this->twoFASUser->disableChannel(Methods::TOTP);
        $this->integrationUser->setTotpSecret(TotpSecretGenerator::generate());

        $crawler = $this->client->request('POST', '/2fas/channel/enable', [
            'channel' => Methods::TOTP
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('channel.success_enabled', []) . '")')->count()
        );

        $this->assertTrue($this->twoFASUser->isChannelEnabled(Methods::TOTP));
    }

    public function testCannotEnableChannelWhenNotConfigured()
    {
        $crawler = $this->client->request('POST', '/2fas/channel/enable', [
            'channel' => Methods::TOTP
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('channel.cannot_enable', []) . '")')->count()
        );

        $this->assertFalse($this->twoFASUser->isChannelEnabled(Methods::TOTP));
    }

    public function testDisableChannel()
    {
        $this->twoFASUser->enableChannel(Methods::TOTP);
        $this->integrationUser->setTotpSecret(TotpSecretGenerator::generate());

        $crawler = $this->client->request('POST', '/2fas/channel/disable', [
            'channel' => Methods::TOTP
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("' . $this->translator->trans('channel.success_disabled', []) . '")')->count()
        );

        $this->assertFalse($this->twoFASUser->isChannelEnabled(Methods::TOTP));
    }
}
