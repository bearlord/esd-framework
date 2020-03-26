<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Event;

/**
 * Interface EventCall
 * @package ESD\Core\Plugins\Event
 */
interface EventCall
{
    /**
     * EventCall constructor.
     * @param EventDispatcher $eventDispatcher
     * @param string $type
     * @param bool $once
     */
    public function __construct(EventDispatcher $eventDispatcher, string $type, bool $once = false);

    /**
     * @param $data
     * @return mixed
     */
    public function send($data);

    /**
     * @param callable $fuc
     * @return mixed
     */
    public function call(callable $fuc);

    /**
     * @param int $timeout
     * @return mixed
     */
    public function wait($timeout = 5);
    /**
     * @return mixed
     */
    public function destroy();

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

    /**
     * @return bool
     */
    public function isOnce(): bool;

    /**
     * @param bool $once
     */
    public function setOnce(bool $once): void;

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher;
}