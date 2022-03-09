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

class StopCmd extends Command
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
        $this->setName('stop')->setDescription("Stop(Kill) server");
        $this->addOption('kill', "k", InputOption::VALUE_NONE, 'Kill server?');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $serverConfig = Server::$instance->getServerConfig();

        $serverName = $serverConfig->getName();
        $masterPid = exec("ps -ef | grep $serverName-master | grep -v 'grep ' | awk '{print $2}'");
        if (empty($masterPid)) {
            $io->warning("server $serverName not run");
            return ConsolePlugin::SUCCESS_EXIT;
        }

        if ($input->getOption('kill')) {
            //kill -9
            exec("ps -ef|grep $serverName|grep -v grep|cut -c 9-15|xargs kill -9");
            return ConsolePlugin::SUCCESS_EXIT;
        }

        // Send stop signal to master process.
        $masterPid && posix_kill($masterPid, SIGTERM);
        // Timeout.
        $timeout = 40;
        $startTime = time();
        // Check master process is still alive?
        while (1) {
            $masterIsAlive = $masterPid && posix_kill($masterPid, 0);
            if ($masterIsAlive) {
                // Timeout?
                if (time() - $startTime >= $timeout) {
                    $io->warning("Server $serverName stop fail");
                    return ConsolePlugin::FAIL_EXIT;
                }
                // Waiting amoment.
                usleep(10000);
                continue;
            }
            // Stop success.
            $io->note("Server $serverName stop success");
            break;
        }
        return ConsolePlugin::SUCCESS_EXIT;
    }
}