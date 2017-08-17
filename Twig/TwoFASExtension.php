<?php

namespace TwoFAS\TwoFactorBundle\Twig;

use InvalidArgumentException;
use SplFileInfo;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use TwoFAS\TwoFactorBundle\Util\ConfigurationChecker;

/**
 * Methods in this extension may be used in twig files.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Twig
 */
class TwoFASExtension extends Twig_Extension
{
    /**
     * @var ConfigurationChecker
     */
    private $configurationChecker;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * TwoFASExtension constructor.
     *
     * @param ConfigurationChecker          $configurationChecker
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $rootDir
     */
    public function __construct(
        ConfigurationChecker $configurationChecker,
        AuthorizationCheckerInterface $authorizationChecker,
        $rootDir
    ) {
        $this->configurationChecker = $configurationChecker;
        $this->authorizationChecker = $authorizationChecker;
        $this->rootDir              = $rootDir;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('canRenderTwoFAS', [$this, 'canRenderTwoFAS'])
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('fileMTime', [$this, 'fileMTime'])
        ];
    }

    /**
     * Method can use to decide to render 2FAS menu (only after second factor or not configured yet)
     *
     * @param string $role
     *
     * @return bool
     */
    public function canRenderTwoFAS($role)
    {
        return ($this->isRemembered($role) || $this->isNotConfigured($role) || $this->isDisabled($role));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function fileMTime($path)
    {
        $filePath = new SplFileInfo($this->rootDir . '/../web' . $path);

        if (!$filePath->isFile()) {
            throw new InvalidArgumentException('File "' . $path . '" does not exists.');
        }

        return $path . '?' . $filePath->getMTime();
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    private function isRemembered($role)
    {
        return $this->authorizationChecker->isGranted('IS_AUTHENTICATED_TWO_FACTOR_REMEMBERED') && $this->authorizationChecker->isGranted($role);
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    private function isNotConfigured($role)
    {
        return $this->authorizationChecker->isGranted($role) && !$this->configurationChecker->isSecondFactorEnabledForUser();
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    private function isDisabled($role)
    {
        return $this->authorizationChecker->isGranted($role) && !$this->configurationChecker->isTwoFASEnabled();
    }
}