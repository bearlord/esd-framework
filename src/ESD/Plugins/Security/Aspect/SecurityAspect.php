<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Security\Aspect;

use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Security\AccessDeniedException;
use ESD\Plugins\Security\Annotation\PostAuthorize;
use ESD\Plugins\Security\Annotation\PreAuthorize;
use ESD\Goaop\Aop\Intercept\MethodInvocation;
use ESD\Goaop\Lang\Annotation\Around;

/**
 * Class SecurityAspect
 * @package ESD\Plugins\Security\Aspect
 */
class SecurityAspect extends OrderAspect
{
    /**
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(ESD\Plugins\Security\Annotation\PostAuthorize)")
     * @return mixed
     * @throws AccessDeniedException
     */
    public function aroundPostAuthorize(MethodInvocation $invocation)
    {
        $postAuthorize = $invocation->getMethod()->getAnnotation(PostAuthorize::class);
        $p = $invocation->getArguments();
        $returnObject = $invocation->proceed();
        $ex = eval("return ($postAuthorize->value);");
        if ($ex) {
            return $returnObject;
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@execution(ESD\Plugins\Security\Annotation\PreAuthorize)")
     * @return mixed
     * @throws AccessDeniedException
     */
    public function aroundPreAuthorize(MethodInvocation $invocation)
    {
        $preAuthorize = $invocation->getMethod()->getAnnotation(PreAuthorize::class);
        $p = $invocation->getArguments();
        $ex = eval("return ($preAuthorize->value);");
        if ($ex) {
            return $invocation->proceed();
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "SecurityAspect";
    }
}