<?php

namespace ESD\Yii\Clickhouse;

use ESD\Yii\Db\ColumnSchemaBuilder as BaseColumnSchemaBuilder;

/**
 * Class ColumnSchemaBuilder
 * @package ESD\Yii\Clickhouse
 */
class ColumnSchemaBuilder extends BaseColumnSchemaBuilder
{
    /**
     * @inheritdoc
     */
    public function __toString()
    {
        switch ($this->getTypeCategory()) {
            case self::CATEGORY_NUMERIC:
                $format = '{unsigned}{type}{default}';
                break;
            default:
                $format = '{type}{default}';
        }

        return $this->buildCompleteString($format);
    }

    /**
     * @inheritdoc
     */
    protected function buildUnsignedString()
    {
        return $this->isUnsigned ? 'U' : '';
    }
}