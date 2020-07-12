<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Amqp;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Class AmqpPlugin
 * @package ESD\Plugins\Amqp
 */
class AmqpPlugin extends AbstractPlugin
{
    use GetLogger;

    use GetAmqp;

    /**
     * @var AmqpConfig
     */
    protected $amqpConfig;

    /**
     * AmqpPlugin constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->amqpConfig = new AmqpConfig();
        $this->amqpConfig->setAmqpConfigs([]);

        $this->setToDIContainer(AmqpConfig::class, $this->amqpConfig);
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return "AmqpPlugin";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws ConfigException
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        //所有配置合併
        foreach ($this->amqpConfig->getAmqpConfigs() as $config) {
            $config->merge();
        }

        $configs = Server::$instance->getConfigContext()->get(AmqpPoolConfig::key, []);
        foreach ($configs as $key => $value) {
            $amqpPoolConfig = new AmqpPoolConfig();
            $amqpPoolConfig->setName($key);
            $this->amqpConfig->addAmqpPoolConfig($amqpPoolConfig->buildFromConfig($value));
        }

        $amqpProxy = new AmqpProxy();
        $this->setToDIContainer(AmqpChannel::class, $amqpProxy);
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \Exception
     */
    public function beforeProcessStart(Context $context)
    {
        $amqpPool = new AmqpPool();
        if (empty($this->amqpConfig->getAmqpConfigs())) {
            $this->warn("没有amqp配置");
            return;
        }

        foreach ($this->amqpConfig->getAmqpConfigs() as $key => $amqpPoolConfig) {
            $amqpConnection = new AmqpConnection($amqpPoolConfig);
            $amqpPool->addConnection($amqpConnection);
            $this->debug("已添加名为 {$amqpPoolConfig->getName()} 的Amqp连接");
        }

        $context->add("amqpPool", $amqpPool);
        $this->setToDIContainer(AmqpPool::class, $amqpPool);
        $this->ready();
    }
}