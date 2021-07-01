<?php
/**
 * ESD framework
 * @author bearload <565364226@qq.com>
 */

namespace ESD\LoadBalance;

/**
 * Class Node
 * @package ESD\LoadBalance
 */
class Node
{
    /**
     * @var int
     */
    public $weight;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * Node constructor.
     * @param string $host
     * @param int $port
     * @param int $weight
     */
    public function __construct(string $host = '127.0.0.1', int $port, int $weight = 0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        $schema = 'http';
        if (property_exists($this, 'schema')) {
            $schema = $this->schema;
        }
        if (!in_array($schema, ['http', 'https'])) {
            $schema = 'http';
        }
        $schema .= '://';
        return $schema;
    }
}
