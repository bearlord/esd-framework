<?php

/**
 * ESD Yii mongodb plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Mongodb;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Core\Context\Context;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Yii\Base\Application;
use ESD\Yii\Plugin\Mongodb\MongodbPool;
use ESD\Yii\Plugin\YiiPlugin;
use ESD\Yii\Yii;

/**
 * Class MongodbPlugin
 * @package ESD\Yii\Plugin\Mongodb
 */
class MongodbPlugin extends \ESD\Core\Plugin\AbstractPlugin
{
    use GetLogger;

    /** @var Configs  */
    protected $configs;

    /**
     * PdoPlugin constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->atAfter(YiiPlugin::class);
        $this->configs = new Configs();
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return 'YiiMongodb';
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     */
    public function init(Context $context)
    {
        return parent::init($context);
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $configs = Server::$instance->getConfigContext()->get("yii.mongodb");

        foreach ($configs as $key => $config) {
            $configObject = new Config($key);
            $this->configs->addConfig($configObject->buildFromConfig($config));
        }
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     * @throws \ESD\Yii\Db\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        $pools = new MongodbPools();

        $configs = $this->configs->getConfigs();
        if (empty($configs)) {
            $this->warn(Yii::t('esd', 'Mongodb configuration not found'));
            return false;
        }

        foreach ($configs as $key => $config) {
            $pool = new MongodbPool($config);
            $pools->addPool($pool);
            $this->debug(Yii::t('esd', '{driverName} connection pool named {name} created', [
                'driverName' => ucfirst($config->getDriverName()),
                'name' => $config->getName()
            ]));
        }

        $context->add("MongodbPool", $pools);
        $this->setToDIContainer(MongodbPools::class, $pools);
        $this->setToDIContainer(MongodbPool::class, $pools->getPool());

        $this->ready();
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
    }
}