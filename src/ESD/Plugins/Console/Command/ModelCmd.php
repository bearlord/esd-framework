<?php

namespace ESD\Plugins\Console\Command;

use ESD\Core\Context\Context;
use ESD\Core\Server\Server;
use ESD\Plugins\Console\ConsolePlugin;
use ESD\Yii\Helpers\Inflector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ModelCmd extends Command
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
        $this->setName('model')->setDescription("model generator");


        $this->addOption('table_name', null, InputOption::VALUE_REQUIRED, 'table name?', '');
        $this->addOption('namespace', 'nc', InputOption::VALUE_OPTIONAL, 'namespace?', 'App\Model');
        $this->addOption('model_class', 'mc', InputOption::VALUE_OPTIONAL, 'model class?', '');
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \ESD\Core\Exception
     * @throws \ESD\Plugins\Mysql\MysqlException
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $tableName = $input->getOption("table_name");
        $namespace = $input->getOption('namespace');
        $modelClass = $input->getOption('model_class');

        $tablePrefix = Server::$instance->getConfigContext()->get("esd-yii.db.default.tablePrefix");

        var_dump($tablePrefix);

        if (empty($modelClass)) {
            $modelClass = ltrim($tableName, $tablePrefix);
            $modelClass = Inflector::camelize($modelClass);
        }

        var_dump($tableName, $namespace, $modelClass);
//        return ConsolePlugin::SUCCESS_EXIT;
    }
}