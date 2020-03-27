<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Plugins\Console\Command;

use ESD\Core\Context\Context;
use ESD\Plugins\Console\ConsolePlugin;
use ESD\Core\Server\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ReloadCmd
 * @package ESD\Plugins\Console\Command
 */
class ReloadCmd extends Command
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
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('reload')->setDescription("Reload server");
        $this->addOption('clearCache', "c", InputOption::VALUE_NONE, 'Who do you want to clear cache?');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \ESD\Core\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $serverConfig = Server::$instance->getServerConfig();
        $server_name = $serverConfig->getName();

        $masterPid = exec("ps -ef | grep $server_name-master | grep -v 'grep ' | awk '{print $2}'");
        $managerPid = exec("ps -ef | grep $server_name-manager | grep -v 'grep ' | awk '{print $2}'");
        if (empty($masterPid)) {
            $io->warning("server $server_name not run");
            return ConsolePlugin::SUCCESS_EXIT;
        }

        if ($input->getOption('clearCache')) {
            $io->note("Clear cache file");

            $serverConfig = Server::$instance->getServerConfig();
            if (file_exists($serverConfig->getCacheDir() . "/aop")) {
                clearDir($serverConfig->getCacheDir() . "/aop");
            }
            if (file_exists($serverConfig->getCacheDir() . "/di")) {
                clearDir($serverConfig->getCacheDir() . "/di");
            }
            if (file_exists($serverConfig->getCacheDir() . "/proxies")) {
                clearDir($serverConfig->getCacheDir() . "/proxies");
            }
        }

        posix_kill($managerPid, SIGUSR1);
        $io->success(sprintf("server %s reload", $server_name));
        return ConsolePlugin::SUCCESS_EXIT;
    }
}