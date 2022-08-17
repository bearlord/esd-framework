<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\PHPUnit;

use ESD\Core\Context\Context;
use ESD\Server\Coroutine\Server;
use ESD\Plugins\Console\ConsolePlugin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestCmd
 * @package ESD\Plugins\PHPUnit
 */
class TestCmd extends Command
{
    /**
     * @var Context
     */
    private $context;

    /**
     * StartCmd constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct();
        $this->context = $context;
    }

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('test')->setDescription("PHPUnit");
        $this->addArgument("file", InputArgument::OPTIONAL, "File or folder path","tests");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \ReflectionException
     * @throws \ESD\Core\Plugins\Config\ConfigException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serverConfig = Server::$instance->getServerConfig();
        $serverConfig->setProxyServerClass(UnitServer::class);
        $file = $input->getArgument("file");
        Server::$instance->getContainer()->set("phpunit.file", $file);
        //Add the unit process
        Server::$instance->addProcess(PHPUnitPlugin::PROCESS_NAME, PHPUnitProcess::class, PHPUnitPlugin::PROCESS_GROUP_NAME);
        return ConsolePlugin::NOEXIT;
    }
}