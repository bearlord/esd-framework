<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Plugins\Event;

use ESD\Core\Order\OrderOwnerTrait;
use ESD\Core\Server\Process\Process;

class EventDispatcher
{
    use OrderOwnerTrait;

    /**
     * @var array
     */
    private $eventCalls = [];

    /**
     * @param AbstractEventDispatcher $eventDispatcher
     */
    public function addEventDispatcher(AbstractEventDispatcher $eventDispatcher)
    {
        $this->addOrder($eventDispatcher);
    }

    /**
     * Get event dispatcher
     *
     * @param string $name
     * @return AbstractEventDispatcher|null
     */
    public function getEventDispatcher(string $name): ?AbstractEventDispatcher
    {
        return $this->orderClassList[$name] ?? null;
    }

    /**
     * Registers an event listener at a certain object.
     *
     * @param string $type
     * @param EventCall|null $eventCall
     * @param bool $once
     * @return EventCall
     * @throws \Exception
     */
    public function listen($type, ?EventCall $eventCall = null, $once = false): EventCall
    {
        if (!array_key_exists($type, $this->eventCalls)) {
            $this->eventCalls [$type] = [];
        }
        if ($eventCall == null) {
            $eventCall = DIGet(EventCall::class, [$this, $type, $once]);
        }
        array_push($this->eventCalls[$type], $eventCall);
        return $eventCall;
    }

    /**
     * Removes an event listener from the object.
     *
     * @param string $type
     * @param EventCall $eventCall
     */
    public function remove($type, EventCall $eventCall)
    {
        if ($eventCall != null) $eventCall->destroy();
        if (array_key_exists($type, $this->eventCalls)) {
            $index = array_search($eventCall, $this->eventCalls [$type]);
            if ($index !== null) {
                unset ($this->eventCalls [$type] [$index]);
            }
            $numListeners = count($this->eventCalls [$type]);
            if ($numListeners == 0) {
                unset ($this->eventCalls [$type]);
            }
        }
    }

    /**
     * Removes all event listeners with a certain type, or all of them if type is null.
     * Be careful when removing all event listeners: you never know who else was listening.
     *
     * @param string $type
     */
    public function removeAll($type = null)
    {
        if ($type) {
            unset ($this->eventCalls [$type]);
        } else {
            $this->eventCalls = array();
        }
    }

    /**
     * Dispatches an event to all objects that have registered listeners for its type.
     * If an event with enabled 'bubble' property is dispatched to a display object, it will
     * travel up along the line of parents, until it either hits the root object or someone
     * stops its propagation manually.
     *
     * @param Event $event
     */
    public function dispatchEvent(Event $event)
    {
        $this->order();
        //Add EventFormInfo, add the information of the message sender
        if (empty($event->getProgress())) {
            foreach ($this->orderList as $order) {
                if ($order instanceof AbstractEventDispatcher) {
                    $order->handleEventFrom($event);
                }
            }
        }

        //Send message
        $start = false;
        if (empty($event->getProgress())) $start = true;
        foreach ($this->orderList as $order) {
            if ($order instanceof AbstractEventDispatcher) {
                if ($start == false && $event->getProgress() == $order->getName()) {
                    $start = true;
                    continue;
                }
                if ($start) {
                    $event->setProgress($order->getName());
                    $result = $order->dispatchEvent($event);
                    if (!$result) break;
                }
            }
        }
    }

    /**
     * send event to process
     *
     * @param Event $event
     * @param Process ...$toProcess
     */
    public function dispatchProcessEvent(Event $event, Process ... $toProcess)
    {
        $pids = [];
        foreach ($toProcess as $process) {
            $pids[] = $process->getProcessId();
        }
        $event->setDstInfo(ProcessEventDispatcher::type, $pids);
        $this->dispatchEvent($event);
    }

    /**
     * send event to process
     *
     * @param Event $event
     * @param array $toProcessIds
     */
    public function dispatchProcessIdEvent(Event $event, $toProcessIds)
    {
        $event->setDstInfo(ProcessEventDispatcher::type, $toProcessIds);
        $this->dispatchEvent($event);
    }

    /**
     * Get event calls
     *
     * @param $type
     * @return EventCall[]||null
     */
    public function getEventCalls($type): ?array
    {
        return $this->eventCalls[$type] ?? null;
    }

}
