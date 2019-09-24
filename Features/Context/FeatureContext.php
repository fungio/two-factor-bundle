<?php

namespace Fungio\TwoFactorBundle\Features\Context;

use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit_Framework_Assert;

/**
 * Context for all features.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Features\Context
 */
class FeatureContext extends MinkContext
{
    /**
     * @var string
     */
    private $imageSrc;

    /**
     * @Given I am logged in as :login with password :password
     */
    public function iAmLoggedInAsWithPassword($login, $password)
    {
        $this->visit('/2fas/login');
        $this->fillField('username', $login);
        $this->fillField('password', $password);
        $this->pressButton('_submit');
    }

    /**
     * @Given I am logged in as :login with password :password and remember me checked
     */
    public function iAmLoggedInAsWithPasswordAndRememberMeChecked($login, $password)
    {
        $this->visit('/2fas/login');
        $this->fillField('username', $login);
        $this->fillField('password', $password);
        $this->checkOption('remember_me');
        $this->pressButton('_submit');
    }

    /**
     * @Given /^I wait for (\d+) seconds$/
     */
    public function iWaitForSeconds($seconds)
    {
        $this->getSession()->wait($seconds * 1000);
    }

    /**
     * @Then I should see totp-secret
     */
    public function iShouldSeeTotpSecret()
    {
        $page       = $this->getSession()->getPage();
        $totpSecret = $page->find('css', '#fungio-totp-secret');

        if (null === $totpSecret) {
            throw new \LogicException('Could not find Totp secret');
        }

        if (!$totpSecret->isVisible()) {
            throw new \LogicException('Totp secret is not visible...');
        }

        if (empty($totpSecret->getText())) {
            throw new \LogicException('Totp secret is empty.');
        }
    }

    /**
     * @Then I should see qr-code
     */
    public function iShouldSeeQrCode()
    {
        $qrCode = $this->getQrCodeImage();

        $this->imageSrc = $qrCode->getAttribute('src');
    }

    /**
     * @return NodeElement|null
     */
    private function getQrCodeImage()
    {
        $page   = $this->getSession()->getPage();
        $qrCode = $page->find('css', 'div.fungio-totp-qrcode > img');

        if (null === $qrCode) {
            throw new \LogicException('Could not find Qr code');
        }

        return $qrCode;
    }

    /**
     * @Then I should see new qr-code
     */
    public function iShouldSeeNewQrCode()
    {
        $qrCode = $this->getQrCodeImage();

        PHPUnit_Framework_Assert::assertNotEquals($this->imageSrc, $qrCode->getAttribute('src'), 'Qr Code is not reloaded.');
    }

    /**
     * @Then I restart session with :cookieName
     */
    public function iRestartSessionWith($cookieName)
    {
        $cookies = $this->getCookies($cookieName);

        $this->getSession()->restart();

        $this->visitPath('/');

        $this->setCookies($cookies);
    }

    /**
     * @param string $cookieName
     *
     * @return array
     */
    private function getCookies($cookieName)
    {
        $cookieNames = explode(',', $cookieName);

        return array_map(function($cookieName) {
            return [$cookieName => $this->getSession()->getCookie($cookieName)];
        }, $cookieNames);
    }

    /**
     * @param array $cookies
     */
    private function setCookies(array $cookies)
    {
        array_walk($cookies, function($cookieData) {
            array_walk($cookieData, function($cookie, $name) {
                $this->getSession()->setCookie($name, $cookie);
            });
        });
    }

    /**
     * @Then I should see new trusted device in list
     */
    public function iShouldSeeNewTrustedDeviceInList()
    {
        $page  = $this->getSession()->getPage();
        $table = $page->find('css', '.fungio-table');
        $rows  = $table->findAll('css', 'tr');

        PHPUnit_Framework_Assert::assertCount(2, $rows);

        $trustedDeviceNode = $rows[1];

        $cols = $trustedDeviceNode->findAll('css', 'td');

        PHPUnit_Framework_Assert::assertCount(4, $cols);
    }

    /**
     * @Then I should see that all channels are disabled
     */
    public function iShouldSeeThatAllChannelsAreDisabled()
    {
        $page  = $this->getSession()->getPage();
        $nodes = $page->findAll('css', '.fungio-channel-active');

        PHPUnit_Framework_Assert::assertCount(3, $nodes);

        array_map(function(NodeElement $node) {
            PHPUnit_Framework_Assert::assertEquals('Disabled', $node->getText());
        }, $nodes);
    }

    /**
     * @Given I should see that :name channel is :status
     */
    public function iShouldSeeThatChannelIs($name, $status)
    {
        $name = strtoupper($name);
        $status = ucfirst(strtolower($status));
        $page  = $this->getSession()->getPage();
        $nodes = $page->findAll('css', '.fungio-channel');

        $matchingStatuses = array_map(function(NodeElement $node) use ($name, $status) {
            $currentStatus = $node->find('css', '.fungio-channel-active')->getText();
            $channel       = $node->find('css', 'h4')->getText();

            if ($channel == $name) {
                return $currentStatus;
            }

        }, $nodes);

        PHPUnit_Framework_Assert::assertContains($status, $matchingStatuses);
    }
}
