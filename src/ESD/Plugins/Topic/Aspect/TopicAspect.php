<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/21
 * Time: 15:46
 */

namespace ESD\Plugins\Topic\Aspect;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Topic\GetTopic;
use ESD\Plugins\Uid\Aspect\UidAspect;
use ESD\Plugins\Uid\GetUid;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Before;

class TopicAspect extends OrderAspect
{
    use GetLogger;
    use GetTopic;
    use GetUid;

    public function __construct()
    {
        //要在UidAspect之前执行，不然uid就被清除了
        $this->atBefore(UidAspect::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "TopicAspect";
    }

    /**
     * around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Before("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onTcpClose(*))")
     */
    protected function afterTcpClose(MethodInvocation $invocation)
    {
        list($fd, $reactorId) = $invocation->getArguments();
        //这里是跨进程调用，所以用uid，不用fd，避免时序错误
        $uid = $this->getFdUid($fd);
        if ($uid != null) {
            $this->clearUidSub($uid);
        }
    }

    /**
     * around onTcpReceive
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \Throwable
     * @Before("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onWsClose(*))")
     */
    protected function afterWsClose(MethodInvocation $invocation)
    {
        list($fd, $reactorId) = $invocation->getArguments();
        $this->clearFdSub($fd);
    }
}