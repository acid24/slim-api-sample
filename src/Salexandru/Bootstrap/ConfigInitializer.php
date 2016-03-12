<?php

namespace Salexandru\Bootstrap;

use Salexandru\Bootstrap\Exception\RuntimeException;
use Salexandru\Config\Cache\AdapterInterface as ConfigCache;
use Slim\Collection;
use Zend\Config\Reader\Ini as IniReader;

class ConfigInitializer extends AbstractResourceInitializer
{

    /**
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * @return void
     */
    public function run()
    {
        $config = $this->loadConfig();

        /** @var Collection $settings */
        $settings = $this->container->get('settings');
        $settings->replace($config);

        if (isset($config['php'])) {
            $this->applyPhpSettings($config['php']);
        }
    }

    private function applyPhpSettings(array $settings, $prefix = '')
    {
        foreach ($settings as $key => $value) {
            $key = empty($prefix) ? $key : $prefix . $key;
            if (is_scalar($value)) {
                ini_set($key, $value);
            } elseif (is_array($value)) {
                $this->applyPhpSettings($value, $key . '.');
            }
        }
    }

    private function loadConfig()
    {
        /** @var ConfigCache $configCache */
        $configCache = $this->container->get('defaultConfigCache');
        if (null !== ($config = $configCache->getCachedConfig())) {
            return $config;
        }

        $pathToConfigFile = CONFIG_DIR . '/config.ini';
        clearstatcache(true, $pathToConfigFile);

        if (!file_exists($pathToConfigFile)) {
            throw new RuntimeException("$pathToConfigFile does not exist");
        }

        $iniReader = new IniReader();
        $config = $iniReader->fromFile($pathToConfigFile);

        $configCache->cache($config);

        return $config;
    }
}
