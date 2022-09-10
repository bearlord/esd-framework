<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\CircuitBreaker;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Plugins\Redis\RedisPlugin;
use ESD\Psr\Cloud\CircuitBreaker;

class CircuitBreakerPlugin extends AbstractPlugin
{

    /**
     * @var CircuitBreakerConfig|null
     */
    private $circuitBreakerConfig;

    /**
     * CircuitBreakerPlugin constructor.
     * @param CircuitBreakerConfig|null $circuitBreakerConfig
     * @throws \ReflectionException
     */
    public function __construct(?CircuitBreakerConfig $circuitBreakerConfig = null)
    {
        parent::__construct();
        if ($circuitBreakerConfig == null) {
            $circuitBreakerConfig = new CircuitBreakerConfig();
        }
        $this->circuitBreakerConfig = $circuitBreakerConfig;
        $this->atAfter(RedisPlugin::class);
    }

    /**
     * @inheritDoc
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
        $this->pluginInterfaceManager->addPlug(new RedisPlugin());
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "CircuitBreakerPlugin";
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $ganesha = GaneshaCircuitBreaker::build([
            'timeWindow' => $this->circuitBreakerConfig->getTimeWindow(),
            'failureRateThreshold' => $this->circuitBreakerConfig->getFailureRateThreshold(),
            'minimumRequests' => $this->circuitBreakerConfig->getMinimumRequests(),
            'intervalToHalfOpen' => $this->circuitBreakerConfig->getIntervalToHalfOpen(),
            'db' => $this->circuitBreakerConfig->getRedisDb(),
            'adapter' => new RedisAdapter(),
        ]);
        $ganesha->setEnable($this->circuitBreakerConfig->isEnable());
        $this->setToDIContainer(CircuitBreaker::class, $ganesha);
        return;
    }

    /**
     * @inheritDoc
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}