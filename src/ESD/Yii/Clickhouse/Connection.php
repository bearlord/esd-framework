<?php

namespace ESD\Yii\Clickhouse;

use ESD\Yii\Base\Component;
use ESD\Yii\Caching\Cache;
use ESD\Yii\HttpClient\Client;
use ESD\Yii\Yii;

/**
 * Class Connection
 * @package ESD\Yii\Clickhouse
 * @property \ESD\Yii\Httpclient\Client $transport
 */
class Connection extends \ESD\Yii\Db\Connection
{
    /**
     * @event Event an event that is triggered after a DB connection is established
     */
    const EVENT_AFTER_OPEN = 'afterOpen';

    /**
     * @var string name use database default use value  "default"
     */
    public $database;

    /**
     * @var string the hostname or ip address to use for connecting to the click-house server. Defaults to 'localhost'.
     */
    public $dsn = 'localhost';

    /**
     * @var integer the port to use for connecting to the click-house server. Default port is 8123.
     */
    public $port = 8123;

    /**
     * @var string
     */
    public $commandClass = 'ESD\Yii\Clickhouse\Command';
    public $schemaClass = 'ESD\Yii\Clickhouse\Schema';
    public $transportClass = 'ESD\Yii\HttpClient\CurlTransport';

    /**
     * @var array
     */
    public $requestConfig = [
        'class' => 'ESD\Yii\Clickhouse\HttpClient\Request',
    ];

    /**
     * @var string[]
     */
    public $schemaMap = [
        'clickhouse' => 'ESD\Yii\Clickhouse\Schema'
    ];

    /**
     * @var bool| Client
     */
    private $_transport = false;

    /**
     * @var string
     */
    private $_schema;

    /**
     * @var array
     */
    private $_options = [];

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * log_queries = 1
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->_options = $options;
    }

    /**
     * @param $sql
     * @param array $params
     * @return \ESD\Yii\Clickhouse\Command
     */
    public function createCommand($sql = null, $params = [])
    {
        $this->open();
        Yii::trace("Executing ClickHouse: {$sql}", __METHOD__);

        /** @var $command \ESD\Yii\Clickhouse\Command */
        $command = new $this->commandClass([
            'db' => $this,
            'sql' => $sql,
        ]);
        $command->addOptions($this->getOptions());

        return $command->bindValues($params);
    }

    /**
     * @return bool|Client
     */
    public function getTransport()
    {
        return $this->_transport;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->_transport !== false;
    }

    public function open()
    {
        if ($this->getIsActive()) {
            return;
        }

        $url = $this->buildDsnUrl();

        $this->_transport = new Client([
            'baseUrl' => $url,
            'transport' => $this->transportClass,
            'requestConfig' => $this->requestConfig,
        ]);
    }

    /**
     * @return string
     */
    private function buildDsnUrl()
    {
        if (strpos($this->dsn, '@') !== false) {
            $url = $this->dsn;
        } else {
            $auth = !empty($this->username) ? $this->username . ':' . $this->password . '@' : '';
            $parsed = parse_url($this->dsn);
            // get default url scheme
            $scheme = array_key_exists('scheme', $parsed) ? $parsed['scheme'] : 'http';
            $dsn = $this->dsn;
            if (strpos($dsn, '://') !== false) {
                $dsn = str_replace($scheme . '://', '', $dsn);
            }

            $url = ($scheme !== '' ? $scheme . '://' : '') . $auth . $dsn . ':' . $this->port;
        }

        $params = [];
        if (!empty($this->database)) {
            $params['database'] = $this->database;
        }
        $url = $this->buildUrl($url, $params);
        return $url;
    }

    /**
     * @param string $url
     * @param array $data
     * @return string
     */
    public function buildUrl(string $url, array $data = [])
    {
        $parsed = parse_url($url);
        isset($parsed['query']) ? parse_str($parsed['query'], $parsed['query']) : $parsed['query'] = [];
        $params = isset($parsed['query']) ? array_merge($parsed['query'], $data) : $data;

        $parsed['query'] = !empty($params) ? '?' . http_build_query($params) : '';
        if (!isset($parsed['path'])) {
            $parsed['path'] = '/';
        }

        $auth = (!empty($parsed['user']) ? $parsed['user'] : '') . (!empty($parsed['pass']) ? ':' . $parsed['pass'] : '');
        $defaultScheme = 'http';

        return (isset($parsed['scheme']) ? $parsed['scheme'] : $defaultScheme)
            . '://'
            . (!empty($auth) ? $auth . '@' : '')
            . $parsed['host']
            . (!empty($parsed['port']) ? ':' . $parsed['port'] : '')
            . $parsed['path']
            . $parsed['query'];
    }

    /**
     * Quotes a string value for use in a query.
     * Note that if the parameter is not a string or int, it will be returned without change.
     * @param string $value string to be quoted
     * @return string the properly quoted string
     */
    public function quoteValue($value): string
    {
        return $this->getSchema()->quoteValue($value);
    }

    /**
     * @param string $sql
     * @return string
     */
    public function quoteSql($sql)
    {
        return $sql;
    }

    /**
     * @return bool
     * @throws \ESD\Yii\Base\InvalidConfigException
     * @throws \ESD\Yii\Db\Exception
     * @throws \ESD\Yii\HttpClient\Exception
     */
    public function ping()
    {
        $this->open();
        $query = 'SELECT 1';
        $response = $this->transport
            ->createRequest()
            ->setHeaders(['Content-Type: application/x-www-form-urlencoded'])
            ->setMethod('POST')
            ->setContent($query)
            ->send();

        return trim($response->content) == '1';
    }

    /**
     * Closes the connection when this component is being serialized.
     * @return array
     */
    public function __sleep()
    {
        $this->close();
        return array_keys(get_object_vars($this));
    }

    /**
     * Closes the currently active DB connection.
     * It does nothing if the connection is already closed.
     */
    public function close()
    {
        if ($this->getIsActive()) {
            $connection = ($this->dsn . ':' . $this->port);
            Yii::trace('Closing DB connection: ' . $connection, __METHOD__);
        }
    }

    /**
     * Initializes the DB connection.
     * This method is invoked right after the DB connection is established.
     * The default implementation triggers an [[EVENT_AFTER_OPEN]] event.
     */
    protected function initConnection()
    {
        $this->trigger(self::EVENT_AFTER_OPEN);
    }

    /**
     * @return object|\ESD\Yii\Clickhouse\Schema
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    public function getSchema()
    {
        return $this->_schema = Yii::createObject([
            'class' => $this->schemaClass,
            'db' => $this
        ]);
    }

    /**
     * @param string $name
     * @return string
     */
    public function quoteTableName($name)
    {
        return $name;
    }

    /**
     * @return string
     */
    public function getDriverName()
    {
        return 'clickhouse';
    }

    /**
     * @param string $name
     * @return string
     */
    public function quoteColumnName($name)
    {
        return $name;
    }

    /**
     * Returns the query builder for the current DB connection.
     * @return QueryBuilder the query builder for the current DB connection.
     */
    public function getQueryBuilder()
    {
        return $this->getSchema()->getQueryBuilder();
    }
}
