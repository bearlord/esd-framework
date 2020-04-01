<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Validate;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Core\Plugin\PluginInterfaceManager;
use ESD\Plugins\AnnotationsScan\AnnotationsScanPlugin;
use ESD\Plugins\Validate\Annotation\Validated;

/**
 * Class ValidatePlugin
 * @package ESD\Plugins\Validate
 */
class ValidatePlugin extends AbstractPlugin
{
    /**
     * @Validated()
     * @var string
     */
    public $test;

    /**
     * ValidatePlugin constructor.
     */
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
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Validate";
    }

    /**
     * @inheritDoc
     * @param Context $context
     */
    public function beforeServerStart(Context $context)
    {

    }

    /**
     * @inheritDoc
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}