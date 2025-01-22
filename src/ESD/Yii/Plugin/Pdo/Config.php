<?php
/**
 * ESD framework
 * @author bearlord <565364226@qq.com>
 */

namespace ESD\Yii\Plugin\Pdo;

use ESD\Core\Plugins\Config\BaseConfig;

/**
 * Class Config
 * @package ESD\Yii\Plugin\Pdo
 */
class Config extends \ESD\Core\Pool\Config
{
    /**
     * @var string
     */
    protected $dsn = "";

    /**
     * @var username
     */
    protected $username = "";

    /**
     * @var password
     */
    protected $password = "";

    /**
     * @var table prefix
     */
    protected $tablePrefix = "";

    /**
     * @var string charset
     */
    protected $charset = "utf8";

    /**
     * @var Enable schema cache
     */
    protected $enableSchemaCache = false;

    /**
     * @var Schema cache duration
     */
    protected $schemaCacheDuration = false;

    /**
     * @var Schema cache component
     */
    protected $schemaCache = "cache";

    /**
     * @return string
     */
    protected function getKey()
    {
        return 'pdo';
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
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
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
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getEnableSchemaCache(): string
    {
        return $this->enableSchemaCache;
    }

    /**
     * @param bool|int|mixed $enable
     */
    public function setEnableSchemaCache($enable): void
    {
        $this->enableSchemaCache = (bool)$enable;
    }

    /**
     * @return string
     */
    public function getSchemaCacheDuration(): string
    {
        return $this->schemaCacheDuration;
    }

    /**
     * @param int $duratioin
     */
    public function setSchemaCacheDuration($duratioin): void
    {
        $this->schemaCacheDuration = $duratioin;
    }

    /**
     * @return string
     */
    public function getSchemaCache(): string
    {
        return $this->schemaCache;
    }

    /**
     * @param string $cache
     */
    public function setSchemaCache($cache): void
    {
        if (!empty($cache)) {
            $this->schemaCache = $cache;
        }
    }

    /**
     * Build config
     * @return array
     */
    public function buildConfig()
    {
        return [
            'dsn' => $this->getDsn(),
            'name' => $this->getName(),
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'tablePrefix' => $this->getTablePrefix(),
            'charset' => $this->getCharset(),
            'poolMaxNumber' => $this->getPoolMaxNumber(),
            'enableSchemaCache' => $this->getEnableSchemaCache(),
            'schemaCacheDuration' => $this->getSchemaCacheDuration(),
            'schemaCache' => $this->getSchemaCache()
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
