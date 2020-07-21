<?php
/**
 * ESD Yii plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Yii\Base\Application;

class YiiPlugin extends \ESD\Core\PlugIn\AbstractPlugin
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