<?php

namespace ESD\Yii\Clickhouse\Console;

use ESD\Yii\Clickhouse\Schema;
use ESD\Yii\Base\BaseObject;
use ESD\Yii\Db\ColumnSchema;
use ESD\Yii\Yii;

/**
 * Class MigrationSchemaCommand
 * @package ESD\Yii\Clickhouse\Console
 */
class MigrationSchemaCommand extends BaseObject
{

    const RESULT_TYPE_SQL = 1;
    const RESULT_TYPE_MIGRATION = 2;

    /** @var string source table name */
    public $sourceTable;

    /** @var \ESD\Yii\Db\Connection */
    public $sourceDb;

    public $columns = [
        'event_date Date'
    ];

    public $excludeSourceColumns = [
        'id'
    ];

    /** @var \ESD\Yii\Clickhouse\Connection */
    public $storeDb;

    public function init()
    {
        parent::init();
        if ($this->storeDb === null) {
            $this->storeDb = Yii::$app->clickhouse;
        }
    }

    /**
     * @param ColumnSchema $column
     * @return string
     */
    private function getConvertTypeToClickHouseType(ColumnSchema $column)
    {
        $size = $column->size;
        $unsigned = $column->unsigned ? 'U' : '';
        $type = $column->type;

        switch ($type) {
            case Schema::TYPE_BIGINT:
                return $unsigned . "Int64";

            case Schema::TYPE_INTEGER:
                $typeSize = ($size > 16) ? 32 : 16;
                return $unsigned . "Int" . $typeSize;

            case Schema::TYPE_SMALLINT:
                return $unsigned . "Int16";

            case Schema::TYPE_BOOLEAN:
                return $unsigned . "Int8";

            case Schema::TYPE_MONEY:
            case Schema::TYPE_DECIMAL:
                $typeSize = $column->precision > 32 ? 64 : 32;
                return "Float" . $typeSize;

            case Schema::TYPE_TEXT:
            case Schema::TYPE_STRING:
                if ($size < 100) {
                    return "FixedString({$size})";
                }
                return "String";

            case Schema::TYPE_TIMESTAMP :
            case Schema::TYPE_DATETIME :
                return "DateTime";

            case Schema::TYPE_DATE:
                return "Date";
        }
        return "";
    }

    /**
     * Get sql schema mysql >  clickhouse table
     * @return bool|string
     */
    public function run()
    {
        if (!$table = Yii::$app->getDb()->getTableSchema($this->sourceTable, true)) {
            return false;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS `' . $this->sourceTable . '` ( ' . "\n";
        $columns = [];
        foreach ($table->columns as $column) {
            if (in_array($column->name, $this->excludeSourceColumns)) {
                continue;
            }
            $type = $this->getConvertTypeToClickHouseType($column);
            $columns[] = '`' . $column->name . '` ' . $type;
        }
        $columns = array_merge($this->columns, $columns);

        $sql .= implode(",\n", $columns);
        $sql .= "\n)";

        return $sql;
    }

}
