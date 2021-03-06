<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Redis;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Yii\Yii;

/**
 * Class RedisPlugin
 * @package ESD\Plugins\Redis
 */
class RedisPlugin extends AbstractPlugin
{
    use GetLogger;

    /**
     * @var RedisConfig
     */
    protected $redisConfig;

    /**
     * RedisPlugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->redisConfig = new RedisConfig();
        $this->redisConfig->setRedisConfigs([]);
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Redis";
    }


    /**
     * @return array
     */
    public function getConfigList(): array
    {
        return $this->redisConfig->getRedisConfigs();
    }

    /**
     * @param RedisOneConfig[] $configList
     */
    public function setConfigList(array $configList): void
    {
        $this->redisConfig->setRedisConfigs($configList);
    }

    /**
     * @param RedisOneConfig $redisOneConfig
     */
    public function addConfigList(RedisOneConfig $redisOneConfig): void
    {
        $this->redisConfig->addRedisOneConfig($redisOneConfig);
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        ini_set('default_socket_timeout', '-1');
        foreach ($this->redisConfig->getRedisConfigs() as $config) {
            $config->merge();
        }

        $configs = Server::$instance->getConfigContext()->get(RedisOneConfig::KEY, []);
        foreach ($configs as $key => $value) {
            $redisOneConfig = new RedisOneConfig($key);
            $this->redisConfig->addRedisOneConfig($redisOneConfig->buildFromConfig($value));
        }

        $redisProxy = new RedisProxy();
        $this->setToDIContainer(\Redis::class, $redisProxy);
        $this->setToDIContainer(Redis::class, $redisProxy);
        $this->setToDIContainer(RedisConfig::class, $this->redisConfig);
        return;
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws RedisException
     */
    public function beforeProcessStart(Context $context)
    {
        ini_set('default_socket_timeout', '-1');
        $redisManyPool = new RedisManyPool();
        if (empty($this->redisConfig->getRedisConfigs())) {
            $this->warn(Yii::t('esd', '{name} configuration not found', [
                'name' => 'Redis'
            ]));
        }
        foreach ($this->redisConfig->getRedisConfigs() as $key => $config) {
            $redisPool = new RedisPool($config);
            $redisManyPool->addPool($redisPool);
            $this->debug(Yii::t('esd', '{driverName} connection pool named {name} created', [
                'driverName' => sprintf("%s", 'Redis'),
                'name' => $config->getName()
            ]));
        }
        $context->add("redisPool", $redisManyPool);
        $this->setToDIContainer(RedisManyPool::class, $redisManyPool);
        $this->setToDIContainer(RedisPool::class, $redisManyPool->getPool());
        $this->ready();
    }
}