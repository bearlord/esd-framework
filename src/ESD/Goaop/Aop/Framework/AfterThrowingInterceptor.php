<?php
/*
 * Go! AOP framework
 *
 * @copyright Copyright 2011, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ESD\Goaop\Aop\Framework;

use Exception;
use ESD\Goaop\Aop\AdviceAfter;
use ESD\Goaop\Aop\Intercept\Joinpoint;

/**
 * "After Throwing" interceptor
 *
 * @api
 */
final class AfterThrowingInterceptor extends BaseInterceptor implements AdviceAfter
{
    /**
     * After throwing exception invoker
     *
     * @param Joinpoint $joinpoint the concrete joinpoint
     *
     * @return mixed the result of the call to {@link Joinpoint::proceed()}
     * @throws Exception
     */
    public function invoke(Joinpoint $joinpoint)
    {
        try {
            return $joinpoint->proceed();
        } catch (Exception $invocationException) {
            $adviceMethod = $this->adviceMethod;
            $adviceMethod($joinpoint, $invocationException);

            throw $invocationException;
        }
    }
}
