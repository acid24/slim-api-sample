<?php

namespace Salexandru\Bootstrap;

use Slim\Collection;
use Slim\Http\Environment;
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

    private function resolvePathToConfigFile()
    {
        /** @var Environment $environment */
        $environment = $this->container->get('environment');

        $appEnv = $environment->get('APPLICATION_ENV', 'production');

        if (defined('CONFIG_DIR')) {
            $dir = CONFIG_DIR;
        } else {
            // determine the location by hand if the CONFIG_DIR constant
            // is not available
            $dir = realpath(__DIR__ . '/../../../config');
        }

        $pathToConfigFile = "$dir/config.$appEnv.ini";
        if (!file_exists($pathToConfigFile)) {
            $pathToConfigFile = "$dir/config.ini";
        }

        return $pathToConfigFile;
    }

    private function loadConfig()
    {
        // @todo add logic here to add/retrieve the configuration from cache (APC makes sense)
        $pathToConfigFile = $this->resolvePathToConfigFile();

        $iniReader = new IniReader();
        $config = $iniReader->fromFile($pathToConfigFile);

        return $config;
    }
}
