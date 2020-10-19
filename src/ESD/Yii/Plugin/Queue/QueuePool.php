<?php


namespace ESD\Yii\Plugin\Queue;


use ESD\Core\Channel\Channel;
use ESD\Yii\Queue\Cli\Queue;
use ESD\Yii\Yii;

class QueuePool
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Channel
     */
    protected $pool;

    /** @var array  */
    protected $config;

    /** @var int  */
    protected $poolMaxNumber = 5;

    /**
     * QueuePool constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct(string $name, array $config)
    {
        $this->setName($name);
        $this->setConfig($config);

        $this->pool = DIGet(Channel::class, [$this->getPoolMaxNumber()]);
        for ($i = 0; $i < $this->getPoolMaxNumber(); $i++) {
            $queue = $this->buildQueue($config);
            $this->pool->push($queue);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
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
        $contextKey = sprintf("Queue:%s", $this->name);
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