<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Order;

trait OrderOwnerTrait
{
    /**
     * @var bool
     */
    protected $fixed = false;

    /**
     * @var Order[]
     */
    protected $orderList = [];

    /**
     * @var Order[]
     */
    protected $orderClassList = [];

    /**
     * Add order
     * @param Order $order
     */
    public function addOrder(Order $order)
    {
        $this->fixed = false;
        $this->orderList[$order->getName()] = $order;
        $this->orderClassList[get_class($order)] = $order;
    }

    /**
     * Sort plugins
     */
    public function order()
    {
        if ($this->fixed) return;
        foreach ($this->orderList as $order) {
            foreach ($this->getOrderBeforeClass($order) as $needAddAfterOrder) {
                $needAddAfterOrder->addAfterOrder($order);
            }
            foreach ($this->getOrderAfterClass($order) as $afterOrder) {
                $order->addAfterOrder($afterOrder);
            }
        }
        usort($this->orderList, function ($a, $b) {
            if ($a->getOrderIndex($a, 0) > $b->getOrderIndex($b, 0)) {
                return 1;
            } else {
                return -1;
            }
        });
        $this->fixed = true;
    }

    /**
     * Get order before class
     *
     * @param Order $order
     * @return Order[]
     */
    protected function getOrderBeforeClass(Order $order): array
    {
        $result = [];
        foreach ($order->getBeforeClass() as $class) {
            $one = $this->orderClassList[$class] ?? null;
            if ($one != null) {
                $result[] = $one;
            }
        }
        return $result;
    }

    /**
     * Get order after class
     *
     * @param Order $order
     * @return Order[]
     */
    protected function getOrderAfterClass(Order $order): array
    {
        $result = [];
        foreach ($order->getAfterClass() as $class) {
            $one = $this->orderClassList[$class] ?? null;
            if ($one != null) {
                $result[] = $one;
            }
        }
        return $result;
    }
}