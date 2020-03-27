<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Config;

use ESD\Core\Plugins\Event\EventDispatcher;
use ESD\Core\Server\Server;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigContext
 * @package ESD\Core\Plugins\Config
 */
class ConfigContext
{
    /**
     * @var array
     */
    protected $contain = [];

    protected $cacheContain = [];

    /**
     * Add a layer of configuration, sort in reverse order of depth
     *
     * @param array $config
     * @param $deep
     */
    public function addDeepConfig(array $config, $deep)
    {
        $this->contain[$deep] = $config;
        krsort($this->contain);

        $this->cache();
        $this->conductConfig($this->contain[$deep]);
        $eventDispatcher = Server::$instance->getContext()->getDeepByClassName(EventDispatcher::class);

        //Try to signal update
        if ($eventDispatcher instanceof EventDispatcher) {
            if (Server::$instance->getProcessManager() != null && Server::$isStart) {
                $eventDispatcher->dispatchProcessEvent(new ConfigChangeEvent(), ...Server::$instance->getProcessManager()->getProcesses());
            } else {
                $eventDispatcher->dispatchEvent(new ConfigChangeEvent());
            }
        }
    }

    /**
     * Append the same layer configuration, sort in reverse order of depth
     *
     * @param array $config
     * @param $deep
     */
    public function appendDeepConfig(array $config, $deep)
    {
        $oldConfig = $this->contain[$deep] ?? null;
        if ($oldConfig != null) {
            $oldConfig = array_replace_recursive($oldConfig, $config);
        } else {
            $oldConfig = $config;
        }
        $this->addDeepConfig($oldConfig, $deep);
    }

    /**
     * Multi-level sequential merge cache
     */
    protected function cache()
    {
        $this->cacheContain = array_replace_recursive(...$this->contain);
    }

    /**
     * Conduct config
     *
     * @param array $config
     */
    protected function conductConfig(array &$config)
    {
        foreach ($config as &$value) {
            if (is_array($value)) {
                $this->conductConfig($value);
            }
            if (is_string($value)) {
                //Handling the information contained in ${}
                $result = [];
                preg_match_all("/\\$\{([^\\$]*)\}/i", $value, $result);
                foreach ($result[1] as &$needConduct) {
                    $defaultArray = explode(":", $needConduct);

                    //Get constant
                    if (defined($defaultArray[0])) {
                        $evn = constant($defaultArray[0]);
                    } else {
                        //Get environment variables
                        $evn = getenv($defaultArray[0]);
                    }

                    //Get the value in config
                    if ($evn === false) {
                        $evn = $this->get($defaultArray[0]);
                    }

                    //Get the default value
                    if (empty($evn)) {
                        $evn = $defaultArray[1] ?? null;
                    }
                    $needConduct = $evn;
                }
                foreach ($result[0] as $key => $needReplace) {
                    $value = str_replace($needReplace, $result[1][$key], $value);
                }
                $this->cache();
            }
        }
    }

    /**
     * 获取a.b.v这种的值，分隔符默认为"."
     * @param $key
     * @param null $default
     * @param string $separator
     * @return array|mixed|null
     */
    public function get($key, $default = null, $separator = ".")
    {
        $arr = explode($separator, $key);
        $result = $this->cacheContain;
        foreach ($arr as $value) {
            $result = $result[$value] ?? null;
            if ($result == null) {
                return $default;
            }
        }
        return $result;
    }

    /**
     * @param int $deep
     * @return array|null
     */
    public function getContainByDeep(int $deep): ?array
    {
        return $this->contain[$deep] ?? null;
    }

    /**
     * @return array
     */
    public function getCacheContain(): array
    {
        return $this->cacheContain;
    }

    public function getCacheContainYaml(): string
    {
        return Yaml::dump($this->cacheContain, 255);
    }
}