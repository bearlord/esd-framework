<?php

namespace ESD\Yii\Db\Taos;

use ESD\Yii\Db\Exception;
use ESD\Yii\Db\PdoValue;
use ESD\Yii\Yii;

/**
 * Command represents an TDengine's SQL statement to be executed against a database.
 *
 * {@inheritdoc}
 *
 * @author bearlord <565364226@qq.com>
 * @since 2.0.14
 */
class Command extends \ESD\Yii\Db\Command
{
    /**
     * Creates an INSERT command.
     *
     * For example,
     *
     * ```php
     * $connection->createCommand()->insert('user', [
     *     'name' => 'Sam',
     *     'age' => 30,
     * ])->execute();
     * ```
     *
     * The method will properly escape the column names, and bind the values to be inserted.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array|\ESD\Yii\Db\Query $columns the column data (name => value) to be inserted into the table or instance
     * of [[ESD\Yii\Db\Query|Query]] to perform INSERT INTO ... SELECT SQL statement.
     * Passing of [[ESD\Yii\Db\Query|Query]] is available since version 2.0.11.
     * @return \ESD\Yii\Db\Taos\Command the command object itself
     */
    public function insert($table, $columns)
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->insert($table, $columns, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Binds a list of values to the corresponding parameters.
     * This is similar to [[bindValue()]] except that it binds multiple values at a time.
     * Note that the SQL data type of each value is determined by its PHP type.
     * @param array $values the values to be bound. This must be given in terms of an associative
     * array with array keys being the parameter names, and array values the corresponding parameter values,
     * e.g. `[':name' => 'John', ':age' => 25]`. By default, the PDO type of each value is determined
     * by its PHP type. You may explicitly specify the PDO type by using a [[ESD\Yii\Db\PdoValue]] class: `new PdoValue(value, type)`,
     * e.g. `[':name' => 'John', ':profile' => new PdoValue($profile, \PDO::PARAM_LOB)]`.
     * @return \ESD\Yii\Db\Taos\Command the current command being executed
     */
    public function bindValues($values)
    {
        if (empty($values)) {
            return $this;
        }

        $schema = $this->db->getSchema();
        foreach ($values as $name => $value) {
            if (is_array($value)) { // TODO: Drop in Yii 2.1
                $this->_pendingParams[$name] = $value;
                $this->params[$name] = $value[0];
            } elseif ($value instanceof PdoValue) {
                $this->_pendingParams[$name] = [$value->getValue(), $value->getType()];
                $this->params[$name] = $value->getValue();
            } else {
                $type = $schema->getPdoType($value);
                $this->_pendingParams[$name] = [$value, $type];
                $this->params[$name] = $value;
            }
        }

        return $this;
    }
    /**
     * Executes the SQL statement.
     * This method should only be used for executing non-query SQL statement, such as `INSERT`, `DELETE`, `UPDATE` SQLs.
     * No result set will be returned.
     * @return int number of rows affected by the execution.
     * @throws Exception execution failed
     */
    public function execute()
    {
        printf("Taos\QueryBuilder execute\n");

        $sql = $this->getSql();

        $this->prepare(false);

        list($profile, $rawSql) = $this->logQuery(__METHOD__);

        if ($sql == '') {
            return 0;
        }

        try {
            $profile and Yii::beginProfile($rawSql, __METHOD__);

            $this->internalExecute($rawSql);
            $n = $this->pdoStatement->rowCount();

            $profile and Yii::endProfile($rawSql, __METHOD__);

            $this->refreshTableSchema();

            return $n;
        } catch (Exception $e) {
            if (!$this->isBreak($e)) {
                throw $e;
            }
            try {
                if ($this->reconnectTimes < $this->reconnectMaxTimes) {
                    ++$this->reconnectTimes;

                    Yii::debug(sprintf("Reconnect times ...%d", $this->reconnectTimes));

                    $this->db->close();
                    $this->db->open();

                    //PDO handle changed with new PDO connection, so pdoStatement need to reacquire.
                    //'prepare' and 'bindPendingParams' function makes no sense but just to generate new pdoStatement
                    //'rawSql' is same as before.
                    $this->pdoStatement = $this->db->pdo->prepare($this->getSql());
                    $this->bindPendingParams();
                    list($profile, $rawSql) = $this->logQuery(__METHOD__);

                    $this->internalExecute($rawSql);
                    $n = $this->pdoStatement->rowCount();

                    $contextKey = sprintf("Pdo:%s", $this->db->poolName);
                    setContextValue($contextKey, $this->db);

                    $profile and Yii::endProfile($rawSql, __METHOD__);

                    $this->refreshTableSchema();
                    return $n;
                }
            } catch (Exception $e2) {
                throw $e2;
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * Executes a prepared statement.
     *
     * It's a wrapper around [[\PDOStatement::execute()]] to support transactions
     * and retry handlers.
     *
     * @param string|null $rawSql the rawSql if it has been created.
     * @throws Exception if execution failed.
     * @since 2.0.14
     */
    protected function internalExecute($rawSql)
    {
        $attempt = 0;
        while (true) {
            try {
                if (
                    ++$attempt === 1
                    && $this->_isolationLevel !== false
                    && $this->db->getTransaction() === null
                ) {
                    $this->db->transaction(function () use ($rawSql) {
                        $this->internalExecute($rawSql);
                    }, $this->_isolationLevel);
                } else {
                    $this->pdoStatement->execute();
                }

                $this->reconnectTimes = 0;
                break;
            } catch (\Exception $e) {
                $rawSql = $rawSql ?: $this->getRawSql();
                $e = $this->db->getSchema()->convertException($e, $rawSql);

                if ($this->_retryHandler === null || !call_user_func($this->_retryHandler, $e, $attempt)) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Creates a SQL command for changing the definition of a column.
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the column type. [[\ESD\Yii\Db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
     * @return \ESD\Yii\Db\Command the command object itself
     */
    public function alterColumn($table, $column, $type)
    {
        $sql = $this->db->getQueryBuilder()->alterColumn($table, $column, $type);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    public function addTag($table ,$tag, $type)
    {
        $sql = $this->db->getQueryBuilder()->alterColumn($table, $column, $type);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }
}