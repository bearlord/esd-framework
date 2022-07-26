<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp;

class QueueBuilder extends Builder
{
    /**
     * @var string
     */
    protected $queue;

    /**
     * @var bool
     */
    protected $exclusive = false;

    /**
     * @var array
     */
    protected $arguments = [
        'x-ha-policy' => ['S', 'all'],
    ];

    /**
     * @inheritDoc
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * @inheritDoc
     * @param string $queue
     * @return $this
     */
    public function setQueue(string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @inheritDoc
     * @return bool
     */
    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    /**
     * @inheritDoc
     * @param bool $exclusive
     * @return $this
     */
    public function setExclusive(bool $exclusive): self
    {
        $this->exclusive = $exclusive;
        return $this;
    }
}
