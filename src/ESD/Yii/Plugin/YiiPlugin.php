<?php
/**
 * ESD Yii plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Yii\Base\Application;

/**
 * Class YiiPlugin
 * @package ESD\Yii\Plugin
 */
class YiiPlugin extends AbstractPlugin
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Yii';
    }

    /**
     * @param Context $context
     * @return mixed|void
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function beforeServerStart(Context $context)
    {
        Application::instance();
    }

    /**
     * @param Context $context
     * @return mixed|void
     */
    public function beforeProcessStart(Context $context)
    {
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