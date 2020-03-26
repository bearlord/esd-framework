<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute;

use ESD\Core\Server\Config\PortConfig;
use ESD\Plugins\EasyRoute\RouteTool\AnnotationRoute;
use ESD\Plugins\Pack\PackTool\LenJsonPack;

/**
 * Class EasyRouteConfig
 * @package ESD\Plugins\EasyRoute
 */
class EasyRouteConfig extends PortConfig
{
    /**
     * @var string
     */
    protected $packTool = LenJsonPack::class;

    /**
     * @var string
     */
    protected $routeTool = AnnotationRoute::class;


    /**
     * @return string
     */
    public function getPackTool(): string
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

    /**
     * @return string
     */
    public function getRouteTool(): string
    {
        return $this->routeTool;
    }

    /**
     * @param string $routeTool
     */
    public function setRouteTool(string $routeTool): void
    {
        $this->routeTool = $routeTool;
    }
}