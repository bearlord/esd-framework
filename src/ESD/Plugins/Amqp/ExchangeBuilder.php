<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Plugins\Amqp;

class ExchangeBuilder extends Builder
{
    /**
     * @var string
     */
    protected $exchange;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $internal = false;

    /**
     * @inheritDoc
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @inheritDoc
     * @param string $exchange
     * @return $this
     */
    public function setExchange(string $exchange): self
    {
        $this->exchange = $exchange;
        return $this;
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @inheritDoc
     * @return bool
     */
    public function isInternal(): bool
    {
        return $this->internal;
    }

    /**
     * @inheritDoc
     * @param bool $internal
     * @return $this
     */
    public function setInternal(bool $internal): self
    {
        $this->internal = $internal;
        return $this;
    }
}
