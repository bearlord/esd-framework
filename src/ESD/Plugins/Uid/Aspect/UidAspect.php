<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Uid\Aspect;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Uid\UidBean;
use ESD\Goaop\Aop\Intercept\MethodInvocation;
use ESD\Goaop\Lang\Annotation\After;

/**
 * Class UidAspect
 * @package ESD\Plugins\Uid\Aspect
 */
class UidAspect extends OrderAspect
{
    use GetLogger;

    /**
     * @var UidBean
     */
    protected $uid;

    /**
     * Around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @After("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onTcpClose(*))")
     */
    protected function afterTcpClose(MethodInvocation $invocation)
    {
        list($fd, $reactorId) = $invocation->getArguments();
        Server::$instance->getContainer()->get(UidBean::class)->unBindUid($fd);
    }

    /**
     * Around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @After("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onWsClose(*))")
     */
    protected function afterWsClose(MethodInvocation $invocation)
    {
        list($fd, $reactorId) = $invocation->getArguments();
        Server::$instance->getContainer()->get(UidBean::class)->unBindUid($fd);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "UidAspect";
    }
}