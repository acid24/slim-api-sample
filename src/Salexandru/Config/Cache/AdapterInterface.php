<?php

namespace Salexandru\Config\Cache;

interface AdapterInterface
{

    const CACHE_KEY = 'app.config';

    public function getCachedConfig();
    public function cache(array $config);
}
