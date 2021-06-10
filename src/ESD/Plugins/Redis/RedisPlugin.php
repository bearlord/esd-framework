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
     * @var Configs
     */
    protected $configs;

    /**
     * RedisPlugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->configs = new Configs();
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
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        ini_set('default_socket_timeout', '-1');
        foreach ($this->configs->getConfigs() as $config) {
            $config->merge();
        }

        $configs = Server::$instance->getConfigContext()->get('redis', []);
        foreach ($configs as $key => $value) {
            $configObject = new Config($key);
            $this->configs->addConfig($configObject->buildFromConfig($value));
        }

        $redisProxy = new RedisProxy();
        $this->setToDIContainer(\Redis::class, $redisProxy);
        $this->setToDIContainer(Redis::class, $redisProxy);
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws RedisException
     */
    public function beforeProcessStart(Context $context)
    {
        ini_set('default_socket_timeout', '-1');
        $pools = new RedisPools();

        $configs = $this->configs->getConfigs();
        if (empty($configs)) {
            $this->warn(Yii::t('esd', '{name} configuration not found', [
                'name' => 'Redis'
            ]));
        }
        foreach ($configs as $key => $config) {
            $pool = new RedisPool($config);
            $pools->addPool($pool);
            $this->debug(Yii::t('esd', '{driverName} connection pool named {name} created', [
                'driverName' => sprintf("%s", 'Redis'),
                'name' => $config->getName()
            ]));
        }

        $context->add("redisPool", $pools);
        $this->setToDIContainer(RedisPools::class, $pools);
        $this->setToDIContainer(RedisPool::class, $pools->getPool());
        $this->ready();
    }
}