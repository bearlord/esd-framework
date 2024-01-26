<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Config;

use ESD\Core\Server\Server;

/**
 * Class BaseConfig
 * @package ESD\Core\Plugins\Config
 */
class BaseConfig
{
    use ToConfigArray;

    /**
     * @var int
     */
    protected static $uuid = 1000;

    /**
     * @var string
     */
    private $configPrefix;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var bool
     */
    private $isArray;

    /**
     * @var null
     */
    private $indexName;

    /**
     * BaseConfig constructor.
     *
     * @param string $prefix
     * @param bool $isArray
     * @param null $indexName
     */
    public function __construct(string $prefix, bool $isArray = false, $indexName = null)
    {
        $this->configPrefix = $prefix;
        $this->isArray = $isArray;
        $this->indexName = $indexName;
    }

    /**
     * Merge config
     *
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function merge()
    {
        $this->config = [];
        $prefix = $this->configPrefix;
        $config = &$this->config;

        if ($this->isArray) {
            if ($this->indexName == null) {
                $index = 0;
            } else {
                $indexName = $this->indexName;
                $index = $this->$indexName;
                if (empty($index)) {
                    throw new ConfigException(sprintf("Error configuration, could not get %s", $indexName));
                }
            }
            $prefix = $prefix . ".$index";
        }

        $prefixList = explode(".", $prefix);
        foreach ($prefixList as $value) {
            $config[$value] = [];
            $config = &$config[$value];
        }

        $config = $this->toConfigArray();

        //Append config context
        Server::$instance->getConfigContext()->appendDeepConfig($this->config, ConfigPlugin::ConfigDeep);

        //Merge config
        $this->config = Server::$instance->getConfigContext()->get($prefix);
        $this->buildFromConfig($this->config);

        DISet(get_class($this), $this);
    }


    /**
     * Process prefix to array
     *
     * @param string $prefix
     * @return array
     */
    protected function processPrefix(string $prefix): array
    {
        $cabinet = [];
        $box = &$cabinet;

        $prefixList = explode(".", $prefix);
        if (empty($prefix)) {
            return [];
        }

        foreach ($prefixList as $value) {
            $box[$value] = [];
            $box = &$box[$value];
        }

        $result = $cabinet;
        unset($cabinet);
        return $result;
    }
}
