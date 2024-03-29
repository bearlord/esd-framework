<?php
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
use ESD\Goaop\Aop\PointFilter;

/**
 * Convenient Pointcut-driven Advisor implementation.
 *
 * This is the most commonly used Advisor implementation. It can be used with any pointcut and advice type,
 * except for introductions. There is normally no need to subclass this class, or to implement custom Advisors.
 */
class DefaultPointcutAdvisor extends AbstractGenericPointcutAdvisor
{

    /**
     * Pointcut instance
     *
     * @var Pointcut
     */
    private $pointcut;

    /**
     * Create a DefaultPointcutAdvisor, specifying Pointcut and Advice.
     *
     * @param Pointcut $pointcut The Pointcut targeting the Advice
     * @param Advice $advice The Advice to run when Pointcut matches
     */
    public function __construct(Pointcut $pointcut, Advice $advice)
    {
        $this->pointcut = $pointcut;
        parent::__construct($advice);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdvice()
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
     *
     * @return Pointcut The pointcut
     */
    public function getPointcut()
    {
        return $this->pointcut;
    }

    /**
     * Specify the pointcut targeting the advice.
     *
     * @param Pointcut $pointcut The Pointcut targeting the Advice
     */
    public function setPointcut(Pointcut $pointcut)
    {
        $this->pointcut = $pointcut;
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function __toString()
    {
        $pointcutClass = get_class($this->getPointcut());
        $adviceClass   = get_class($this->getAdvice());

        return static::class . ": pointcut [{$pointcutClass}]; advice [{$adviceClass}]";
    }
}
