<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Pack;

use ESD\Core\Server\Config\PortConfig;
use ESD\Plugins\Pack\PackTool\LenJsonPack;
use ESD\Plugins\Pack\PackTool\NonJsonPack;

/**
 * Class PackConfig
 * @package ESD\Plugins\Pack
 */
class PackConfig extends PortConfig
{
    /**
     * @var string
     */
    protected $packTool;

    /**
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function merge()
    {
        if ($this->isOpenWebsocketProtocol() && $this->packTool == null) {
            $this->packTool = NonJsonPack::class;
        } else if (!$this->isOpenHttpProtocol() && $this->packTool == null) {
            $this->packTool = LenJsonPack::class;
        }
        parent::merge();
    }

    /**
     * @return string
     */
    public function getPackTool(): ?string
    {
        return $this->packTool;
    }

    /**
     * @param string $packTool
     */
    public function setPackTool(string $packTool): void
    {
        $this->packTool = $packTool;
    }
}