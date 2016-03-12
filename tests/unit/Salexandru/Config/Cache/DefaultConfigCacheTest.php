<?php

namespace Salexandru\Config\Cache;

use Mockery as m;
use Doctrine\Common\Cache\Cache as CacheProvider;
use Salexandru\Config\Cache\AdapterInterface as ConfigCache;

class DefaultConfigCacheTest extends \PHPUnit_Framework_TestCase
{

    public function testGetCachedConfigReturnsNullIfConfigNotPresentInCache()
    {
        $cacheProvider = m::mock(CacheProvider::class)
            ->shouldReceive('contains')
            ->with(ConfigCache::CACHE_KEY)
            ->once()
            ->andReturn(false)
            ->getMock();

        $configCache = new DefaultConfigCache($cacheProvider);
        $config = $configCache->getCachedConfig();

        $this->assertNull($config);
    }

    public function testGetCachedConfig()
    {
        $expected = ['something' => 'test'];

        $cacheProvider = m::mock(CacheProvider::class);
        $cacheProvider->shouldReceive('contains')
            ->with(ConfigCache::CACHE_KEY)
            ->once()
            ->andReturn(true);
        $cacheProvider->shouldReceive('fetch')
            ->with(ConfigCache::CACHE_KEY)
            ->once()
            ->andReturn($expected);

        $configCache = new DefaultConfigCache($cacheProvider);
        $actual = $configCache->getCachedConfig();

        $this->assertEquals($expected, $actual);
    }

    public function testCacheConfig()
    {
        $config = ['something' => 'test'];

        $cacheProvider = m::mock(CacheProvider::class)
            ->shouldReceive('save')
            ->with(ConfigCache::CACHE_KEY, $config)
            ->once()
            ->getMock();

        $configCache = new DefaultConfigCache($cacheProvider);
        $configCache->cache($config);
    }
}
