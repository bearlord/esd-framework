<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Console;

use ESD\Core\Context\Context;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Plugins\Console\Command\EntityCmd;
use ESD\Plugins\Console\Command\ModelCmd;
use ESD\Plugins\Console\Command\ReloadCmd;
use ESD\Plugins\Console\Command\RestartCmd;
use ESD\Plugins\Console\Command\StartCmd;
use ESD\Plugins\Console\Command\StopCmd;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class ConsolePlugin
 * @package ESD\Plugins\Console
 */
class ConsolePlugin extends AbstractPlugin
{
    const SUCCESS_EXIT = 0;
    const FAIL_EXIT = 1;
    const NOEXIT = -255;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var ConsoleConfig
     */
    private $config;

    /**
     * @inheritDoc
     * @return string
     */
    public function getName(): string
    {
        return "Console";
    }

    /**
     * ConsolePlugin constructor.
     * @param ConsoleConfig|null $config
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \ReflectionException
     */
    public function __construct(?ConsoleConfig $config = null)
    {
        parent::__construct();
        if ($config == null) {
            $config = new ConsoleConfig();
        }
        $this->config = $config;
        $this->application = new Application("ESD-YII");
        $this->application->setAutoExit(false);
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed|void
     * @throws \ESD\Core\Plugins\Config\ConfigException
     * @throws \ReflectionException
     */
    public function init(Context $context)
    {
        parent::init($context);
        enableRuntimeCoroutine(false);
        $input = new ArgvInput();
        $output = new ConsoleOutput();
        $this->config->addCmdClass(ReloadCmd::class);
        $this->config->addCmdClass(RestartCmd::class);
        $this->config->addCmdClass(StartCmd::class);
        $this->config->addCmdClass(StopCmd::class);
//        $this->config->addCmdClass(EntityCmd::class);
        $this->config->addCmdClass(ModelCmd::class);
        $this->config->merge();
        $cmds = [];
        foreach ($this->config->getCmdClassList() as $value) {
            $cmd = new $value($context);
            if ($cmd instanceof Command) {
                $cmds[$cmd->getName()] = $cmd;
            }
        }
        $this->application->addCommands($cmds);
        $exitCode = $this->application->run($input, $output);
        if ($exitCode >= 0) {
            \swoole_event_exit();
            exit();
        }
        enableRuntimeCoroutine();
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        return;
    }

    /**
     * @inheritDoc
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }
}