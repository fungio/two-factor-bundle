<?php

namespace TwoFAS\TwoFactorBundle\Cache;

use Traversable;
use Psr\SimpleCache\CacheInterface;

/**
 * Used if cache is disabled.
 *
 * @author Krystian Dąbek <k.dabek@2fas.com>
 * @package TwoFAS\TwoFactorBundle\Cache
 */
class EmptyCacheStorage implements CacheInterface
{
    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {

    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {

    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        return $default;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $this->checkParameter($keys);

        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $default;
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {

    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {

    }

    /**
     * @inheritDoc
     */
    public function clear()
    {

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
}