<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Controller;

use ESD\Psr\Tracing\TracingInterface;

interface IController extends TracingInterface
{
    public function handle(?string $controllerName, ?string $methodName, ?array $params);

    public function initialization(?string $controllerName, ?string $methodName);

    public function onExceptionHandle(\Throwable $e);
}