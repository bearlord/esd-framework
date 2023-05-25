<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Aop;

use ESD\Core\Order\Order;
use ESD\Goaop\Go\Aop\Aspect;

/**
 * Class OrderAspect
 * @package ESD\Plugins\Aop
 */
abstract class OrderAspect extends Order implements Aspect
{
}