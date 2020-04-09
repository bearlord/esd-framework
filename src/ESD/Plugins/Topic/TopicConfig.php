<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Topic;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class TopicConfig
 * @package ESD\Plugins\Topic
 */
class TopicConfig extends BaseConfig
{
    const key = "topic";
    /**
     * @var int
     */
    protected $cacheTopicCount = 10000;
    /**
     * @var int
     */
    protected $topicMaxLength = 256;
    /**
     * In the helper process by default, other names can be set, and a new process will be created
     * @var string
     */
    protected $processName = "helper";

    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return int
     */
    public function getCacheTopicCount(): int
    {
        return $this->cacheTopicCount;
    }

    /**
     * @param int $cacheTopicCount
     */
    public function setCacheTopicCount(int $cacheTopicCount): void
    {
        $this->cacheTopicCount = $cacheTopicCount;
    }

    /**
     * @return string
     */
    public function getProcessName(): string
    {
        return $this->processName;
    }

    /**
     * @param string $processName
     */
    public function setProcessName(string $processName): void
    {
        $this->processName = $processName;
    }

    /**
     * @return int
     */
    public function getTopicMaxLength(): int
    {
        return $this->topicMaxLength;
    }

    /**
     * @param int $topicMaxLength
     */
    public function setTopicMaxLength(int $topicMaxLength): void
    {
        $this->topicMaxLength = $topicMaxLength;
    }
}