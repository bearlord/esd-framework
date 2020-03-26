<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Order;


interface OrderInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param Order $root
     * @param int $layer
     * @return int
     */
    public function getOrderIndex(Order $root, int $layer): int;

    /**
     * @param mixed $afterPlug
     */
    public function addAfterOrder(Order $afterPlug);

    /**
     * @param $className
     * @return void
     */
    public function atAfter(...$className);

    /**
     * @param $className
     * @return void
     */
    public function atBefore(...$className);

    /**
     * @return array
     */
    public function getAfterClass(): array;

    /**
     * @return array
     */
    public function getBeforeClass(): array;

}