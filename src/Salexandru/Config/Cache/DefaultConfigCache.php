<?php

namespace Salexandru\Config\Cache;

use Doctrine\Common\Cache\Cache as CacheProvider;

class DefaultConfigCache implements AdapterInterface
{

    private $cacheProvider;

    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function getCachedConfig()
    {
        if ($this->cacheProvider->contains(self::CACHE_KEY)) {
            return $this->cacheProvider->fetch(self::CACHE_KEY);
        }

        return null;
    }

    public function cache(array $config)
    {
        $this->cacheProvider->save(self::CACHE_KEY, $config);
    }
}
