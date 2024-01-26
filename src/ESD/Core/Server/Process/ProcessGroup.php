<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Server\Process;

use ESD\Core\Message\Message;

/**
 * Class ProcessGroup
 * @package ESD\Core\Server\Process
 */
class ProcessGroup
{
    /**
     * @var Process[]
     */
    private $processes = [];

    private $groupName;

    private $index;

    /**
     * @var ProcessManager
     */
    private $processManager;

    /**
     * ProcessGroup constructor.
     * @param ProcessManager $processManager
     * @param string $groupName
     * @param array $processes
     */
    public function __construct(ProcessManager $processManager, string $groupName, array $processes)
    {
        $this->processManager = $processManager;
        $this->processes = $processes;
        $this->groupName = $groupName;
        $this->index = 0;
    }

    /**
     * @return Process[]
     */
    public function getProcesses(): array
    {
        return $this->processes;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * Send message to group.
     * @param \ESD\Core\Message\Message $message
     */
    public function sendMessageToGroup(Message $message)
    {
        if ($this->index == count($this->processes)) {
            $this->index = 0;
        }
        $process = $this->processes[$this->index];
        $this->processManager->getCurrentProcess()->sendMessage($message, $process);
        $this->index++;
    }
}
