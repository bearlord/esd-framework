<?php

namespace ESD\Plugins\Actor;

use ESD\Plugins\Actor\ActorCacheProcess;

class ActorCacheHash implements \ArrayAccess
{
    /**
     * @var ActorCacheProcess process
     */
    protected $process;

    /**
     * @var string delimiter
     */
    protected $delimiter = ".";

    /**
     * @var array container
     */
    protected $container = [];

    /**
     * @param ActorCacheProcess $process
     * @param string|null $delimiter
     */
    public function __construct(ActorCacheProcess $process, ?string $delimiter = ".")
    {
        $this->process = $process;
        $this->delimiter = $delimiter;
    }

    /**
     * @return array get container
     */
    public function &getContainer()
    {
        return $this->container;
    }

    /**
     * @inheritDoc
     * @param $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        $path = explode($this->delimiter, $offset);
        $deep = &$this->container;

        $count = count($path);
        for ($i = 0; $i < $count; $i++) {
            $point = $path[$i];
            if (array_key_exists($point, $deep)) {
                $deep = &$deep[$point];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     * @param $offset
     * @return array|mixed|null
     */
    public function offsetGet(mixed $offset): bool
    {
        $path = explode($this->delimiter, $offset);
        $deep = &$this->container;

        $count = count($path);
        for ($i = 0; $i < $count; $i++) {
            $point = $path[$i];
            if (array_key_exists($point, $deep)) {
                $deep = &$deep[$point];
            } else {
                return null;
            }
        }

        return $deep;
    }

    /**
     * @inheritDoc
     * @param $offset
     * @param $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $path = explode($this->delimiter, $offset);
            $deep = &$this->container;

            $count = count($path) - 1;
            for ($i = 0; $i < $count; $i++) {
                $point = $path[$i];
                if (array_key_exists($point, $deep)) {
                    $deep = &$deep[$point];
                } else {
                    $deep[$point] = [];
                    $deep = &$deep[$point];
                }
            }
            $deep[$path[$count]] = $value;
        }
    }

    /**
     * @inheritDoc
     * @param $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $path = explode($this->delimiter, $offset);
        $deep = &$this->container;

        $count = count($path) - 1;

        for ($i = 0; $i < $count; $i++) {
            $point = $path[$i];
            if (array_key_exists($point, $deep)) {
                $deep = &$deep[$point];
            } else {
                return;
            }
        }
        unset($deep[$path[$count]]);
    }
}
