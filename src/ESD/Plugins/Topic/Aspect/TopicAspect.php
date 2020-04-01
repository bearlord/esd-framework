<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Topic\Aspect;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Topic\GetTopic;
use ESD\Plugins\Uid\Aspect\UidAspect;
use ESD\Plugins\Uid\GetUid;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Before;

/**
 * Class TopicAspect
 * @package ESD\Plugins\Topic\Aspect
 */
class TopicAspect extends OrderAspect
{
    use GetLogger;
    use GetTopic;
    use GetUid;

    /**
     * TopicAspect constructor.
     */
    public function __construct()
    {
        //To be executed before UidAspect, otherwise the uid will be cleared
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

        //This is a cross-process call, so use uid instead of fd to avoid timing errors
        $uid = $this->getFdUid($fd);
        if ($uid != null) {
            $this->clearUidSub($uid);
        }
    }

    /**
     * Around onTcpReceive
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