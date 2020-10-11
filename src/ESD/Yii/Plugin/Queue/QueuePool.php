<?php


namespace ESD\Yii\Plugin\Queue;


use ESD\Core\Channel\Channel;
use ESD\Yii\Queue\Cli\Queue;
use ESD\Yii\Yii;

class QueuePool
{
    /**
     * @var Channel
     */
    protected $pool;

    /** @var Config  */
    protected $config;

    /** @var int  */
    protected $poolMaxNumber = 5;

    /**
     * QueuePool constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->pool = DIGet(Channel::class, [$this->getPoolMaxNumber()]);
        for ($i = 0; $i < $this->getPoolMaxNumber(); $i++) {
            $queue = $this->buildQueue($config);
            $this->pool->push($queue);
        }
    }

    /**
     * @return int|mixed
     */
    protected function getPoolMaxNumber()
    {
        return $this->config['poolMaxNumber'] ?? $this->poolMaxNumber;
    }

    /**
     * @param $config
     * @return object
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function buildQueue($config)
    {
        if (!empty($config['minIntervalTime'])) {
            unset($config['minIntervalTime']);
        }
        if (!empty($config['poolMaxNumber'])) {
            unset($config['poolMaxNumber']);
        }

        return Yii::createObject($config);
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $contextKey = "Queue:default";
        $handle = getContextValue($contextKey);

        if ($handle == null) {
            /** @var \ESD\Yii\Queue\Cli\Queue $handle */
            $handle = $this->pool->pop();

            \Swoole\Coroutine::defer(function () use ($handle) {
                $this->pool->push($handle);
            });
            setContextValue($contextKey, $handle);
        }
        return $handle;
    }

}