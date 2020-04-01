<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Controller;

use ESD\Psr\Tracing\TracingInterface;

/**
 * Interface IController
 * @package ESD\Plugins\EasyRoute\Controller
 */
interface IController extends TracingInterface
{
    /**
     * @param string|null $controllerName
     * @param string|null $methodName
     * @param array|null $params
     * @return mixed
     */
    public function handle(?string $controllerName, ?string $methodName, ?array $params);

    /**
     * @param string|null $controllerName
     * @param string|null $methodName
     * @return mixed
     */
    public function initialization(?string $controllerName, ?string $methodName);

    /**
     * @param \Throwable $e
     * @return mixed
     */
    public function onExceptionHandle(\Throwable $e);
}