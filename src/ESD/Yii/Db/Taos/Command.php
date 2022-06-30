<?php

namespace ESD\Yii\Db\Taos;

use ESD\Yii\Db\Exception;
use ESD\Yii\Db\PdoValue;
use ESD\Yii\Yii;
use PDO;

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
            if (is_array($value)) {
                $value = $this->formatPdoValue($value);
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

    protected function formatPdoValue(array $value)
    {
        switch ($value[1]) {
            case PDO::PARAM_TAOS_TIMESTAMP:
            case PDO::PARAM_TAOS_TINYINT:
            case PDO::PARAM_TAOS_SMALLINT:
            case PDO::PARAM_TAOS_INT:
            case PDO::PARAM_TAOS_BIGINT:
            case PDO::PARAM_TAOS_UTINYINT:
            case PDO::PARAM_TAOS_USMALLINT:
            case PDO::PARAM_TAOS_UINT:
            case PDO::PARAM_TAOS_UBIGINT:
                $value[0] = (int)$value[0];
                break;

            case PDO::PARAM_TAOS_FLOAT:
            case PDO::PARAM_TAOS_DOUBLE:
                $value[0] = (float)$value[0];
                break;

            case PDO::PARAM_TAOS_BINARY:
            case PDO::PARAM_TAOS_NCHAR:
                $value[0] = (string)$value[0];
                break;

            case PDO::PARAM_TAOS_NULL:
                $value[0] = null;
                break;

            case PDO::PARAM_TAOS_BOOL:
                $value[0] = (bool)$value[0];
                break;
        }
        return $value;
    }

    /**
     * Specifies the SQL statement to be executed. The SQL statement will be quoted using [[Connection::quoteSql()]].
     * The previous SQL (if any) will be discarded, and [[params]] will be cleared as well. See [[reset()]]
     * for details.
     *
     * @param string $sql the SQL statement to be set.
     * @return \ESD\Yii\Db\Command this command instance
     * @see reset()
     * @see cancel()
     */
    public function setSql($sql)
    {
        if ($sql !== $this->_sql) {
            $this->cancel();
            $this->reset();
            $this->_sql = $this->quoteSql($sql);
        }

        return $this;
    }

    /**
     * Processes a SQL statement by quoting table and column names that are enclosed within double brackets.
     * Tokens enclosed within double curly brackets are treated as table names, while
     * tokens enclosed within double square brackets are column names. They will be quoted accordingly.
     * Also, the percentage character "%" at the beginning or ending of a table name will be replaced
     * with [[tablePrefix]].
     * @param string $sql the SQL to be quoted
     * @return string the quoted SQL
     */
    public function quoteSql($sql)
    {
        return preg_replace_callback(
            '/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])/',
            function ($matches) {
                if (isset($matches[3])) {
                    return $matches[3];
                }

                return str_replace('%', $this->db->tablePrefix, $matches[2]);
            },
            $sql
        );
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

    /**
     * @param $table
     * @param $tag
     * @param $type
     * @return \ESD\Yii\Db\Command
     */
    public function addTag($table, $tag, $type)
    {
        $sql = $this->db->getQueryBuilder()->alterColumn($table, $column, $type);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }


    /**
     * Creates a SQL command for creating a new super table.
     *
     * @param $table
     * @param $columns
     * @param array $tags
     * @return \ESD\Yii\Db\Command
     */
    public function createSTable($table, $columns, $tags = [])
    {
        $sql = $this->db->getQueryBuilder()->createSTable($table, $columns, $tags);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for creating a new sub table.
     *
     * @param $table
     * @param $stable
     * @param array $tags
     * @return \ESD\Yii\Db\Command
     */
    public function createSubTable($table, $stable, $tags = [])
    {
        $sql = $this->db->getQueryBuilder()->createSubTable($table, $stable, $tags);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates an INSERT SQL statement.
     *
     * @param $table
     * @param $columns
     * @param $stable
     * @param array $tags
     * @return \ESD\Yii\Db\Command
     */
    public function insertUsingSTable($table, $columns, $stable, $tags = [])
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->insertUsingSTable($table, $columns, $params, $stable, $tags);

        return $this->setSql($sql)->bindValues($params);
    }
}