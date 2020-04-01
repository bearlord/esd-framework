<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\EasyRoute\Filter;

use ESD\Plugins\Pack\ClientData;

/**
 * Class FilterManager
 * @package ESD\Plugins\EasyRoute\Filter
 */
class FilterManager
{
    /**
     * @var EveryFilterManager[]
     */
    protected $managers = [];

    /**
     * FilterManager constructor.
     */
    public function __construct()
    {
        $this->managers[AbstractFilter::FILTER_PRE] = new EveryFilterManager();
        $this->managers[AbstractFilter::FILTER_PRO] = new EveryFilterManager();
        $this->managers[AbstractFilter::FILTER_ROUTE] = new EveryFilterManager();
    }

    /**
     * @param AbstractFilter $filter
     */
    public function addFilter(AbstractFilter $filter)
    {
        $this->managers[$filter->getType()]->addOrder($filter);
        $this->managers[$filter->getType()]->order();
    }

    /**
     * @param $type
     * @param ClientData $clientData
     * @param null $data
     * @return int
     */
    public function filter($type, ClientData $clientData, $data = null): int
    {
        return $this->managers[$type]->filter($clientData, $data);
    }
}