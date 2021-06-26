<?php

namespace ESD\Yii\Clickhouse;

/**
 * Class ColumnSchemaBuilder
 * @package ESD\Yii\Clickhouse
 */
class ColumnSchemaBuilder extends \ESD\Yii\Db\ColumnSchemaBuilder
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