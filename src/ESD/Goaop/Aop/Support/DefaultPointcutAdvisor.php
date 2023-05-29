<?php

declare(strict_types = 1);
/*
 * Go! AOP framework
 *
 * @copyright Copyright 2012, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ESD\Goaop\Aop\Support;

use ESD\Goaop\Aop\Advice;
use ESD\Goaop\Aop\Framework\DynamicInvocationMatcherInterceptor;
use ESD\Goaop\Aop\Intercept\Interceptor;
use ESD\Goaop\Aop\Pointcut;
use ESD\Goaop\Aop\PointcutAdvisor;
use ESD\Goaop\Aop\PointFilter;

/**
 * Convenient Pointcut-driven Advisor implementation.
 *
 * This is the most commonly used Advisor implementation. It can be used with any pointcut and advice type,
 * except for introductions. There is normally no need to subclass this class, or to implement custom Advisors.
 */
class DefaultPointcutAdvisor extends AbstractGenericAdvisor implements PointcutAdvisor
{
    /**
     * The Pointcut targeting the Advice
     */
    private Pointcut $pointcut;

    /**
     * Creates a DefaultPointcutAdvisor, specifying the Advice to run when Pointcut matches
     */
    public function __construct(Pointcut $pointcut, Advice $advice)
    {
        $this->pointcut = $pointcut;
        parent::__construct($advice);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdvice(): Advice
    {
        $advice = parent::getAdvice();
        if (($advice instanceof Interceptor) && ($this->pointcut->getKind() & PointFilter::KIND_DYNAMIC)) {
            $advice = new DynamicInvocationMatcherInterceptor(
                $this->pointcut,
                $advice
            );
        }

        return $advice;
    }

    /**
     * Get the Pointcut that drives this advisor.
     */
    public function getPointcut(): Pointcut
    {
        return $this->pointcut;
    }
}
