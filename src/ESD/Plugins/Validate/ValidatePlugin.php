<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/17
 * Time: 11:36
 */

namespace ESD\Plugins\Validate;


use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\Validate\Annotation\Validated;

class ValidatePlugin extends AbstractPlugin
{
    /**
     * @Validated()
     * @var string
     */
    public $test;
    public function __construct()
    {
        parent::__construct();
        $this->atAfter(AnnotationsScanPlugin::class);
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     * @throws \DI\DependencyException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $pluginInterfaceManager->addPlug(new AnnotationsScanPlugin());
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Validate";
    }

    /**
     * 初始化
     * @param Context $context
     */
    public function beforeServerStart(Context $context)
    {

    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}