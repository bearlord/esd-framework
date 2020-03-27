<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/21
 * Time: 15:46
 */

namespace ESD\Plugins\Uid\Aspect;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Uid\UidBean;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;

class UidAspect extends OrderAspect
{
    use GetLogger;

    /**
     * @var UidBean
     */
    protected $uid;


    /**
     * around onTcpReceive
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
     * around onTcpReceive
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