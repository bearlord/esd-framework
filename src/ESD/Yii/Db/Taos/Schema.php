<?php

namespace ESD\Yii\Db\Taos;

use ESD\Yii\Db\CheckConstraint;
use ESD\Yii\Db\Constraint;
use ESD\Yii\Db\ConstraintFinderInterface;
use ESD\Yii\Db\DefaultValueConstraint;
use ESD\Yii\Db\ForeignKeyConstraint;
use ESD\Yii\Db\IndexConstraint;
use ESD\Yii\Db\TableSchema;

/**
 * Schema is the class for retrieving metadata from a TDengine database.
 *
 * @author Bearlord <565364226@qq.com>
 * @since 2.0
 */
class Schema extends \ESD\Yii\Db\Schema implements ConstraintFinderInterface
{
    const TYPE_INT = 'int';
    const TYPE_BOOL = 'bool';
    const TYPE_NCHAR = 'nchar';

    public function getTablePrimaryKey($name, $refresh = false)
    {
        // TODO: Implement getTablePrimaryKey() method.
    }

    public function getSchemaPrimaryKeys($schema = '', $refresh = false)
    {
        // TODO: Implement getSchemaPrimaryKeys() method.
    }

    public function getTableForeignKeys($name, $refresh = false)
    {
        // TODO: Implement getTableForeignKeys() method.
    }

    public function getSchemaForeignKeys($schema = '', $refresh = false)
    {
        // TODO: Implement getSchemaForeignKeys() method.
    }

    public function getTableIndexes($name, $refresh = false)
    {
        // TODO: Implement getTableIndexes() method.
    }

    public function getSchemaIndexes($schema = '', $refresh = false)
    {
        // TODO: Implement getSchemaIndexes() method.
    }

    public function getTableUniques($name, $refresh = false)
    {
        // TODO: Implement getTableUniques() method.
    }

    public function getSchemaUniques($schema = '', $refresh = false)
    {
        // TODO: Implement getSchemaUniques() method.
    }

    public function getTableChecks($name, $refresh = false)
    {
        // TODO: Implement getTableChecks() method.
    }

    public function getSchemaChecks($schema = '', $refresh = false)
    {
        // TODO: Implement getSchemaChecks() method.
    }

    public function getTableDefaultValues($name, $refresh = false)
    {
        // TODO: Implement getTableDefaultValues() method.
    }

    public function getSchemaDefaultValues($schema = '', $refresh = false)
    {
        // TODO: Implement getSchemaDefaultValues() method.
    }

    protected function loadTableSchema($name)
    {
        // TODO: Implement loadTableSchema() method.
    }

    /**
     * Creates a query builder for the PostgreSQL database.
     * @return QueryBuilder query builder instance
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * If the table name is already quoted or contains '(' or '{{',
     * then this method will do nothing.
     * @param string $name table name
     * @return string the properly quoted table name
     * @see quoteSimpleTableName()
     */
    public function quoteTableName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '{{') !== false) {
            return $name;
        }
        if (strpos($name, '.') === false) {
            return $name;
        }
        $parts = explode('.', $name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $part;
        }

        return implode('.', $parts);
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * If the column name is already quoted or contains '(', '[[' or '{{',
     * then this method will do nothing.
     * @param string $name column name
     * @return string the properly quoted column name
     * @see quoteSimpleColumnName()
     */
    public function quoteColumnName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '[[') !== false) {
            return $name;
        }
        if (($pos = strrpos($name, '.')) !== false) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else {
            $prefix = '';
        }
        if (strpos($name, '{{') !== false) {
            return $name;
        }

        return $prefix . $name;
    }

}