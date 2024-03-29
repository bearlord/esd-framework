<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\Gateway\Controller;

use ESD\Psr\Tracing\TracingInterface;

/**
 * Interface IController
 * @package ESD\Plugins\Cloud\Gateway\Controller
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
     * @param \Throwable $exception
     * @return mixed
     */
    public function onExceptionHandle(\Throwable $exception);
}