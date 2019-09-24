<?php

namespace Fungio\TwoFactorBundle\Cache;

use Traversable;
use \SplFileObject;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Save cache data into file under the symfony cache_dir directory
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package Fungio\TwoFactorBundle\Cache
 */
class FileCacheStorage implements CacheInterface
{
    const FILE_NAME = 'fungio_two_factor.cache';

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @param Filesystem $filesystem
     * @param string     $cacheDir
     */
    public function __construct(Filesystem $filesystem, $cacheDir)
    {
        $this->cacheDir   = $cacheDir;
        $this->fileSystem = $filesystem;

        if (!$this->fileSystem->exists($this->getFilePath())) {
            $this->fileSystem->touch($this->getFilePath());
        }
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $this->checkKey($key);

        return array_key_exists($key, $this->readCache());
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->checkKey($key);

        $cache       = $this->readCache();
        $cache[$key] = $value;

        $this->writeCache($cache);
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $this->checkParameter($values);

        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        $cache = $this->readCache();

        return $cache[$key];
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $this->checkParameter($keys);

        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->get($key, $default);
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        if (!$this->has($key)) {
            return;
        }

        $cache = $this->readCache();
        unset($cache[$key]);

        $this->writeCache($cache);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        $this->checkParameter($keys);

        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->writeCache([]);
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    private function checkKey($key)
    {
        if (!in_array($key, CacheKeys::getAvailableKeys())) {
            throw new InvalidArgumentException('Cache key is not valid.');
        }
    }

    /**
     * @param mixed $parameter
     *
     * @throws InvalidArgumentException
     */
    private function checkParameter($parameter)
    {
        if (is_array($parameter) || $parameter instanceof Traversable) {
            return;
        }

        throw new InvalidArgumentException('Cache parameter is not valid.');
    }

    /**
     * @return array
     */
    private function readCache()
    {
        $fileObject = new SplFileObject($this->getFilePath(), 'r');

        if (0 === $fileObject->getSize()) {
            return [];
        }

        $cache = unserialize($fileObject->fread($fileObject->getSize()));

        if (is_array($cache)) {
            return $cache;
        }

        return [];
    }

    /**
     * @param array $values
     */
    private function writeCache(array $values)
    {
        $this->fileSystem->dumpFile($this->getFilePath(), serialize($values));
    }

    /**
     * @return string
     */
    private function getFilePath()
    {
        return $this->cacheDir . '/' . self::FILE_NAME;
    }
}