<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/8
 * Time: 17:36
 */

namespace ESD\Plugins\Security\Aspect;

use ESD\Plugins\Aop\OrderAspect;
use ESD\Plugins\Security\AccessDeniedException;
use ESD\Plugins\Security\Annotation\PostAuthorize;
use ESD\Plugins\Security\Annotation\PreAuthorize;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;

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