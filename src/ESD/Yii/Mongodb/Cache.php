<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace ESD\Yii\Mongodb;

use ESD\Yii\Yii;
use ESD\Yii\Base\InvalidConfigException;
use ESD\Yii\Di\Instance;

/**
 * Cache implements a cache application component by storing cached data in a MongoDB.
 *
 * By default, Cache stores session data in a MongoDB collection named 'cache' inside the default database.
 * This collection is better to be pre-created with fields 'id' and 'expire' indexed.
 * The collection name can be changed by setting [[cacheCollection]].
 *
 * Please refer to [[\ESD\Yii\Caching\Cache]] for common cache operations that are supported by Cache.
 *
 * The following example shows how you can configure the application to use Cache:
 *
 * ```php
 * 'cache' => [
 *     'class' => 'ESD\Yii\Mongodb\Cache',
 *     // 'db' => 'mymongodb',
 *     // 'cacheCollection' => 'my_cache',
 * ]
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Cache extends \ESD\Yii\Caching\Cache
{
    /**
     * @var Connection|array|string the MongoDB connection object or the application component ID of the MongoDB connection.
     * After the Cache object is created, if you want to change this property, you should only assign it
     * with a MongoDB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'mongodb';
    /**
     * @var string|array the name of the MongoDB collection that stores the cache data.
     * Please refer to [[Connection::getCollection()]] on how to specify this parameter.
     * This collection is better to be pre-created with fields 'id' and 'expire' indexed.
     */
    public $cacheCollection = 'cache';
    /**
     * @var int the probability (parts per million) that garbage collection (GC) should be performed
     * when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
     * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
     */
    public $gcProbability = 100;


    /**
     * Initializes the Cache component.
     * This method will initialize the [[db]] property to make sure it refers to a valid MongoDB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * Retrieves a value from cache with a specified key.
     * This method should be implemented by child classes to retrieve the data
     * from specific cache storage.
     * @param string $key a unique key identifying the cached value
     * @return string|bool the value stored in cache, false if the value is not in the cache or expired.
     */
    protected function getValue($key)
    {
        $query = new Query;
        $row = $query->select(['data'])
            ->from($this->cacheCollection)
            ->where([
                'id' => $key,
                '$or' => [
                    [
                        'expire' => 0
                    ],
                    [
                        'expire' => ['$gt' => time()]
                    ],
                ],
            ])
            ->one($this->db);

        if (empty($row)) {
            return false;
        }
        return $row['data'];
    }

    /**
     * Stores a value identified by a key in cache.
     * This method should be implemented by child classes to store the data
     * in specific cache storage.
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
     */
    protected function setValue($key, $value, $duration): bool
    {
        $result = $this->db->getCollection($this->cacheCollection)
            ->update(['id' => $key], [
                'expire' => $duration > 0 ? $duration + time() : 0,
                'data' => $value,
            ]);

        if ($result) {
            $this->gc();
            return true;
        }
        return $this->addValue($key, $value, $duration);
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * This method should be implemented by child classes to store the data
     * in specific cache storage.
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
     */
    protected function addValue($key, $value, $duration): bool
    {
        $this->gc();

        if ($duration > 0) {
            $duration += time();
        } else {
            $duration = 0;
        }

        try {
            $this->db->getCollection($this->cacheCollection)
                ->insert([
                    'id' => $key,
                    'expire' => $duration,
                    'data' => $value,
                ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Deletes a value with the specified key from cache
     * This method should be implemented by child classes to delete the data from actual cache storage.
     * @param string $key the key of the value to be deleted
     * @return bool if no error happens during deletion
     * @throws \ESD\Yii\Mongodb\Exception
     */
    protected function deleteValue($key): bool
    {
        $this->db->getCollection($this->cacheCollection)->remove(['id' => $key]);
        return true;
    }

    /**
     * Deletes all values from cache.
     * Child classes may implement this method to realize the flush operation.
     * @return bool whether the flush operation was successful.
     * @throws \ESD\Yii\Mongodb\Exception
     */
    protected function flushValues(): bool
    {
        $this->db->getCollection($this->cacheCollection)->remove();
        return true;
    }

    /**
     * Removes the expired data values.
     * @param bool $force whether to enforce the garbage collection regardless of [[gcProbability]].
     * Defaults to false, meaning the actual deletion happens with the probability as specified by [[gcProbability]].
     * @throws \ESD\Yii\Mongodb\Exception
     */
    public function gc($force = false)
    {
        if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
            $this->db->getCollection($this->cacheCollection)
                ->remove([
                    'expire' => [
                        '$gt' => 0,
                        '$lt' => time(),
                    ]
                ]);
        }
    }
}
