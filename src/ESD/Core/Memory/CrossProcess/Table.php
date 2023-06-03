<?php
/**
 * ESD framework
 * @author tmtbe <896369042@qq.com>
 */

namespace ESD\Core\Memory\CrossProcess;

/**
 * Class Table
 * An ultra-high performance, concurrent data structure based on shared memory and locks.
 * Used to solve multi-process/multi-thread data sharing and synchronization locking issues.
 * Supports multiple processes
 * Use shared memory to save data. Before creating a child process, be sure to execute Table->create()
 * Table->create() must be executed before Server->start()
 *
 * @package ESD\BaseServer\Memory
 */
class Table implements \Iterator, \Countable
{
    /**
     * Type int, default is 4 bytes, you can set a total of 1, 2, 4, and 8 lengths.
     */
    const TYPE_INT = \Swoole\Table::TYPE_INT;

    /**
     * Type float, 8 bytes
     */
    const TYPE_FLOAT = \Swoole\Table::TYPE_FLOAT;

    /**
     * Type String, the string must be less than this length
     */
    const TYPE_STRING = \Swoole\Table::TYPE_STRING;


    private $swooleTable;

    /**
     * Table constructor.
     *
     * @param int $size The parameter specifies the maximum number of rows in the table.
     * If $size is not an N-th power of 2, such as 1024, 8192, 65536, etc., the bottom layer will automatically adjust to a close number.
     * If it is less than 1024, it defaults to 1024, which means that 1024 is the minimum
     * @param float $conflictProportion
     */
    public function __construct(int $size, float $conflictProportion = 0.2)
    {
        $this->swooleTable = new \Swoole\Table($size, $conflictProportion);
    }

    /**
     * Add column
     *
     * @param string $name filed name
     * @param int $type Field type, supports 3 types, TYPE_INT, TYPE_FLOAT, TYPE_STRING
     * @param int $size The maximum length of the string field, in bytes. Fields of type string must specify $size
     */
    public function column(string $name, int $type, int $size = 0)
    {
        $this->swooleTable->column($name, $type, $size);
    }

    /**
     * Create a memory table.
     * After defining the structure of the table, execute create to request memory from the operating system and create the table
     * You cannot use set, get and other data read and write operations before calling create
     * You cannot use the column method to add new fields after calling create
     * Insufficient system memory, application failed, return false
     * Application success, return true
     *
     * @return bool
     */
    public function create(): bool
    {
        return $this->swooleTable->create();
    }

    /**
     * Set row data, Table uses key-value to access data.
     * You can set the value of all fields, or modify only some fields
     * Before setting, all fields of this row of data are blank
     * If the length of the incoming string exceeds the maximum size set when the column is defined, the bottom layer will be automatically truncated.
     *
     * @param string $key The key of the data. The same $key corresponds to the same row of data.
     * If the same key is set, the previous data will be overwritten.
     * The key is not binary safe and must be a string type. Binary data must not be passed in.
     * @param array $value must be an array and must be exactly the same as the field's $name
     */
    public function set(string $key, array $value): void
    {
        $this->swooleTable->set($key, $value);
    }

    /**
     * Atomic increment operation.
     *
     * @param string $key specifies the data key. If the row corresponding to $key does not exist, the default column value is 0
     * @param string $column specifies the column name, only floating point and integer fields are supported
     * @param mixed $incrBy increment, default is 1. If the column is integer, $incrBy must be int, if the column is floating point, $incrBy must be float
     * @return int | float returns the final result value
     */
    public function incr(string $key, string $column, $incrBy = 1)
    {
        return $this->swooleTable->incr($key, $column, $incrBy);
    }

    /**
     * Atomic decrement operation.
     *
     * @param string $key specifies the data key. If the row corresponding to $key does not exist, the default column value is 0
     * @param string $column specifies the column name, only floating point and integer fields are supported
     * @param mixed $incrBy increment, default is 1. If the column is integer, $incrBy must be int, if the column is floating point, $incrBy must be float
     * @return int | float returns the final result value
     */
    public function decr(string $key, string $column, $incrBy = 1)
    {
        return $this->swooleTable->decr($key, $column, $incrBy);
    }

    /**
     * Get a row of data
     * 
     * @param string $key specifies the KEY of the query data row, which must be a string type
     * @param string|null $field returns only the value of the field when $field is specified, not the entire record
     * @return mixed If the final result value $key does not exist, it will return false, and the result array will be returned successfully
     */
    public function get(string $key, string $field = null)
    {
        return $this->swooleTable->get($key, $field);
    }

    /**
     * Check if a certain key exists in the table.
     *
     * @param string $key
     * @return bool
     */
    public function exist(string $key): bool
    {
        return $this->swooleTable->exist($key);
    }

    /**
     * the number of entries in the table
     *
     * @return int
     */
    public function count(): int
    {
        return $this->swooleTable->count();
    }

    /**
     * Delete data
     *
     * @param $key $key对应的数据不存在，将返回false
     * @return bool 成功删除返回true
     */
    public function del($key): bool
    {
        return $this->swooleTable->del($key);
    }

    /**
     * Return the current element
     *
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current(): mixed
    {
        return $this->swooleTable->current();
    }

    /**
     * Move forward to next element
     *
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next(): void
    {
        $this->swooleTable->next();
    }

    /**
     * Return the key of the current element
     *
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key(): mixed
    {
        return $this->swooleTable->key();
    }

    /**
     * Checks if current position is valid
     *
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid(): bool
    {
        return $this->swooleTable->valid();
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind(): void
    {
        $this->swooleTable->rewind();
    }
}