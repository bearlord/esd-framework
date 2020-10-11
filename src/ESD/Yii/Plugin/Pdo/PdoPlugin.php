<?php

/**
 * ESD Yii pdo plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Core\Context\Context;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Yii\Base\Application;
use ESD\Yii\Plugin\YiiPlugin;
use ESD\Yii\Yii;

/**
 * Class PdoPlugin
 * @package ESD\Yii\Plugin\Pdo
 */
class PdoPlugin extends \ESD\Core\Plugin\AbstractPlugin
{
    use GetLogger;

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
        return 'YiiPdo';
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
        $configs = Server::$instance->getConfigContext()->get("yii.db");

        foreach ($configs as $key => $config) {
            $configObject = new Config($key);
            $this->configs->addConfig($configObject->buildFromConfig($config));

            $slaveConfigs = $this->getSlaveConfigs($config);
            if (!empty($slaveConfigs)) {
                foreach ($slaveConfigs as $slaveKey => $slaveConfig) {
                    $_salveKey = sprintf("%s.slave.%s", $key, $slaveKey);
                    $slaveConfigObject = new Config($_salveKey);
                    $this->configs->addConfig($slaveConfigObject->buildFromConfig($slaveConfig));
                }
            }

            $masterConfigs = $this->getMasterConfigs($config);
            if (!empty($masterConfigs)) {
                foreach ($masterConfigs as $masterKey => $masterConfig) {
                    $_masterKey = sprintf("%s.master.%s", $key, $slaveKey);
                    $masterConfigObject = new Config($_masterKey);
                    $this->configs->addConfig($masterConfigObject->buildFromConfig($masterConfigs));
                }
            }
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
        $pools = new PdoPools();

        $configs = $this->configs->getConfigs();
        if (empty($configs)) {
            $this->warn(Yii::t('esd', 'PDO configuration not found'));
            return false;
        }

        foreach ($configs as $key => $config) {
            $pool = new PdoPool($config);
            $pools->addPool($pool);
            $this->debug(Yii::t('esd', '{driverName} connection pool named {name} created', [
                'driverName' => sprintf("Pdo.%s", $config->getDriverName()),
                'name' => $config->getName()
            ]));
        }

        $context->add("PdoPools", $pools);
        $this->setToDIContainer(PdoPools::class, $pools);
        $this->setToDIContainer(PdoPool::class, $pools->getPool());

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

    /**
     * @param $config
     * @return array|bool
     */
    protected function getMasterConfigs($config)
    {
        if (empty($config['masters'])) {
            return false;
        }
        if (empty($config['masterConfig'])) {
            return false;
        }
        $row = [];
        foreach ($config['masters'] as $k => $v) {
            $v['username'] = $config['masterConfig']['username'];
            $v['password'] = $config['masterConfig']['password'];
            $v['poolMaxNumber'] = $config['masterConfig']['poolMaxNumber'];
            $v['charset'] = $config['charset'];
            $v['tablePrefix'] = $config['tablePrefix'];
            $v['enableSchemaCache'] = $config['enableSchemaCache'];
            $v['schemaCacheDuration'] = $config['schemaCacheDuration'];
            $v['schemaCache'] = $config['schemaCache'];
            $row[] = $v;
        }
        return $row;
    }

    /**
     * @param $config
     * @return array|bool
     */
    protected function getSlaveConfigs($config)
    {
        if (empty($config['slaves'])) {
            return false;
        }
        if (empty($config['slaveConfig'])) {
            return false;
        }
        $row = [];
        foreach ($config['slaves'] as $k => $v) {
            $v['username'] = $config['slaveConfig']['username'];
            $v['password'] = $config['slaveConfig']['password'];
            $v['poolMaxNumber'] = $config['slaveConfig']['poolMaxNumber'];
            $v['charset'] = $config['charset'];
            $v['tablePrefix'] = $config['tablePrefix'];
            $v['enableSchemaCache'] = $config['enableSchemaCache'];
            $v['schemaCacheDuration'] = $config['schemaCacheDuration'];
            $v['schemaCache'] = $config['schemaCache'];
            $row [] = $v;
        }
        return $row;
    }
}