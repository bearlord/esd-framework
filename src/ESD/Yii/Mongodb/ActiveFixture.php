<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Mongodb;

use ESD\Yii\Yii;
use ESD\Yii\Base\InvalidConfigException;
use ESD\Yii\Test\BaseActiveFixture;

/**
 * ActiveFixture represents a fixture backed up by a [[modelClass|MongoDB ActiveRecord class]] or a [[collectionName|MongoDB collection]].
 *
 * Either [[modelClass]] or [[collectionName]] must be set. You should also provide fixture data in the file
 * specified by [[dataFile]] or overriding [[getData()]] if you want to use code to generate the fixture data.
 *
 * When the fixture is being loaded, it will first call [[resetCollection()]] to remove any existing data in the collection.
 * It will then populate the collection with the data returned by [[getData()]].
 *
 * After the fixture is loaded, you can access the loaded data via the [[data]] property. If you set [[modelClass]],
 * you will also be able to retrieve an instance of [[modelClass]] with the populated data via [[getModel()]].
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveFixture extends BaseActiveFixture
{
    /**
     * @var Connection|string the DB connection object or the application component ID of the DB connection.
     */
    public $db = 'mongodb';
    /**
     * @var string|array the collection name that this fixture is about. If this property is not set,
     * the collection name will be determined via [[modelClass]].
     * @see Connection::getCollection()
     */
    public $collectionName;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (!isset($this->modelClass) && !isset($this->collectionName)) {
            throw new InvalidConfigException('Either "modelClass" or "collectionName" must be set.');
        }
    }

    /**
     * Loads the fixture data.
     * The default implementation will first reset the MongoDB collection and then populate it with the data
     * returned by [[getData()]].
     */
    public function load()
    {
        $this->resetCollection();
        $this->data = [];
        $data = $this->getData();
        if (empty($data)) {
            return;
        }
        $this->getCollection()->batchInsert($data);
        foreach ($data as $alias => $row) {
            $this->data[$alias] = $row;
        }
    }

    /**
     * Returns collection used by this fixture.
     * @return Collection related collection.
     */
    protected function getCollection()
    {
        return $this->db->getCollection($this->getCollectionName());
    }

    /**
     * Returns collection name used by this fixture.
     * @return array|string related collection name
     */
    protected function getCollectionName()
    {
        if ($this->collectionName) {
            return $this->collectionName;
        }

        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        return $modelClass::collectionName();
    }

    /**
     * Returns the fixture data.
     *
     * This method is called by [[loadData()]] to get the needed fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return an array of data rows (column name => column value), each corresponding to a row in the collection.
     *
     * If the data file does not exist, an empty array will be returned.
     *
     * @return array the data rows to be inserted into the collection.
     * @throws \ESD\Yii\Base\InvalidConfigException
     */
    protected function getData(): array
    {
        if ($this->dataFile === null) {
            $class = new \ReflectionClass($this);

            $collectionName = $this->getCollectionName();
            $dataFile = dirname($class->getFileName()) . '/data/' . (is_array($collectionName) ? implode('.', $collectionName) : $collectionName) . '.php';

            return is_file($dataFile) ? require($dataFile) : [];
        }

        return parent::getData();
    }

    /**
     * Removes all existing data from the specified collection and resets sequence number if any.
     * This method is called before populating fixture data into the collection associated with this fixture.
     */
    protected function resetCollection()
    {
        $this->getCollection()->remove();
    }
}
