<?php
/**
 * Copied from hyperf, and modifications are not listed anymore.
 * @contact  group@hyperf.io
 * @licence  MIT License
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ESD\Parallel;

/**
 * Class Exception
 * @package ESD\Parallel
 */
class Exception extends \RuntimeException
{
    /**
     * @var array
     */
    private $results;

    /**
     * @var array
     */
    private $throwables;

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param array $results
     */
    public function setResults(array $results)
    {
        $this->results = $results;
    }

    /**
     * @return array
     */
    public function getThrowables()
    {
        return $this->throwables;
    }

    /**
     * @param array $throwables
     * @return array
     */
    public function setThrowables(array $throwables)
    {
        return $this->throwables = $throwables;
    }
}