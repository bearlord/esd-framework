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
class Config extends \ESD\Core\Pool\Config
{
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
     * @return string
     */
    protected function getKey()
    {
        return 'mongodb';
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