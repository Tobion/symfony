<?php

namespace Symfony\Bundle\FrameworkBundle\Secret;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CachedSecretStorage implements SecretStorageInterface
{
    /**
     * @var SecretStorageInterface
     */
    private $decoratedStorage;
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(SecretStorageInterface $decoratedStorage, CacheItemPoolInterface $cache)
    {
        $this->decoratedStorage = $decoratedStorage;
        $this->cache = $cache;
    }

    public function getSecret(string $key): string
    {
        $cacheItem = $this->cache->getItem($this->getCacheKey($key));
        if (!$cacheItem->isHit()) {
            $cacheItem->set($this->decoratedStorage->getSecret($key));

            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }

    public function putSecret(string $key, string $secret): void
    {
        $this->decoratedStorage->putSecret($key, $secret);
        $this->cache->deleteItem($this->getCacheKey($key));
    }

    public function deleteSecret(string $key): void
    {
        $this->decoratedStorage->deleteSecret($key);
        $this->cache->deleteItem($this->getCacheKey($key));
    }

    public function listKeys(): iterable
    {
        return $this->decoratedStorage->listKeys();
    }

    private function getCacheKey(string $key){
        return md5(__CLASS__.$key);
    }
}
