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

use ESD\Goaop\Aop\AdviceAfter;
use ESD\Goaop\Aop\Intercept\Joinpoint;

/**
 * "After" interceptor
 *
 * @api
 */
final class AfterInterceptor extends BaseInterceptor implements AdviceAfter
{
    /**
     * After invoker
     *
     * @param Joinpoint $joinpoint the concrete joinpoint
     *
     * @return mixed the result of the call to {@link Joinpoint::proceed()}
     */
    public function invoke(Joinpoint $joinpoint)
    {
        try {
            return $joinpoint->proceed();
        } finally {
            $adviceMethod = $this->adviceMethod;
            $adviceMethod($joinpoint);
        }
    }
}
