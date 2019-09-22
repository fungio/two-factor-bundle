<?php

namespace Fungio\TwoFactorBundle\DependencyInjection\Factory;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use Fungio\Api\Fungio;
use Fungio\Encryption\Cryptographer;
use Fungio\TwoFactorBundle\Cache\CacheKeys;
use Fungio\TwoFactorBundle\Model\Entity\OptionInterface;
use Fungio\TwoFactorBundle\Model\Persister\ObjectPersisterInterface;
use Fungio\TwoFactorBundle\FungioTwoFactorBundle;

/**
 * Factory for API SDK.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\DependencyInjection\Factory
 */
class ApiSdkFactory
{
    /**
     * @var ObjectPersisterInterface
     */
    private $optionPersister;

    /**
     * @var Cryptographer
     */
    private $cryptographer;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var null|string
     */
    private $appName;

    /**
     * @var null|string
     */
    private $baseUrl;

    /**
     * FungioFactory constructor.
     *
     * @param ObjectPersisterInterface $optionPersister
     * @param Cryptographer            $cryptographer
     * @param RequestStack             $requestStack
     * @param CacheInterface           $cache
     * @param null|string              $appName
     * @param null|string              $apiUrl
     */
    public function __construct(
        ObjectPersisterInterface $optionPersister,
        Cryptographer $cryptographer,
        RequestStack $requestStack,
        CacheInterface $cache,
        $appName = null,
        $apiUrl = null
    ) {
        $this->optionPersister = $optionPersister;
        $this->cryptographer   = $cryptographer;
        $this->requestStack    = $requestStack;
        $this->cache           = $cache;
        $this->appName         = $appName;
        $this->baseUrl         = $apiUrl;
    }

    /**
     * @return Fungio
     */
    public function createInstance()
    {
        $headers = [
            'Plugin-Version' => FungioTwoFactorBundle::VERSION,
            'Php-Version'    => phpversion(),
            'App-Version'    => Kernel::VERSION,
            'App-Name'       => $this->appName,
            'App-Url'        => $this->getUrl()
        ];

        $fungio = new Fungio($this->getLogin(), $this->getToken(), $headers);

        if (!is_null($this->baseUrl)) {
            $fungio->setBaseUrl($this->baseUrl);
        }

        return $fungio;
    }

    /**
     * @return null|string
     */
    private function getUrl()
    {
        $request = $this->requestStack->getMasterRequest();

        if ($request instanceof Request) {
            return $request->getHttpHost();
        }

        return null;
    }

    /**
     * @return null|string
     */
    private function getLogin()
    {
        return $this->getOption(CacheKeys::LOGIN, OptionInterface::LOGIN);
    }

    /**
     * @return null|string
     */
    private function getToken()
    {
        return $this->getOption(CacheKeys::TOKEN, OptionInterface::TOKEN);
    }

    /**
     * @param string $cacheKey
     * @param string $optionName
     *
     * @return null|string
     */
    private function getOption($cacheKey, $optionName)
    {
        try {
            /** @var OptionInterface|null $option */
            if ($this->cache->has($cacheKey)) {
                $option = $this->cache->get($cacheKey);
            } else {
                $option = $this->optionPersister->getRepository()->findOneBy(['name' => $optionName]);
                $this->cache->set($cacheKey, $option);
            }

            if (!is_null($option)) {
                return $this->cryptographer->decrypt($option->getValue());
            }

        } catch (TableNotFoundException $e) {
            //do nothing (eq. schema not exists)
            //so Fungio must be empty
        }

        return null;
    }
}