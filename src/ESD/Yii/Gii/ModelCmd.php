<?php

namespace ESD\Yii\Gii;

use ESD\Core\Context\Context;
use ESD\Core\Server\Server;
use ESD\Plugins\Console\ConsolePlugin;
use ESD\Yii\Gii\Console\GenerateController;
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
        $this->setName('model')->setDescription("Model generator");

        $this->addOption('tableName', null, InputOption::VALUE_REQUIRED, 'table name?', '');
        $this->addOption('namespace', 'nc', InputOption::VALUE_OPTIONAL, 'namespace?', 'App\Model');
        $this->addOption('modelClass', 'mc', InputOption::VALUE_OPTIONAL, 'model class?', '');
        $this->addOption('standardizeCapitals', null, InputOption::VALUE_OPTIONAL, 'standardize capitals?', 0);
        $this->addOption('singularize', null, InputOption::VALUE_OPTIONAL, 'singularize?', 0);
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
        $tableName = $input->getOption("tableName");
        $namespace = $input->getOption('namespace');
        $modelClass = $input->getOption('modelClass');
        $standardizeCapitals = $input->getOption('standardizeCapitals');
        $singularize = $input->getOption('singularize');

        if (strcmp($standardizeCapitals, "true") === 0) {
            $standardizeCapitals = true;
        } else {
            $standardizeCapitals = false;
        }

        if (strcmp($singularize, "true") === 0) {
            $singularize = true;
        } else {
            $singularize = false;
        }

        $generateLabelsFromComments = true;
        $useTablePrefix = true;

        /*
         *
        $tablePrefix = Server::$instance->getConfigContext()->get("yii.db.default.tablePrefix");
        if (empty($modelClass)) {
            $modelClass = ltrim($tableName, $tablePrefix);
            $modelClass = Inflector::camelize($modelClass);
        }
        */

        $controller = new GenerateController();
        $controller->runAction("model", [
            'tableName' => $tableName,
            'ns' => $namespace,
            'modelClass' => $modelClass,
            'generateLabelsFromComments' => $generateLabelsFromComments,
            'useTablePrefix' => $useTablePrefix,
            'standardizeCapitals' => $standardizeCapitals,
            'singularize' => $singularize,
        ]);
        return ConsolePlugin::SUCCESS_EXIT;

    }
}