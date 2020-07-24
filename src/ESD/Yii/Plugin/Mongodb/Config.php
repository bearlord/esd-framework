<?php
/**
 * ESD Yii mongodb plugin
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Mongodb;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class Config
 * @package ESD\Yii\Plugin\Mongodb
 */
class Config extends BaseConfig
{
    const key = "mongodb";
    /**
     * @var string
     */
    protected $name = "default";
    /**
     * @var int
     */
    protected $poolMaxNumber = 5;
    /**
     * @var string
     */
    protected $dsn = "";
    /**
     * @var string table prefix
     */
    protected $tablePrefix = "";

    /** @var array  */
    protected $options = [];

    /**
     * Config constructor.
     * @param $name
     */
    public function __construct($name)
    {
        parent::__construct(self::key, true, "name");
        $this->setName($name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPoolMaxNumber(): int
    {
        return $this->poolMaxNumber;
    }

    /**
     * @param int $poolMaxNumber
     */
    public function setPoolMaxNumber(int $poolMaxNumber): void
    {
        $this->poolMaxNumber = $poolMaxNumber;
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        return $this->dsn;
    }

    /**
     * @param string $dsn
     */
    public function setDsn(string $dsn): void
    {
        $this->dsn = $dsn;
    }

    /**
     * @return string
     */
    public function getDb(): string
    {
        return $this->db;
    }

    /**
     * @param string $db
     */
    public function setDb(string $db): void
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * @param string $tablePrefix
     */
    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }


    /**
     * Build config
     * @return array
     */
    public function buildConfig()
    {
        return [
            'dsn' => $this->dsn,
            'name' => $this->name,
            'tablePrefix' => $this->tablePrefix,
            'options' => $this->options,
            'poolMaxNumber' => $this->poolMaxNumber
        ];
    }

    /**
     * Build config from array
     *
     * @param $array
     * @return Config
     */
    public function buildFromArray($array)
    {
        $self = new self();
        $self->setDsn($array['dsn']);
        $self->setTablePrefix($array['tablePrefix']);
        $self->setOptions($array['options']);
        $self->setPoolMaxNumber($array['poolMaxNumber']);

        return $self;
    }


    /**
     * Returns the name of the DB driver. Based on the the current [[dsn]], in case it was not set explicitly
     * by an end user.
     * @return string name of the DB driver
     */
    public function getDriverName()
    {
        if (($pos = strpos($this->dsn, ':')) !== false) {
            return strtolower(substr($this->dsn, 0, $pos));
        }
    }
}