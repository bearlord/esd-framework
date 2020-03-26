<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\PlugIn;

use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;

interface PluginInterface
{
    /**
     * Get ready channel
     *
     * @return Channel
     */
    public function getReadyChannel();

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * initialization
     *
     * @param Context $context
     * @return mixed
     */
    public function init(Context $context);

    /**
     * Before server start
     *
     * @param Context $context
     * @return mixed
     */
    public function beforeServerStart(Context $context);

    /**
     * Before process start
     *
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context);

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager);

}