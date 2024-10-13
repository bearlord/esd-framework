<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Order;

/**
 * Interface OrderInterface
 * @package ESD\Core\Order
 */
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
     * @param mixed $afterOrder
     */
    public function addAfterOrder(Order $afterOrder);

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
