<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Cloud\CircuitBreaker;

use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Configuration;
use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Psr\Cloud\CircuitBreaker;

class GaneshaCircuitBreaker extends Ganesha implements CircuitBreaker
{
    use GetLogger;

    private static $enable = true;

    /**
     * @inheritDoc
     * @param  array $params
     * @return GaneshaCircuitBreaker
     * @throws \Exception
     */
    public static function build(array $params)
    {
        $params['strategyClass'] = '\Ackintosh\Ganesha\Strategy\Rate';
        return self::perform($params);
    }

    /**
     * @inheritDoc
     * @param  array $params
     * @return GaneshaCircuitBreaker
     * @throws \Exception
     */
    public static function buildWithCountStrategy(array $params)
    {
        $params['strategyClass'] = '\Ackintosh\Ganesha\Strategy\Count';
        return self::perform($params);
    }

    /**
     * @inheritDoc
     * @return GaneshaCircuitBreaker
     * @throws \Exception
     */
    private static function perform($params)
    {
        call_user_func([$params['strategyClass'], 'validate'], $params);

        $configuration = new Configuration($params);
        $ganesha = new GaneshaCircuitBreaker(
            call_user_func(
                [$configuration['strategyClass'], 'create'],
                $configuration
            )
        );

        return $ganesha;
    }

    /**
     * @inheritDoc
     * @param $enable
     * @return mixed|void
     */
    public function setEnable($enable)
    {
        self::$enable = $enable;
        if ($enable) {
            self::enable();
        } else {
            self::disable();
        }
    }

    /**
     * @inheritDoc
     * @return bool|mixed
     */
    public function isEnable()
    {
        return self::$enable;
    }

    /**
     * @inheritDoc
     * @param $service
     * @return bool|mixed
     * @throws \Exception
     */
    public function isAvailable($service)
    {
        $result = parent::isAvailable($service);
        if ($result) {
            $this->debug("Service $service availability");
        } else {
            $this->debug("Service $service unavailability");
        }
        return $result;
    }

    /**
     * @inheritDoc
     * @param $service
     * @return mixed|void
     * @throws \Exception
     */
    public function failure($service)
    {
        parent::failure($service);
        $this->debug("Service $service tag failure");
    }

    /**
     * @inheritDoc
     * @param $service
     * @return mixed|void
     * @throws \Exception
     */
    public function success($service)
    {
        parent::success($service);
        $this->debug("Service $service tag success");
    }
}