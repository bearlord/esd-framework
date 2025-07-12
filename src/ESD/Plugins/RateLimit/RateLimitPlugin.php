<?php

namespace ESD\Plugins\RateLimit;

use ESD\Core\Context\Context;
use ESD\Core\Plugin\AbstractPlugin;
use ESD\Plugins\Aop\AopConfig;
use ESD\Plugins\Pack\PackPlugin;
use ESD\Plugins\RateLimit\Aspect\RateLimitAspect;
use ESD\Plugins\Redis\RedisPlugin;
use ESD\Plugins\Route\RoutePlugin;

class RateLimitPlugin  extends AbstractPlugin
{

    public function __construct()
    {
        parent::__construct();

        $this->atAfter(RedisPlugin::class);
        $this->atAfter(PackPlugin::class);
        $this->atAfter(RoutePlugin::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "RateLimit";
    }

    /**
     * @param Context $context
     * @return void
     * @throws \Exception
     */
    public function init(Context $context)
    {
        $aopConfig = DIget(AopConfig::class);

        $routeLimitAspect = new RateLimitAspect();

        $aopConfig->addAspect($routeLimitAspect);
    }



    public function beforeServerStart(Context $context)
    {
        // TODO: Implement beforeServerStart() method.
    }

    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}