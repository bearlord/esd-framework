<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;


use ESD\Core\Order\OrderOwnerTrait;
use ESD\Plugins\Pack\ClientData;

class EveryFilterManager
{
    use OrderOwnerTrait;

    public function filter(ClientData $clientData): int
    {
        /** @var AbstractFilter $order */
        foreach ($this->orderList as $order) {
            if ($order->isEnable($clientData)) {
                $code = $order->filter($clientData);
                if ($code < AbstractFilter::RETURN_NEXT) return $code;
            }
        }
        return AbstractFilter::RETURN_END_FILTER;
    }
}