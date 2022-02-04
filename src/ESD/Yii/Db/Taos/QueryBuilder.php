<?php

namespace ESD\Yii\Db\Taos;

use ESD\Yii\Base\InvalidArgumentException;
use ESD\Yii\Base\NotSupportedException;
use ESD\Yii\Db\Conditions\ConditionInterface;
use ESD\Yii\Db\Conditions\HashCondition;
use ESD\Yii\Db\Exception;
use ESD\Yii\Db\ExpressionInterface;
use ESD\Yii\Db\Query;
use ESD\Yii\Helpers\StringHelper;
use ESD\Yii\Yii;

class QueryBuilder extends \ESD\Yii\Db\QueryBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return array_merge(parent::defaultExpressionBuilders(), [
            'ESD\Yii\Db\Conditions\InCondition' => 'ESD\Yii\Db\Toas\Conditions\InConditionBuilder',
            'ESD\Yii\Db\Conditions\LikeCondition' => 'ESD\Yii\Db\Toas\conditions\LikeConditionBuilder',
        ]);
    }

    /**
     * Transforms $condition defined in array format (as described in [[Query::where()]]
     * to instance of [[ESD\Yii\Db\condition\ConditionInterface|ConditionInterface]] according to
     * [[conditionClasses]] map.
     *
     * @param string|array $condition
     * @see conditionClasses
     * @return ConditionInterface
     * @since 2.0.14
     */
    public function createConditionFromArray($condition)
    {
        if (isset($condition[0])) { // operator format: operator, operand 1, operand 2, ...
            $operator = strtoupper(array_shift($condition));
            if (isset($this->conditionClasses[$operator])) {
                $className = $this->conditionClasses[$operator];
            } else {
                $className = 'ESD\Yii\Db\Conditions\SimpleCondition';
            }
            /** @var ConditionInterface $className */
            return $className::fromArrayDefinition($operator, $condition);
        }

        // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
        return new HashCondition($condition);
    }

    /**
     * Creates an INSERT SQL statement.
     * For example,
     * ```php
     * $sql = $queryBuilder->insert('device_log', [
     *     'device_id' => '6c447a8fe7677ddc4c4cd2efddcfe650e4e6c706',
     *     'device_state' => 'normal',
     * ], $params);
     * ```
     * The method will properly escape the table and column names.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array|Query $columns the column data (name => value) to be inserted into the table or instance
     * of [[ESD\Yii\Db\Query|Query]] to perform INSERT INTO ... SELECT SQL statement.
     * Passing of [[ESD\Yii\Db\Query|Query]] is available since version 2.0.11.
     * @param array $params the binding parameters that will be generated by this method.
     * They should be bound to the DB command later.
     * @return string the INSERT SQL
     */
    public function insert($table, $columns, &$params)
    {
        list($names, $placeholders, $values, $params) = $this->prepareInsertValues($table, $columns, $params);

        return 'INSERT INTO ' . $table
            . (!empty($names) ? ' (' . implode(', ', $names) . ')' : '')
            . (!empty($placeholders) ? ' VALUES (' . implode(', ', $placeholders) . ')' : $values);
    }

    /**
     * Prepares a `VALUES` part for an `INSERT` SQL statement.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array|Query $columns the column data (name => value) to be inserted into the table or instance
     * of [[ESD\Yii\Db\Query|Query]] to perform INSERT INTO ... SELECT SQL statement.
     * @param array $params the binding parameters that will be generated by this method.
     * They should be bound to the DB command later.
     * @return array array of column names, placeholders, values and params.
     * @since 2.0.14
     */
    protected function prepareInsertValues($table, $columns, $params = [])
    {
        $names = [];
        $placeholders = [];
        $values = '';
        if ($columns instanceof Query) {
            list($names, $values, $params) = $this->prepareInsertSelectSubQuery($columns, $schema, $params);
        } else {
            foreach ($columns as $name => $value) {
                $names[] = $name;

                if ($value instanceof ExpressionInterface) {
                    $placeholders[] = $this->buildExpression($value, $params);
                } elseif ($value instanceof \ESD\Yii\Db\Query) {
                    list($sql, $params) = $this->build($value, $params);
                    $placeholders[] = "($sql)";
                } else {
                    $placeholders[] = $this->bindParam($value, $params);
                }
            }
        }
        return [$names, $placeholders, $values, $params];
    }

    /**
     * Prepare select-subquery and field names for INSERT INTO ... SELECT SQL statement.
     *
     * @param Query $columns Object, which represents select query.
     * @param \ESD\Yii\Db\Schema $schema Schema object to quote column name.
     * @param array $params the parameters to be bound to the generated SQL statement. These parameters will
     * be included in the result with the additional parameters generated during the query building process.
     * @return array array of column names, values and params.
     * @throws InvalidArgumentException if query's select does not contain named parameters only.
     * @since 2.0.11
     */
    protected function prepareInsertSelectSubQuery($columns, $schema, $params = [])
    {
        if (!is_array($columns->select) || empty($columns->select) || in_array('*', $columns->select)) {
            throw new InvalidArgumentException('Expected select query object with enumerated (named) parameters');
        }

        list($values, $params) = $this->build($columns, $params);
        $names = [];
        $values = ' ' . $values;
        foreach ($columns->select as $title => $field) {
            if (is_string($title)) {
                $names[] = $title;
            } elseif (preg_match('/^(.*?)(?i:\s+as\s+|\s+)([\w\-_\.]+)$/', $field, $matches)) {
                $names[] = $matches[2];
            } else {
                $names[] = $field;
            }
        }

        return [$names, $values, $params];
    }

    /**
     * Generates a batch INSERT SQL statement.
     *
     * For example,
     *
     * ```php
     * $sql = $queryBuilder->batchInsert('user', ['name', 'age'], [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ]);
     * ```
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * The method will properly escape the column names, and quote the values to be inserted.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column names
     * @param array|\Generator $rows the rows to be batch inserted into the table
     * @param array $params the binding parameters. This parameter exists since 2.0.14
     * @return string the batch INSERT SQL statement
     */
    public function batchInsert($table, $columns, $rows, &$params = [])
    {
        if (empty($rows)) {
            return '';
        }

        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }

        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $i => $value) {
                if (isset($columns[$i], $columnSchemas[$columns[$i]])) {
                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
                }
                if (is_string($value)) {
                    $value = $schema->quoteValue($value);
                } elseif (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    $value = StringHelper::floatToString($value);
                } elseif ($value === false) {
                    $value = 0;
                } elseif ($value === null) {
                    $value = 'NULL';
                } elseif ($value instanceof ExpressionInterface) {
                    $value = $this->buildExpression($value, $params);
                }
                $vs[] = $value;
            }
            $values[] = '(' . implode(', ', $vs) . ')';
        }
        if (empty($values)) {
            return '';
        }

        return 'INSERT INTO ' . $schema->quoteTableName($table)
            . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $values);
    }

    /**
     * Creates a DELETE SQL statement.
     *
     * For example,
     *
     * ```php
     * $sql = $queryBuilder->delete('user', 'status = 0');
     * ```
     *
     * The method will properly escape the table and column names.
     *
     * @param string $table the table where the data will be deleted from.
     * @param array|string $condition the condition that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify condition.
     * @param array $params the binding parameters that will be modified by this method
     * so that they can be bound to the DB command later.
     * @return string the DELETE SQL
     */
    public function delete($table, $condition, &$params)
    {
        $sql = 'DELETE FROM ' . $table;
        $where = $this->buildWhere($condition, $params);

        return $where === '' ? $sql : $sql . ' ' . $where;
    }

    /**
     * Builds a SQL statement for creating a new DB table.
     *
     * The columns in the new table should be specified as name-definition pairs (e.g. 'device_id' => 'string'),
     * where name stands for a column name which will be properly quoted by the method, and definition
     * stands for the column type which can contain an abstract DB type.
     * The [[getColumnType()]] method will be invoked to convert any abstract type into a physical one.
     *
     *
     * For example,
     *
     * ```php
     * $sql = $queryBuilder->createTable('device_log', [
     *  'created_timestamp' => 'timestamp',
     *  'device_id' => 'nchar(40)',
     *  'device_state' => 'nchar(20)',
     * ]);
     * ```
     *
     * @param string $table the name of the table to be created. The name will be properly quoted by the method.
     * @param array $columns the columns (name => definition) in the new table.
     * @param string $options additional SQL fragment that will be appended to the generated SQL.
     * @return string the SQL statement for creating a new DB table.
     */
    public function createTable($table, $columns, $options = null)
    {
        $cols = [];
        foreach ($columns as $name => $type) {
            if (is_string($name)) {
                $cols[] = "\t" . $name . ' ' . $this->getColumnType($type);
            } else {
                $cols[] = "\t" . $type;
            }
        }
        $sql = 'CREATE TABLE ' . $table . " (\n" . implode(",\n", $cols) . "\n)";

        return $options === null ? $sql : $sql . ' ' . $options;
    }


    /**
     * Builds a SQL statement for renaming a DB table.
     * @param string $oldName the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB table.
     */
    public function renameTable($oldName, $newName)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    /**
     * Builds a SQL statement for dropping a DB table.
     * @param string $table the table to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a DB table.
     */
    public function dropTable($table)
    {
        return 'DROP TABLE ' . $table;
    }

    public function upsert($table, $insertColumns, $updateColumns, &$params)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function update($table, $columns, $condition, &$params)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function addPrimaryKey($name, $table, $columns)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function dropPrimaryKey($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    /**
     * Builds a SQL statement for adding a new DB column.
     * @param string $table the table that the new column will be added to.
     * @param string $column the name of the new column. The name will be properly quoted by the method.
     * @param string $type the column type. The [[getColumnType()]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for adding a new column.
     */
    public function addColumn($table, $column, $type)
    {
        return 'ALTER TABLE ' . $table
            . ' ADD ' . $column . ' '
            . $this->getColumnType($type);
    }

    /**
     * Builds a SQL statement for dropping a DB column.
     * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
     * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a DB column.
     */
    public function dropColumn($table, $column)
    {
        return 'ALTER TABLE ' . $table
            . ' DROP COLUMN ' . $column;
    }

    public function renameColumn($table, $oldName, $newName)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    /**
     * Builds a SQL statement for modify the lenth of the DB column.
     * If the type of the data column is variable length format (BINARY or NCHAR), you can use this command to modify
     * its width (only larger, not smaller). (New in TDengine version 2.1.3.0)
     * If the table is created through a supertable, operations that change the table structure can only be performed
     * on the supertable. At the same time, changes to the structure of the super table take effect on all tables
     * created through the structure. For tables not created through supertables,
     * the table structure can be modified directly.
     * @param string $table the table that the column will be modified.
     * @param string $column the name of the new column. The name will be properly quoted by the method.
     * @param string $type the column type. The [[getColumnType()]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for adding a new column.
     */
    public function alterColumn($table, $column, $type)
    {
        return 'ALTER TABLE ' . $table
            . ' MODIFY COLUMN ' . $column . ' '
            . $this->getColumnType($type);
    }

    public function addTag($table, $tag, $type)
    {
        return 'ALTER TABLE ' . $table
            . ' ADD TAG ' . $tag . ' '
            . $type;
    }

    public function dropTag($table, $tag)
    {
        return 'ALTER TABLE ' . $table
            . ' DROP TAG ' . $tag;
    }

    public function changeTag($table, $oldTag, $newTag)
    {
        return 'ALTER TABLE ' . $table
            . ' CHANGE TAG ' . $oldTag . ' '
            . $newTag;
    }

    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function dropForeignKey($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function createIndex($name, $table, $columns, $unique = false)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function dropIndex($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function addUnique($name, $table, $columns)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function dropUnique($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function addCheck($name, $table, $expression)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function dropCheck($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function addDefaultValue($name, $table, $column, $value)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function dropDefaultValue($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function resetSequence($table, $value = null)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function addCommentOnColumn($table, $column, $comment)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function addCommentOnTable($table, $comment)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function dropCommentFromColumn($table, $column)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function dropCommentFromTable($table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function createView($viewName, $subQuery)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }
    
    public function dropView($viewName)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }

    public function truncateTable($table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by TDengine.');
    }
}